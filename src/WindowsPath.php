<?php namespace Loilo\NodePath;

use InvalidArgumentException;

/**
 * Provides utilities for working with Windows file and directory paths
 */
class WindowsPath extends BasePath
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

        // Check for a drive letter prefix so as not to mistake the following
        // path separator as an extra separator at the end of the path that can be
        // disregarded
        if (strlen($path) >= 2 &&
            static::isWindowsDeviceRoot(ord($path)) &&
            ord($path[1]) === static::CHAR_COLON
        ) {
            $start = 2;
        }

        if (!is_null($ext) && strlen($ext) > 0 && strlen($ext) <= strlen($path)) {
            if ($ext === $path) {
                return '';
            }

            $extIdx = strlen($ext) - 1;
            $firstNonSlashEnd = -1;

            for ($i = strlen($path) - 1; $i >= $start; --$i) {
                $code = ord($path[$i]);

                if (static::isPathSeparator($code)) {
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
                $end = strlen($path);
            }
            return static::slice($path, $start, $end);
        }

        for ($i = strlen($path) - 1; $i >= $start; --$i) {
            if (static::isPathSeparator(ord($path[$i]))) {
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
        $len = strlen($path);

        if ($len === 0) {
            return '.';
        }

        $rootEnd = -1;
        $offset = 0;
        $code = ord($path);

        if ($len === 1) {
            // `path` contains just a path separator, exit early to avoid
            // unnecessary work or a dot.
            return static::isPathSeparator($code) ? $path : '.';
        }

        // Try to match a root
        if (static::isPathSeparator($code)) {
            // Possible UNC root

            $rootEnd = 1;
            $offset = 1;

            if (static::isPathSeparator(ord($path[1]))) {
                // Matched double path separator at beginning
                $j = $last = 2;
                // Match 1 or more non-path separators
                while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                    $j++;
                }
                if ($j < $len && $j !== $last) {
                    // Matched!
                    $last = $j;
                    // Match 1 or more path separators
                    while ($j < $len && static::isPathSeparator(ord($path[$j]))) {
                        $j++;
                    }
                    if ($j < $len && $j !== $last) {
                        // Matched!
                        $last = $j;
                        // Match 1 or more non-path separators
                        while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                            $j++;
                        }
                        if ($j === $len) {
                            // We matched a UNC root only
                            return $path;
                        }
                        if ($j !== $last) {
                            // We matched a UNC root with leftovers

                            // Offset by 1 to include the separator after the UNC root to
                            // treat it as a "normal root" on top of a (UNC) root
                            $rootEnd = $offset = $j + 1;
                        }
                    }
                }
            }
            // Possible device root
        } elseif (static::isWindowsDeviceRoot($code) && ord($path[1]) === static::CHAR_COLON) {
            $rootEnd = $len > 2 && static::isPathSeparator(ord($path[2])) ? 3 : 2;
            $offset = $rootEnd;
        }

        $end = -1;
        $matchedSlash = true;

        for ($i = $len - 1; $i >= $offset; --$i) {
            if (static::isPathSeparator(ord($path[$i]))) {
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
            if ($rootEnd === -1) {
                return '.';
            }

            $end = $rootEnd;
        }
        return static::slice($path, 0, $end);
    }

    /**
     * {@inheritdoc}
     */
    public static function extname(string $path): string
    {
        $start = 0;
        $startDot = -1;
        $startPart = 0;
        $end = -1;
        $matchedSlash = true;
        // Track the state of characters (if any) we see before our first dot and
        // after any path separator we find
        $preDotState = 0;

        // Check for a drive letter prefix so as not to mistake the following
        // path separator as an extra separator at the end of the path that can be
        // disregarded

        if (strlen($path) >= 2 &&
            ord($path[1]) === static::CHAR_COLON &&
            static::isWindowsDeviceRoot(ord($path))
        ) {
            $start = $startPart = 2;
        }

        for ($i = strlen($path) - 1; $i >= $start; --$i) {
            $code = ord($path[$i]);
            if (static::isPathSeparator($code)) {
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
        return ';';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSeparator(): string
    {
        return '\\';
    }

    /**
     * {@inheritdoc}
     */
    public static function isAbsolute(string $path): bool
    {
        $len = strlen($path);

        if ($len === 0) {
            return false;
        }

        $code = ord($path);

        return static::isPathSeparator($code) ||
            // Possible device root
            ($len > 2 &&
                static::isWindowsDeviceRoot($code) &&
                ord($path[1]) === static::CHAR_COLON &&
                static::isPathSeparator(ord($path[2])));
    }

    /**
     * {@inheritdoc}
     */
    protected static function isPathSeparator(int $code): bool
    {
        return static::isWindowsPathSeparator($code);
    }

    /**
     * Check whether the provided char code could be a Windows device root
     *
     * @param integer $code
     * @return bool
     */
    protected static function isWindowsDeviceRoot(int $code): bool
    {
        return ($code >= static::CHAR_UPPERCASE_A && $code <= static::CHAR_UPPERCASE_Z) ||
            ($code >= static::CHAR_LOWERCASE_A && $code <= static::CHAR_LOWERCASE_Z);
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
        $firstPart = null;

        for ($i = 0; $i < $pathsNum; ++$i) {
            $arg = $paths[$i];

            if (!is_string($arg)) {
                throw new InvalidArgumentException(
                    'All paths passed to the join() method must be strings'
                );
            }

            if (strlen($arg) > 0) {
                if (is_null($joined)) {
                    $firstPart = $arg;
                    $joined = $arg;
                } else {
                    $joined .= "\\$arg";
                }
            }
        }

        if (is_null($joined)) {
            return '.';
        }

        // Make sure that the joined path doesn't start with two slashes, because
        // normalize() will mistake it for an UNC path then.
        //
        // This step is skipped when it is very clear that the user actually
        // intended to point at an UNC path. This is assumed when the first
        // non-empty string arguments starts with exactly two slashes followed by
        // at least one more non-slash character.
        //
        // Note that for normalize() to treat a path as an UNC path it needs to
        // have at least 2 components, so we don't filter for that here.
        // This means that the user can use join to construct UNC paths from
        // a server name and a share name; for example:
        //   path.join('//server', 'share') -> '\\\\server\\share\\')
        $needsReplace = true;
        $slashCount = 0;

        if (static::isPathSeparator(ord($firstPart))) {
            ++$slashCount;
            $firstLen = strlen($firstPart);
            if ($firstLen > 1 && static::isPathSeparator(ord($firstPart[1]))) {
                ++$slashCount;
                if ($firstLen > 2) {
                    if (static::isPathSeparator(ord($firstPart[2]))) {
                        ++$slashCount;
                    } else {
                        // We matched a UNC path in the first part
                        $needsReplace = false;
                    }
                }
            }
        }

        if ($needsReplace) {
            // Find any more consecutive slashes we need to replace
            while ($slashCount < strlen($joined) &&
                   static::isPathSeparator(ord($joined[$slashCount]))
            ) {
                $slashCount++;
            }

            // Replace the slashes if needed
            if ($slashCount >= 2) {
                $joined = '\\' . static::slice($joined, $slashCount);
            }
        }

        return static::normalize($joined);
    }

    /**
     * {@inheritdoc}
     */
    public static function normalize(string $path): string
    {
        $len = strlen($path);

        if ($len === 0) {
            return '.';
        }

        $rootEnd = 0;
        $device = null;
        $isAbsolute = false;
        $code = ord($path);

        // Try to match a root
        if ($len === 1) {
            // `path` contains just a single char, exit early to avoid
            // unnecessary work
            return static::isPosixPathSeparator($code) ? '\\' : $path;
        }

        if (static::isPathSeparator($code)) {
            // Possible UNC root

            // If we started with a separator, we know we at least have an absolute
            // path of some kind (UNC or otherwise)
            $isAbsolute = true;

            if (static::isPathSeparator(ord($path[1]))) {
                // Matched double path separator at beginning
                $j = 2;
                $last = $j;
                // Match 1 or more non-path separators
                while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                    $j++;
                }

                if ($j < $len && $j !== $last) {
                    $firstPart = static::slice($path, $last, $j);
                    // Matched!
                    $last = $j;
                    // Match 1 or more path separators
                    while ($j < $len && static::isPathSeparator(ord($path[$j]))) {
                        $j++;
                    }
                    if ($j < $len && $j !== $last) {
                        // Matched!
                        $last = $j;
                        // Match 1 or more non-path separators
                        while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                            $j++;
                        }

                        if ($j === $len) {
                            // We matched a UNC root only
                            // Return the normalized version of the UNC root since there
                            // is nothing left to process
                            return '\\\\' . $firstPart . '\\' . static::slice($path, $last) . '\\';
                        }

                        if ($j !== $last) {
                            // We matched a UNC root with leftovers
                            $device = '\\\\' . $firstPart . '\\' . static::slice($path, $last, $j);
                            $rootEnd = $j;
                        }
                    }
                }
            } else {
                $rootEnd = 1;
            }
        } elseif (static::isWindowsDeviceRoot($code) && ord($path[1]) === static::CHAR_COLON) {
            // Possible device root
            $device = static::slice($path, 0, 2);
            $rootEnd = 2;
            if ($len > 2 && static::isPathSeparator(ord($path[2]))) {
                // Treat separator following drive name as an absolute path
                // indicator
                $isAbsolute = true;
                $rootEnd = 3;
            }
        }

        $tail =
            $rootEnd < $len ? static::normalizeString(static::slice($path, $rootEnd), !$isAbsolute) : '';

        if (strlen($tail) === 0 && !$isAbsolute) {
            $tail = '.';
        }

        if (strlen($tail) > 0 && static::isPathSeparator(ord($path[$len - 1]))) {
            $tail .= '\\';
        }

        if (is_null($device)) {
            return $isAbsolute ? '\\' . $tail : $tail;
        }

        return $isAbsolute ? "$device\\$tail" : "$device$tail";
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

        $len = strlen($path);
        $rootEnd = 0;
        $code = ord($path);

        if ($len === 1) {
            if (static::isPathSeparator($code)) {
                // `path` contains just a path separator, exit early to avoid
                // unnecessary work
                $ret['root'] = $ret['dir'] = $path;
                return PathObject::fromArray($ret);
            }

            $ret['base'] = $ret['name'] = $path;
            return PathObject::fromArray($ret);
        }

        // Try to match a root
        if (static::isPathSeparator($code)) {
            // Possible UNC root

            $rootEnd = 1;
            if (static::isPathSeparator(ord($path[1]))) {
                // Matched double path separator at beginning
                $j = $last = 2;
                // Match 1 or more non-path separators
                while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                    $j++;
                }
                if ($j < $len && $j !== $last) {
                    // Matched!
                    $last = $j;
                    // Match 1 or more path separators
                    while ($j < $len && static::isPathSeparator(ord($path[$j]))) {
                        $j++;
                    }
                    if ($j < $len && $j !== $last) {
                        // Matched!
                        $last = $j;
                        // Match 1 or more non-path separators
                        while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                            $j++;
                        }
                        if ($j === $len) {
                            // We matched a UNC root only
                            $rootEnd = $j;
                        } elseif ($j !== $last) {
                            // We matched a UNC root with leftovers
                            $rootEnd = $j + 1;
                        }
                    }
                }
            }
        } elseif (static::isWindowsDeviceRoot($code) && ord($path[1]) === static::CHAR_COLON) {
            // Possible device root
            if ($len <= 2) {
                // `path` contains just a drive root, exit early to avoid
                // unnecessary work
                $ret['root'] = $ret['dir'] = $path;
                return PathObject::fromArray($ret);
            }
            $rootEnd = 2;
            if (static::isPathSeparator(ord($path[2]))) {
                if ($len === 3) {
                    // `path` contains just a drive root, exit early to avoid
                    // unnecessary work
                    $ret['root'] = $ret['dir'] = $path;
                    return PathObject::fromArray($ret);
                }
                $rootEnd = 3;
            }
        }
        if ($rootEnd > 0) {
            $ret['root'] = static::slice($path, 0, $rootEnd);
        }

        $startDot = -1;
        $startPart = $rootEnd;
        $end = -1;
        $matchedSlash = true;
        $i = strlen($path) - 1;

        // Track the state of characters (if any) we see before our first dot and
        // after any path separator we find
        $preDotState = 0;

        // Get non-dir info
        for (; $i >= $rootEnd; --$i) {
            $code = ord($path[$i]);
            if (static::isPathSeparator($code)) {
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
            if ($startDot === -1 ||
                // We saw a non-dot character immediately before the dot
                $preDotState === 0 ||
                // The (right-most) trimmed path component is exactly '..'
                ($preDotState === 1 && $startDot === $end - 1 && $startDot === $startPart + 1)
            ) {
                $ret['base'] = $ret['name'] = static::slice($path, $startPart, $end);
            } else {
                $ret['name'] = static::slice($path, $startPart, $startDot);
                $ret['base'] = static::slice($path, $startPart, $end);
                $ret['ext'] = static::slice($path, $startDot, $end);
            }
        }

        // If the directory is the root, use the entire root as the `dir` including
        // the trailing slash if any (`C:\abc` -> `C:\`). Otherwise, strip out the
        // trailing slash (`C:\abc\def` -> `C:\abc`).
        if ($startPart > 0 && $startPart !== $rootEnd) {
            $ret['dir'] = static::slice($path, 0, $startPart - 1);
        } else {
            $ret['dir'] = $ret['root'];
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

        $fromOrig = static::resolve($from);
        $toOrig = static::resolve($to);

        if ($fromOrig === $toOrig) {
            return '';
        }

        $from = strtolower($fromOrig);
        $to = strtolower($toOrig);

        if ($from === $to) {
            return '';
        }

        // Trim any leading backslashes
        $fromStart = 0;
        while ($fromStart < strlen($from) &&
               ord($from[$fromStart]) === static::CHAR_BACKWARD_SLASH
        ) {
            $fromStart++;
        }
        // Trim trailing backslashes (applicable to UNC paths only)
        $fromEnd = strlen($from);
        while ($fromEnd - 1 > $fromStart &&
               ord($from[$fromEnd - 1]) === static::CHAR_BACKWARD_SLASH
        ) {
            $fromEnd--;
        }
        $fromLen = $fromEnd - $fromStart;

        // Trim any leading backslashes
        $toStart = 0;
        while ($toStart < strlen($to) &&
               ord($to[$toStart]) === static::CHAR_BACKWARD_SLASH
        ) {
            $toStart++;
        }
        // Trim trailing backslashes (applicable to UNC paths only)
        $toEnd = strlen($to);
        while ($toEnd - 1 > $toStart &&
               ord($to[$toEnd - 1]) === static::CHAR_BACKWARD_SLASH
        ) {
            $toEnd--;
        }
        $toLen = $toEnd - $toStart;

        // Compare paths to find the longest common path from root
        $length = $fromLen < $toLen ? $fromLen : $toLen;
        $lastCommonSep = -1;

        for ($i = 0; $i < $length; $i++) {
            $fromCode = ord($from[$fromStart + $i]);
            if ($fromCode !== ord($to[$toStart + $i])) {
                break;
            } elseif ($fromCode === static::CHAR_BACKWARD_SLASH) {
                $lastCommonSep = $i;
            }
        }

        // We found a mismatch before the first common path separator was seen, so
        // return the original `to`.
        if ($i !== $length) {
            if ($lastCommonSep === -1) {
                return $toOrig;
            }
        } else {
            if ($toLen > $length) {
                if (ord($to[$toStart + $i]) === static::CHAR_BACKWARD_SLASH) {
                    // We get here if `from` is the exact base path for `to`.
                    // For example: from='C:\\foo\\bar'; to='C:\\foo\\bar\\baz'
                    return static::slice($toOrig, $toStart + $i + 1);
                }
                if ($i === 2) {
                    // We get here if `from` is the device root.
                    // For example: from='C:\\'; to='C:\\foo'
                    return static::slice($toOrig, $toStart + $i);
                }
            }

            if ($fromLen > $length) {
                if (ord($from[$fromStart + $i]) === static::CHAR_BACKWARD_SLASH) {
                    // We get here if `to` is the exact base path for `from`.
                    // For example: from='C:\\foo\\bar'; to='C:\\foo'
                    $lastCommonSep = $i;
                } elseif ($i === 2) {
                    // We get here if `to` is the device root.
                    // For example: from='C:\\foo\\bar'; to='C:\\'
                    $lastCommonSep = 3;
                }
            }

            if ($lastCommonSep === -1) {
                $lastCommonSep = 0;
            }
        }

        $out = '';
        // Generate the relative path based on the path difference between `to` and
        // `from`
        for ($i = $fromStart + $lastCommonSep + 1; $i <= $fromEnd; ++$i) {
            if ($i === $fromEnd || ord($from[$i]) === static::CHAR_BACKWARD_SLASH) {
                $out .= strlen($out) === 0 ? '..' : '\\..';
            }
        }

        $toStart += $lastCommonSep;

        // Lastly, append the rest of the destination (`to`) path that comes after
        // the common path parts
        if (strlen($out) > 0) {
            return $out . static::slice($toOrig, $toStart, $toEnd);
        }

        if (ord($toOrig[$toStart]) === static::CHAR_BACKWARD_SLASH) {
            ++$toStart;
        }
        return static::slice($toOrig, $toStart, $toEnd);
    }

    /**
     * {@inheritdoc}
     */
    public static function resolve(...$paths): string
    {
        $resolvedDevice = '';
        $resolvedTail = '';
        $resolvedAbsolute = false;

        $pathsNum = sizeof($paths);

        for ($i = $pathsNum - 1; $i >= -1; $i--) {
            $path = null;

            if ($i >= 0) {
                $path = $paths[$i];

                if (!is_string($path)) {
                    throw new InvalidArgumentException(
                        'All paths passed to the resolve() method must be strings'
                    );
                }

                // Skip empty entries
                if (strlen($path) === 0) {
                    continue;
                }
            } elseif (strlen($resolvedDevice) === 0) {
                $path = getcwd();
            } else {
                // Windows has the concept of drive-specific current working
                // directories. If we've resolved a drive letter but not yet an
                // absolute path, get cwd for that drive, or the process cwd if
                // the drive cwd is not available. We're sure the device is not
                // a UNC path at this points, because UNC paths are always absolute.
                $path = getenv('=' . $resolvedDevice) ?: getcwd();

                // Verify that a cwd was found and that it actually points
                // to our drive. If not, default to the drive's root.
                if (is_null($path) ||
                    (strtolower(static::slice($path, 0, 2)) !== strtolower($resolvedDevice) &&
                        ord($path[2]) === static::CHAR_BACKWARD_SLASH)
                ) {
                    $path = $resolvedDevice . '\\';
                }
            }

            $len = strlen($path);
            $rootEnd = 0;
            $device = '';
            $isAbsolute = false;
            $code = ord($path);

            // Try to match a root
            if ($len === 1) {
                if (static::isPathSeparator($code)) {
                    // `path` contains just a path separator
                    $rootEnd = 1;
                    $isAbsolute = true;
                }
            } elseif (static::isPathSeparator($code)) {
                // Possible UNC root

                // If we started with a separator, we know we at least have an
                // absolute path of some kind (UNC or otherwise)
                $isAbsolute = true;

                if (static::isPathSeparator(ord($path[1]))) {
                    // Matched double path separator at beginning
                    $j = 2;
                    $last = $j;

                    // Match 1 or more non-path separators
                    while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                        $j++;
                    }

                    if ($j < $len && $j !== $last) {
                        $firstPart = static::slice($path, $last, $j);
                        // Matched!
                        $last = $j;
                        // Match 1 or more path separators
                        while ($j < $len && static::isPathSeparator(ord($path[$j]))) {
                            $j++;
                        }

                        if ($j < $len && $j !== $last) {
                            // Matched!
                            $last = $j;
                            // Match 1 or more non-path separators
                            while ($j < $len && !static::isPathSeparator(ord($path[$j]))) {
                                $j++;
                            }

                            if ($j === $len || $j !== $last) {
                                // We matched a UNC root
                                $device = '\\\\' . $firstPart . '\\' . static::slice($path, $last, $j);
                                $rootEnd = $j;
                            }
                        }
                    }
                } else {
                    $rootEnd = 1;
                }
            } elseif (static::isWindowsDeviceRoot($code) && ord($path[1]) === static::CHAR_COLON) {
                // Possible device root
                $device = static::slice($path, 0, 2);
                $rootEnd = 2;
                if ($len > 2 && static::isPathSeparator(ord($path[2]))) {
                    // Treat separator following drive name as an absolute path
                    // indicator
                    $isAbsolute = true;
                    $rootEnd = 3;
                }
            }

            if (strlen($device) > 0) {
                if (strlen($resolvedDevice) > 0) {
                    if (strtolower($device) !== strtolower($resolvedDevice)) {
                        // This path points to another device so it is not applicable
                        continue;
                    }
                } else {
                    $resolvedDevice = $device;
                }
            }

            if ($resolvedAbsolute) {
                if (strlen($resolvedDevice) > 0) {
                    break;
                }
            } else {
                $resolvedTail = static::slice($path, $rootEnd) . '\\' . $resolvedTail;
                $resolvedAbsolute = $isAbsolute;
                if ($isAbsolute && strlen($resolvedDevice) > 0) {
                    break;
                }
            }
        }

        // At this point the path should be resolved to a full absolute path,
        // but handle relative paths to be safe (might happen when process.cwd()
        // fails)

        // Normalize the tail path
        $resolvedTail = static::normalizeString($resolvedTail, !$resolvedAbsolute);

        return $resolvedAbsolute
            ? $resolvedDevice . '\\' . $resolvedTail
            : ($resolvedDevice . $resolvedTail ?:
                '.');
    }

    /**
     * {@inheritdoc}
     */
    public static function toNamespacedPath($path = null)
    {
        // Note: this will *probably* throw somewhere.
        if (!is_string($path)) {
            return $path;
        }

        if (strlen($path) === 0) {
            return '';
        }

        $resolvedPath = static::resolve($path);

        if (strlen($resolvedPath) <= 2) {
            return $path;
        }

        if (ord($resolvedPath) === static::CHAR_BACKWARD_SLASH) {
            // Possible UNC root
            if (ord($resolvedPath[1]) === static::CHAR_BACKWARD_SLASH) {
                $code = ord($resolvedPath[2]);
                if ($code !== static::CHAR_QUESTION_MARK && $code !== static::CHAR_DOT) {
                    // Matched non-long UNC root, convert the path to a long UNC path
                    return '\\\\?\\UNC\\' . static::slice($resolvedPath, 2);
                }
            }
        } elseif (static::isWindowsDeviceRoot(ord($resolvedPath)) &&
                  ord($resolvedPath[1]) === static::CHAR_COLON &&
                  ord($resolvedPath[2]) === static::CHAR_BACKWARD_SLASH
        ) {
            // Matched device root, convert the path to a long UNC path
            return "\\\\?\\$resolvedPath";
        }

        return $path;
    }
}
