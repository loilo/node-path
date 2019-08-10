<?php namespace Loilo\NodePath;

use InvalidArgumentException;

/**
 * Provides utilities for working with Posix file and directory paths
 */
class PosixPath extends BasePath
{
    /**
     * {@inheritdoc}
     */
    public static function basename(string $path, ?string $ext = null): string
    {
        $start = 0;
        $end = -1;
        $matchedSlash = true;
        $i = null;
        $extLength = is_null($ext) ? 0 : strlen($ext);
        $pathLength = strlen($path);

        if (!is_null($ext) && $extLength > 0 && $extLength <= $pathLength) {
            if ($ext === $path) {
                return '';
            }
            $extIdx = $extLength - 1;
            $firstNonSlashEnd = -1;
            for ($i = $pathLength - 1; $i >= 0; --$i) {
                $code = ord($path[$i]);
                if ($code === static::CHAR_FORWARD_SLASH) {
                    // If we reached a path separator that was not part of a set of path
                    // separators at the end of the string, stop now
                    if (!$matchedSlash) {
                        $start = $i + 1;
                        break;
                    }
                } else {
                    if ($firstNonSlashEnd === -1) {
                        // We saw the first non-path separator, remember this index in case
                        // we need it if the extension ends up not matching
                        $matchedSlash = false;
                        $firstNonSlashEnd = $i + 1;
                    }
                    if ($extIdx >= 0) {
                        // Try to match the explicit extension
                        if ($code === ord($ext[$extIdx])) {
                            if (--$extIdx === -1) {
                                // We matched the extension, so mark this as the end of our path
                                // component
                                $end = $i;
                            }
                        } else {
                            // Extension does not match, so our result is the entire path
                            // component
                            $extIdx = -1;
                            $end = $firstNonSlashEnd;
                        }
                    }
                }
            }

            if ($start === $end) {
                $end = $firstNonSlashEnd;
            } elseif ($end === -1) {
                $end = $pathLength;
            }
            return static::slice($path, $start, $end);
        }
        for ($i = $pathLength - 1; $i >= 0; --$i) {
            if (ord($path[$i]) === static::CHAR_FORWARD_SLASH) {
                // If we reached a path separator that was not part of a set of path
                // separators at the end of the string, stop now
                if (!$matchedSlash) {
                    $start = $i + 1;
                    break;
                }
            } elseif ($end === -1) {
                // We saw the first non-path separator, mark this as the end of our
                // path component
                $matchedSlash = false;
                $end = $i + 1;
            }
        }

        if ($end === -1) {
            return '';
        }
        return static::slice($path, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public static function dirname(string $path): string
    {
        if (strlen($path) === 0) {
            return '.';
        }

        $hasRoot = ord($path) === static::CHAR_FORWARD_SLASH;

        $end = -1;
        $matchedSlash = true;
        for ($i = strlen($path) - 1; $i >= 1; --$i) {
            if (ord($path[$i]) === static::CHAR_FORWARD_SLASH) {
                if (!$matchedSlash) {
                    $end = $i;
                    break;
                }
            } else {
                // We saw the first non-path separator
                $matchedSlash = false;
            }
        }

        if ($end === -1) {
            return $hasRoot ? '/' : '.';
        }
        if ($hasRoot && $end === 1) {
            return '//';
        }
        return static::slice($path, 0, $end);
    }

    /**
     * {@inheritdoc}
     */
    public static function extname(string $path): string
    {
        $startDot = -1;
        $startPart = 0;
        $end = -1;
        $matchedSlash = true;
        // Track the state of characters (if any) we see before our first dot and
        // after any path separator we find
        $preDotState = 0;
        for ($i = strlen($path) - 1; $i >= 0; --$i) {
            $code = ord($path[$i]);
            if ($code === static::CHAR_FORWARD_SLASH) {
                // If we reached a path separator that was not part of a set of path
                // separators at the end of the string, stop now
                if (!$matchedSlash) {
                    $startPart = $i + 1;
                    break;
                }
                continue;
            }
            if ($end === -1) {
                // We saw the first non-path separator, mark this as the end of our
                // extension
                $matchedSlash = false;
                $end = $i + 1;
            }
            if ($code === static::CHAR_DOT) {
                // If this is our first dot, mark it as the start of our extension
                if ($startDot === -1) {
                    $startDot = $i;
                } elseif ($preDotState !== 1) {
                    $preDotState = 1;
                }
            } elseif ($startDot !== -1) {
                // We saw a non-dot and non-path separator before our dot, so we should
                // have a good chance at having a non-empty extension
                $preDotState = -1;
            }
        }

        if ($startDot === -1 ||
            $end === -1 ||
            // We saw a non-dot character immediately before the dot
            $preDotState === 0 ||
            // The (right-most) trimmed path component is exactly '..'
            ($preDotState === 1 && $startDot === $end - 1 && $startDot === $startPart + 1)
        ) {
            return '';
        }

        return static::slice($path, $startDot, $end);
    }

    /**
     * {@inheritdoc}
     */
    public static function format($pathData): string
    {
        return parent::format($pathData);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDelimiter(): string
    {
        return ':';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSeparator(): string
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     */
    public static function isAbsolute(string $path): bool
    {
        return strlen($path) > 0 && ord($path) === static::CHAR_FORWARD_SLASH;
    }

    /**
     * {@inheritdoc}
     */
    protected static function isPathSeparator(int $code): bool
    {
        return static::isPosixPathSeparator($code);
    }

    /**
     * {@inheritdoc}
     */
    public static function join(...$paths): string
    {
        $pathsNum = sizeof($paths);

        if ($pathsNum === 0) {
            return '.';
        }

        $joined = null;
        for ($i = 0; $i < $pathsNum; ++$i) {
            $arg = $paths[$i];

            if (!is_string($arg)) {
                throw new InvalidArgumentException(
                    'All paths passed to the join() method must be strings'
                );
            }

            if (strlen($arg) > 0) {
                if (is_null($joined)) {
                    $joined = $arg;
                } else {
                    $joined .= "/$arg";
                }
            }
        }
        if (is_null($joined)) {
            return '.';
        }

        return static::normalize($joined);
    }

    /**
     * {@inheritdoc}
     */
    public static function normalize(string $path): string
    {
        if (strlen($path) === 0) {
            return '.';
        }

        $isAbsolute = ord($path) === static::CHAR_FORWARD_SLASH;
        $trailingSeparator = ord($path[strlen($path) - 1]) === static::CHAR_FORWARD_SLASH;

        // Normalize the path
        $path = static::normalizeString($path, !$isAbsolute);

        if (strlen($path) === 0) {
            if ($isAbsolute) {
                return '/';
            }
            return $trailingSeparator ? './' : '.';
        }
        if ($trailingSeparator) {
            $path .= '/';
        }

        return $isAbsolute ? "/$path" : $path;
    }

    /**
     * {@inheritdoc}
     */
    public static function parse(string $path): PathObjectInterface
    {
        $ret = [
            'root' => '',
            'dir' => '',
            'base' => '',
            'ext' => '',
            'name' => ''
        ];

        if (strlen($path) === 0) {
            return PathObject::fromArray($ret);
        }

        $isAbsolute = ord($path) === static::CHAR_FORWARD_SLASH;
        $start = null;
        if ($isAbsolute) {
            $ret['root'] = '/';
            $start = 1;
        } else {
            $start = 0;
        }
        $startDot = -1;
        $startPart = 0;
        $end = -1;
        $matchedSlash = true;
        $i = strlen($path) - 1;

        // Track the state of characters (if any) we see before our first dot and
        // after any path separator we find
        $preDotState = 0;

        // Get non-dir info
        for (; $i >= $start; --$i) {
            $code = ord($path[$i]);
            if ($code === static::CHAR_FORWARD_SLASH) {
                // If we reached a path separator that was not part of a set of path
                // separators at the end of the string, stop now
                if (!$matchedSlash) {
                    $startPart = $i + 1;
                    break;
                }
                continue;
            }
            if ($end === -1) {
                // We saw the first non-path separator, mark this as the end of our
                // extension
                $matchedSlash = false;
                $end = $i + 1;
            }
            if ($code === static::CHAR_DOT) {
                // If this is our first dot, mark it as the start of our extension
                if ($startDot === -1) {
                    $startDot = $i;
                } elseif ($preDotState !== 1) {
                    $preDotState = 1;
                }
            } elseif ($startDot !== -1) {
                // We saw a non-dot and non-path separator before our dot, so we should
                // have a good chance at having a non-empty extension
                $preDotState = -1;
            }
        }

        if ($end !== -1) {
            $start = $startPart === 0 && $isAbsolute ? 1 : $startPart;
            if ($startDot === -1 ||
                // We saw a non-dot character immediately before the dot
                $preDotState === 0 ||
                // The (right-most) trimmed path component is exactly '..'
                ($preDotState === 1 && $startDot === $end - 1 && $startDot === $startPart + 1)
            ) {
                $ret['base'] = $ret['name'] = static::slice($path, $start, $end);
            } else {
                $ret['name'] = static::slice($path, $start, $startDot);
                $ret['base'] = static::slice($path, $start, $end);
                $ret['ext'] = static::slice($path, $startDot, $end);
            }
        }

        if ($startPart > 0) {
            $ret['dir'] = static::slice($path, 0, $startPart - 1);
        } elseif ($isAbsolute) {
            $ret['dir'] = '/';
        }

        return PathObject::fromArray($ret);
    }

    /**
     * {@inheritdoc}
     */
    public static function relative(string $from, string $to): string
    {
        if ($from === $to) {
            return '';
        }

        // Trim leading forward slashes.
        $from = static::resolve($from);
        $to = static::resolve($to);

        if ($from === $to) {
            return '';
        }

        $fromStart = 1;
        $fromEnd = strlen($from);
        $fromLen = $fromEnd - $fromStart;
        $toStart = 1;
        $toLen = strlen($to) - $toStart;

        // Compare paths to find the longest common path from root
        $length = $fromLen < $toLen ? $fromLen : $toLen;
        $lastCommonSep = -1;
        for ($i = 0; $i < $length; $i++) {
            $fromCode = ord($from[$fromStart + $i]);
            if ($fromCode !== ord($to[$toStart + $i])) {
                break;
            } elseif ($fromCode === static::CHAR_FORWARD_SLASH) {
                $lastCommonSep = $i;
            }
        }
        if ($i === $length) {
            if ($toLen > $length) {
                if (ord($to[$toStart + $i]) === static::CHAR_FORWARD_SLASH) {
                    // We get here if `from` is the exact base path for `to`.
                    // For example: from='/foo/bar'; to='/foo/bar/baz'
                    return static::slice($to, $toStart + $i + 1);
                }
                if ($i === 0) {
                    // We get here if `from` is the root
                    // For example: from='/'; to='/foo'
                    return static::slice($to, $toStart + $i);
                }
            } elseif ($fromLen > $length) {
                if (ord($from[$fromStart + $i]) === static::CHAR_FORWARD_SLASH) {
                    // We get here if `to` is the exact base path for `from`.
                    // For example: from='/foo/bar/baz'; to='/foo/bar'
                    $lastCommonSep = $i;
                } elseif ($i === 0) {
                    // We get here if `to` is the root.
                    // For example: from='/foo/bar'; to='/'
                    $lastCommonSep = 0;
                }
            }
        }

        $out = '';
        // Generate the relative path based on the path difference between `to`
        // and `from`.
        for ($i = $fromStart + $lastCommonSep + 1; $i <= $fromEnd; ++$i) {
            if ($i === $fromEnd || ord($from[$i]) === static::CHAR_FORWARD_SLASH) {
                $out .= strlen($out) === 0 ? '..' : '/..';
            }
        }

        // Lastly, append the rest of the destination (`to`) path that comes after
        // the common path parts.
        return $out . static::slice($to, $toStart + $lastCommonSep);
    }

    /**
     * {@inheritdoc}
     */
    public static function resolve(...$paths): string
    {
        $resolvedPath = '';
        $resolvedAbsolute = false;

        for ($i = sizeof($paths) - 1; $i >= -1 && !$resolvedAbsolute; $i--) {
            $path = $i >= 0 ? $paths[$i] : getcwd();

            if (!is_string($path)) {
                throw new InvalidArgumentException(
                    'All paths passed to the resolve() method must be strings'
                );
            }

            // Skip empty entries
            if (strlen($path) === 0) {
                continue;
            }

            $resolvedPath = "$path/$resolvedPath";
            $resolvedAbsolute = ord($path) === static::CHAR_FORWARD_SLASH;
        }

        // At this point the path should be resolved to a full absolute path, but
        // handle relative paths to be safe (might happen when process.cwd() fails)

        // Normalize the path
        $resolvedPath = static::normalizeString($resolvedPath, !$resolvedAbsolute);

        if ($resolvedAbsolute) {
            return "/$resolvedPath";
        }
        return strlen($resolvedPath) > 0 ? $resolvedPath : '.';
    }

    /**
     * {@inheritdoc}
     */
    public static function toNamespacedPath($path = null)
    {
        // Non-op on posix systems
        return $path;
    }
}
