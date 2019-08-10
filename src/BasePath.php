<?php namespace Loilo\NodePath;

use InvalidArgumentException;

/**
 * {@inheritdoc}
 */
abstract class BasePath implements PathInterface
{
    const CHAR_UPPERCASE_A = 65;
    const CHAR_LOWERCASE_A = 97;
    const CHAR_UPPERCASE_Z = 90;
    const CHAR_LOWERCASE_Z = 122;
    const CHAR_DOT = 46;
    const CHAR_FORWARD_SLASH = 47;
    const CHAR_BACKWARD_SLASH = 92;
    const CHAR_COLON = 58;
    const CHAR_QUESTION_MARK = 63;

    /**
     * Check whether the given char code maps to a path separator
     *
     * @param integer $code
     * @return bool
     */
    abstract protected static function isPathSeparator(int $code): bool;

    /**
     * Check whether the given char code maps to a Posix path separator
     *
     * @param integer $code
     * @return bool
     */
    protected static function isPosixPathSeparator(int $code): bool
    {
        return $code === static::CHAR_FORWARD_SLASH;
    }

    /**
     * Check whether the given char code maps to a Windows path separator
     *
     * @param integer $code
     * @return bool
     */
    protected static function isWindowsPathSeparator(int $code): bool
    {
        return $code === static::CHAR_FORWARD_SLASH || $code === static::CHAR_BACKWARD_SLASH;
    }

    /**
     * Resolves . and .. elements in the $path with directory names
     *
     * @param string $path
     * @param boolean $allowAboveRoot Whether paths may start with ..
     * @return string
     */
    protected static function normalizeString(string $path, bool $allowAboveRoot): string
    {
        $res = '';
        $lastSegmentLength = 0;
        $lastSlash = -1;
        $dots = 0;
        $code = 0;

        $pathLength = strlen($path);

        for ($i = 0; $i <= $pathLength; ++$i) {
            if ($i < $pathLength) {
                $code = ord($path[$i]);
            } elseif (static::isPathSeparator($code)) {
                break;
            } else {
                $code = static::CHAR_FORWARD_SLASH;
            }

            if (static::isPathSeparator($code)) {
                if ($lastSlash === $i - 1 || $dots === 1) {
                    // NOOP
                } elseif ($dots === 2) {
                    if (strlen($res) < 2 ||
                        $lastSegmentLength !== 2 ||
                        ord($res[-1]) !== static::CHAR_DOT ||
                        ord($res[-2]) !== static::CHAR_DOT
                    ) {
                        if (strlen($res) > 2) {
                            $lastSlashIndex = strrpos($res, static::getSeparator());
                            if ($lastSlashIndex === false) {
                                $lastSlashIndex = -1;
                            }

                            if ($lastSlashIndex === -1) {
                                $res = '';
                                $lastSegmentLength = 0;
                            } else {
                                $res = static::slice($res, 0, $lastSlashIndex);
                                $newLastSlashIndex = strrpos($res, static::getSeparator());
                                if ($newLastSlashIndex === false) {
                                    $newLastSlashIndex = -1;
                                }

                                $lastSegmentLength =
                                    strlen($res) - 1 - $newLastSlashIndex;
                            }

                            $lastSlash = $i;
                            $dots = 0;
                            continue;
                        } elseif (strlen($res) !== 0) {
                            $res = '';
                            $lastSegmentLength = 0;
                            $lastSlash = $i;
                            $dots = 0;
                            continue;
                        }
                    }

                    if ($allowAboveRoot) {
                        $res .= strlen($res) > 0 ? static::getSeparator() . '..' : '..';
                        $lastSegmentLength = 2;
                    }
                } else {
                    if (strlen($res) > 0) {
                        $res .= static::getSeparator() . static::slice($path, $lastSlash + 1, $i);
                    } else {
                        $res = static::slice($path, $lastSlash + 1, $i);
                    }

                    $lastSegmentLength = $i - $lastSlash - 1;
                }

                $lastSlash = $i;
                $dots = 0;
            } elseif ($code === static::CHAR_DOT && $dots !== -1) {
                ++$dots;
            } else {
                $dots = -1;
            }
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public static function format($pathData): string
    {
        if (!is_array($pathData) && !($pathData instanceof PathObjectInterface)) {
            throw new InvalidArgumentException(sprintf(
                'Path data passed to %s::format() needs to either be an instance of %s or an associative array containing path information',
                static::class,
                PathObjectInterface::class
            ));
        }

        if (is_array($pathData)) {
            $pathData = array_merge([
                'root' => '',
                'dir' => '',
                'base' => '',
                'ext' => '',
                'name' => ''
            ], $pathData);
        }

        $dir = $pathData['dir'] ?: $pathData['root'];
        $base = $pathData['base'] ?: ($pathData['name'] ?: '') . ($pathData['ext'] ?: '');

        if (!$dir) {
            return $base;
        }

        return $dir === $pathData['root'] ? $dir . $base : $dir . static::getSeparator() . $base;
    }

    /**
     * Slices a substring from a string given a start end end index, matches JavaScript's String.prototype.slice
     *
     * @param string       $value      The string to slice
     * @param integer      $startIndex The index to start at. May be negative to start relative to the end of the string.
     * @param integer|null $endIndex   The index to stop at. If omitted, the returned slice will end at the end of $value
     * @return string The sliced substring
     */
    protected static function slice(string $value, int $startIndex, ?int $endIndex = null): string
    {
        if (is_null($endIndex)) {
            return substr($value, $startIndex) ?: '';
        } else {
            return substr(
                $value,
                $startIndex,
                is_null($endIndex) ? null : ($endIndex - $startIndex)
            ) ?: '';
        }
    }
}
