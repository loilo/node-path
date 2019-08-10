<?php namespace Loilo\NodePath;

/**
 * Provides utilities for working with file and directory paths
 */
interface PathInterface
{
    /**
     * Returns the last portion of a $path, similar to the Unix `basename` command. Trailing directory separators are ignored, see PathInterface::getSeparator().
     *
     * @param string $path
     * @param string|null $ext An optional file extension
     * @return string
     */
    public static function basename(string $path, ?string $ext = null): string;

    /**
     * Returns the directory name of a path, similar to the Unix `dirname` command. Trailing directory separators are ignored, see PathInterface::getSeparator().
     */
    public static function dirname(string $path): string;

    /**
     * Returns the extension of the $path, from the last occurrence of the `.` (period) character to end of string in the last portion of the $path. If there is no `.` in the last portion of the $path, or if there are no `.` characters other than the first character of the basename of $path (see PathInterface::basename()) , an empty string is returned.
     *
     * @param string $path
     * @return string
     */
    public static function extname(string $path): string;

    /**
     * Returns a path string from an associative array or a PathObjectInterface instance. This is the opposite of PathInterface::parse().
     *
     * When providing properties to the $pathData remember that there are combinations where one property has priority over another:
     * - $pathData['root'] is ignored if $pathData['dir'] is provided.
     * - $pathData['ext'] and $pathData['name'] are ignored if $pathData['base'] exists.
     *
     * @param PathObjectInterface|array $pathData
     * @return string
     */
    public static function format($pathData): string;

    /**
     * Provides the platform-specific path delimiter:
     * - `;` for Windows
     * - `:` for POSIX
     *
     * @return string
     */
    public static function getDelimiter(): string;

    /**
     * Provides the platform-specific path segment separator:
     * - `\` on Windows
     * - `/` on POSIX
     *
     * @return string
     */
    public static function getSeparator(): string;

    /**
     * Determines if $path is an absolute path.
     *
     * If the given path is a zero-length string, `false` will be returned.
     *
     * @param string $path
     * @return bool
     */
    public static function isAbsolute(string $path): bool;

    /**
     * Joins all given path segments together using the platform-specific separator as a delimiter, then normalizes the resulting path.
     *
     * Zero-length path segments are ignored. If the joined path string is a zero-length string then `'.'` will be returned, representing the current working directory.
     *
     * @param string[] ...$paths A sequence of path segments
     * @return string
     */
    public static function join(...$paths): string;

    /**
     * Normalizes the given $path, resolving '..' and '.' segments.
     *
     * When multiple, sequential path segment separation characters are found (e.g. `/` on POSIX and either `\` or `/` on Windows), they are replaced by a single instance of the platform-specific path segment separator (`/` on POSIX and `\` on Windows). Trailing separators are preserved.
     *
     * If the path is a zero-length string, `'.'` is returned, representing the current working directory.
     *
     * @param string $path
     * @return string
     */
    public static function normalize(string $path): string;

    /**
     * Returns an object whose properties represent significant elements of the path. Trailing directory separators are ignored, see PathInterface::getSeparator().
     *
     * @param string $path
     * @return PathObjectInterface
     */
    public static function parse(string $path): PathObjectInterface;

    /**
     * Returns the relative path from $from to $to based on the current working directory. If $from and $to each resolve to the same path (after calling PathInterface::resolve() on each), a zero-length string is returned.
     *
     * If a zero-length string is passed as $from or $to, the current working directory will be used instead of the zero-length strings.
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function relative(string $from, string $to): string;

    /**
     * Resolves a sequence of paths or path segments into an absolute path.
     *
     * The given sequence of paths is processed from right to left, with each subsequent path prepended until an absolute path is constructed. For instance, given the sequence of path segments: `/foo`, `/bar`, `baz`, calling `PathInterface::resolve('/foo', '/bar', 'baz')` would return `/bar/baz`.
     *
     * If after processing all given path segments an absolute path has not yet been generated, the current working directory is used.
     *
     * The resulting path is normalized and trailing slashes are removed unless the path is resolved to the root directory.
     *
     * Zero-length path segments are ignored.
     *
     * If no path segments are passed, PathInterface::resolve() will return the absolute path of the current working directory.
     *
     * @param string[] ...$paths A sequence of paths or path segments
     * @return string
     */
    public static function resolve(...$paths): string;

    /**
     * On Windows systems only, returns an equivalent namespace-prefixed path for the given path. If path is not a string, path will be returned without modifications.
     *
     * This method is meaningful only on Windows system. On POSIX systems, the method is non-operational and always returns path without modifications.
     *
     * @param mixed $path
     * @return mixed
     */
    public static function toNamespacedPath($path = null);
}
