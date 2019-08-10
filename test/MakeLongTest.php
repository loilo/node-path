<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\Path;
use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class MakeLongTest extends TestCase
{
    use Helpers\DetectWindowsTrait;

    public function testToNamespacedPath(): void
    {
        $this->assertSame('', Path::toNamespacedPath(''));
        $this->assertSame(null, Path::toNamespacedPath(null));
        $this->assertSame(100, Path::toNamespacedPath(100));
        $this->assertSame(false, Path::toNamespacedPath(false));
        $this->assertSame(true, Path::toNamespacedPath(true));
        $this->assertSame('/foo/bar', PosixPath::toNamespacedPath('/foo/bar'));
        $this->assertSame('foo/bar', PosixPath::toNamespacedPath('foo/bar'));
        $this->assertSame(null, PosixPath::toNamespacedPath(null));
        $this->assertSame(true, PosixPath::toNamespacedPath(true));
        $this->assertSame(1, PosixPath::toNamespacedPath(1));
        $this->assertSame(null, PosixPath::toNamespacedPath());
        $this->assertSame([], PosixPath::toNamespacedPath([]));
        $this->assertSame('\\\\?\\C:\\foo', WindowsPath::toNamespacedPath('C:\\foo'));
        $this->assertSame('\\\\?\\C:\\foo', WindowsPath::toNamespacedPath('C:/foo'));
        $this->assertSame('\\\\?\\UNC\\foo\\bar\\', WindowsPath::toNamespacedPath('\\\\foo\\bar'));
        $this->assertSame('\\\\?\\UNC\\foo\\bar\\', WindowsPath::toNamespacedPath('//foo//bar'));
        $this->assertSame('\\\\?\\foo', WindowsPath::toNamespacedPath('\\\\?\\foo'));
        $this->assertSame(null, WindowsPath::toNamespacedPath(null));
        $this->assertSame(true, WindowsPath::toNamespacedPath(true));
        $this->assertSame(1, WindowsPath::toNamespacedPath(1));
        $this->assertSame(null, WindowsPath::toNamespacedPath());
        $this->assertSame([], WindowsPath::toNamespacedPath([]));
    }

    public function testWindows()
    {
        // These tests cause resolve() to use the cwd, so we cannot test them from
        // non-Windows platforms (easily)
        if (!static::$isWindows) {
            $this->markTestSkipped('Windows-only test');
            return;
        }

        $file = __FILE__;

        $this->assertSame("\\\\?\\$file", Path::toNamespacedPath($file));
        $this->assertSame("\\\\?\\$file", Path::toNamespacedPath("\\\\?\\$file"));
        $this->assertSame(
            '\\\\?\\UNC\\someserver\\someshare\\somefile',
            Path::toNamespacedPath('\\\\someserver\\someshare\\somefile')
        );
        $this->assertSame(
            '\\\\?\\UNC\\someserver\\someshare\\somefile',
            Path::toNamespacedPath('\\\\?\\UNC\\someserver\\someshare\\somefile')
        );
        $this->assertSame('\\\\.\\pipe\\somepipe', Path::toNamespacedPath('\\\\.\\pipe\\somepipe'));

        $cwd = getcwd();
        $lowerCaseCwd = strtolower($cwd);

        $this->assertSame('', Path::toNamespacedPath(''));
        $this->assertSame(
            "\\\\?\\$lowerCaseCwd\\foo\\bar",
            strtolower(Path::toNamespacedPath('foo\\bar'))
        );
        $this->assertSame(
            "\\\\?\\$lowerCaseCwd\\foo\\bar",
            strtolower(Path::toNamespacedPath('foo/bar'))
        );

        $currentDeviceLetter = substr(Path::parse($cwd)['root'], 0, 2);
        $this->assertSame(
            "\\\\?\\$lowerCaseCwd",
            strtolower(Path::toNamespacedPath($currentDeviceLetter)),
            sprintf(
                'Namespacing with the drive letter "%s" did not yield the expected result',
                $currentDeviceLetter
            )
        );
        $this->assertSame(
            "\\\\?\\$lowerCaseCwd\\c",
            strtolower(Path::toNamespacedPath('C'))
        );
    }
}
