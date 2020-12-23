# Node.js `path` Module
[![Tests](https://badgen.net/github/checks/loilo/node-path/master)](https://github.com/loilo/node-path/actions)
[![Version on packagist.org](https://badgen.net/packagist/v/loilo/node-path)](https://packagist.org/packages/loilo/node-path)

This package is a port of Node.js' [`path`](https://nodejs.org/docs/latest-v12.x/api/path.html#path_path) module to PHP.

Ported code and docs were created from Node.js v12.8.0.

## Install
```bash
composer require loilo/node-path
```

## Usage
### Example

Executed on a Unix system (see [Windows vs. POSIX](#windows-vs-posix)):
```php
use Loilo\NodePath\Path;

Path::basename('/foo/bar/baz/asdf/quux.html') === 'quux.html';
Path::basename('/foo/bar/baz/asdf/quux.html', '.html') === 'quux';

Path::dirname('/foo/bar/baz/asdf/quux') === '/foo/bar/baz/asdf';

Path::extname('index.html') === '.html';
Path::extname('index.coffee.md') === '.md';
Path::extname('index.') === '.';
Path::extname('index') === '';
Path::extname('.index') === '';
Path::extname('.index.md') === '.md';

// If $dir, $root and $base are provided,
// $dir . Path::getSeparator() . $base
// will be returned. $root is ignored.
Path::format([
  'root' => '/ignored',
  'dir' => '/home/user/dir',
  'base' => 'file.txt'
]) === '/home/user/dir/file.txt';

// $root will be used if $dir is not specified.
// If only $root is provided or $dir is equal to $root then the
// platform separator will not be included. $ext will be ignored.
Path::format([
  'root' => '/',
  'base' => 'file.txt',
  'ext' => 'ignored'
]) === '/file.txt';

// $name . $ext will be used if $base is not specified.
Path::format([
  'root' => '/',
  'name' => 'file',
  'ext' => '.txt'
]) === '/file.txt';

Path::getDelimiter() === ':';

Path::getSeparator() === '/';

Path::isAbsolute('/foo/bar') === true;
Path::isAbsolute('/baz/..') === true;
Path::isAbsolute('qux/') === false;
Path::isAbsolute('.') === false;

Path::join('/foo', 'bar', 'baz/asdf', 'quux', '..') === '/foo/bar/baz/asdf';

Path::normalize('/foo/bar//baz/asdf/quux/..') === '/foo/bar/baz/asdf';

Path::parse('/home/user/dir/file.txt');
// Returns an instance of Loilo\NodePath\PathObjectInterface
// representing path components.
// Can be cast to an array by calling toArray() on it.

Path::relative('/data/orandea/test/aaa', '/data/orandea/impl/bbb') === '../../impl/bbb';

Path::resolve('/foo/bar', './baz') === '/foo/bar/baz';
Path::resolve('/foo/bar', '/tmp/file/') === '/tmp/file';
Path::resolve('wwwroot', 'static_files/png/', '../gif/image.gif');
// If the current working directory is /home/myself/node,
// this returns '/home/myself/node/wwwroot/static_files/gif/image.gif'
```

### Windows vs. POSIX
The default operation of this package varies based on the operating system on which your PHP application is running. Specifically, when running on a Windows operating system, it will assume that Windows-style paths are being used.

So using `Path::basename()` might yield different results on POSIX and Windows:

On POSIX:
```php
Path::basename('C:\\temp\\myfile.html') === 'C:\\temp\\myfile.html';
```

On Windows:
```php
Path::basename('C:\\temp\\myfile.html') === 'myfile.html';
```

To achieve consistent results when working with Windows file paths on any operating system, use `Loilo\NodePath\WindowsPath`:

On POSIX and Windows:
```php
use Loilo\NodePath\WindowsPath;

WindowsPath::basename('C:\\temp\\myfile.html') === 'myfile.html';
```

To achieve consistent results when working with POSIX file paths on any operating system, use `Loilo\NodePath\PosixPath`:

On POSIX and Windows:
```php
use Loilo\NodePath\PosixPath;

PosixPath::basename('/tmp/myfile.html') === 'myfile.html';
```

On Windows, this package follows the concept of per-drive working directory. This behavior can be observed when using a drive path without a backslash. For example, `Path::resolve('c:\\')` can potentially return a different result than `Path::resolve('c:')`. For more information, see [this MSDN page](https://docs.microsoft.com/en-us/windows/desktop/FileIO/naming-a-file#fully-qualified-vs-relative-paths).

### API
This is the full API of the [`Loilo\NodePath\PathInterface`](src/PathInterface.php) which is implemented by both the [`WindowsPath`](src/WindowsPath.php) and the [`PosixPath`](src/PosixPath.php) class.

> **Note:** If not stated otherwise, `PathInterface` methods are assumed to be executed under a Unix environment.

#### `basename ( string $path [, ext: $suffix ] ) : string`
Returns the last portion of `$path`, similar to the Unix `basename` command. Trailing directory separators are ignored, see [`getSeparator()`](#getseparator--void---string).

#### `dirname ( string $path ) : string`
Returns the directory name of a path, similar to the Unix `dirname` command. Trailing directory separators are ignored, see [`getSeparator()`](#getseparator--void---string).

#### `extname ( string $path ) : string`
Returns the extension of the `$path`, from the last occurrence of the `.` (period) character to end of string in the last portion of the `$path`. If there is no `.` in the last portion of the `$path`, or if there are no `.` characters other than the first character of the basename of `$path` (see [`basename()`](#basename--string-path--ext-suffix----string)) , an empty string is returned.

#### `format ( Loilo\NodePath\PathObjectInterface|array $pathData ) : string`
Returns a path string from an associative array or a PathObjectInterface instance. This is the opposite of [`parse()`](#parse--string-path---loilonodepathpathobjectinterface).

When providing properties to the `$pathData` remember that there are combinations where one property has priority over another:

- `$pathData['root']` is ignored if `$pathData['dir']` is provided.
- `$pathData['ext']` and `$pathData['name']` are ignored if `$pathData['base']` exists.

#### `getDelimiter ( void ) : string`
Provides the platform-specific path delimiter:
- `;` on Windows
- `:` on POSIX

#### `getSeparator ( void ) : string`
Provides the platform-specific path segment separator:
- `\` on Windows
- `/` on POSIX

#### `isAbsolute ( string $path ) : bool`
Determines if `$path` is an absolute path.

If the given path is a zero-length string, `false` will be returned.

#### `join ([ array $... ] ) : string`
Joins all given path segments together using the platform-specific separator as a delimiter, then normalizes the resulting path.

Zero-length path segments are ignored. If the joined path string is a zero-length string then `'.'` will be returned, representing the current working directory.

#### `normalize ( string $path ) : string`
Normalizes the given `$path`, resolving `..` and `.` segments.

When multiple, sequential path segment separation characters are found (e.g. `/` on POSIX and either `\` or `/` on Windows), they are replaced by a single instance of the platform-specific path segment separator (`/` on POSIX and `\` on Windows). Trailing separators are preserved.

If the path is a zero-length string, `'.'` is returned, representing the current working directory.

#### `parse ( string $path ) : Loilo\NodePath\PathObjectInterface`
Returns an object whose properties represent significant elements of the path. Trailing directory separators are ignored, see [`getSeparator()`](#getseparator--void---string).

#### `relative ( string $from, string $to ) : string`
Returns the relative path from `$from` to `$to` based on the current working directory. If `$from` and `$to` each resolve to the same path (after calling [`resolve()`](#resolve--array-----string) on each), a zero-length string is returned.

If a zero-length string is passed as `$from` or `$to`, the current working directory will be used instead of the zero-length strings.

#### `resolve ([ array $... ] ) : string`
Resolves a sequence of paths or path segments into an absolute path.

The given sequence of paths is processed from right to left, with each subsequent path prepended until an absolute path is constructed. For instance, given the sequence of path segments: `/foo`, `/bar`, `baz`, calling `PathInterface::resolve('/foo', '/bar', 'baz')` would return `/bar/baz`.

If after processing all given path segments an absolute path has not yet been generated, the current working directory is used.

The resulting path is normalized and trailing slashes are removed unless the path is resolved to the root directory.

Zero-length path segments are ignored.

If no path segments are passed, `PathInterface::resolve()` will return the absolute path of the current working directory.

#### `toNamespacedPath ([ mixed $path = null ] ) : mixed`
On Windows systems only, returns an equivalent [namespace-prefixed path](https://docs.microsoft.com/en-us/windows/desktop/FileIO/naming-a-file#namespaces) for the given path. If `$path` is not a string, it will be returned without modifications.

This method is meaningful only on Windows system. On POSIX systems, the method is non-operational and always returns `$path` without modifications.
