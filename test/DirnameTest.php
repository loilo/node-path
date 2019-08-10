<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\Path;
use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class DirnameTest extends TestCase
{
    use Helpers\DetectWindowsTrait;

    public function testDirnameDetection(): void
    {
        $this->assertSame($this::$isWindows ? '\\test' : '/test', substr(Path::dirname(__file__), -5));

        $this->assertSame('/a', PosixPath::dirname('/a/b/'));
        $this->assertSame('/a', PosixPath::dirname('/a/b'));
        $this->assertSame('/', PosixPath::dirname('/a'));
        $this->assertSame('.', PosixPath::dirname(''));
        $this->assertSame('/', PosixPath::dirname('/'));
        $this->assertSame('/', PosixPath::dirname('////'));
        $this->assertSame('//', PosixPath::dirname('//a'));
        $this->assertSame('.', PosixPath::dirname('foo'));

        $this->assertSame('c:\\', WindowsPath::dirname('c:\\'));
        $this->assertSame('c:\\', WindowsPath::dirname('c:\\foo'));
        $this->assertSame('c:\\', WindowsPath::dirname('c:\\foo\\'));
        $this->assertSame('c:\\foo', WindowsPath::dirname('c:\\foo\\bar'));
        $this->assertSame('c:\\foo', WindowsPath::dirname('c:\\foo\\bar\\'));
        $this->assertSame('c:\\foo\\bar', WindowsPath::dirname('c:\\foo\\bar\\baz'));
        $this->assertSame('c:\\foo bar', WindowsPath::dirname('c:\\foo bar\\baz'));
        $this->assertSame('\\', WindowsPath::dirname('\\'));
        $this->assertSame('\\', WindowsPath::dirname('\\foo'));
        $this->assertSame('\\', WindowsPath::dirname('\\foo\\'));
        $this->assertSame('\\foo', WindowsPath::dirname('\\foo\\bar'));
        $this->assertSame('\\foo', WindowsPath::dirname('\\foo\\bar\\'));
        $this->assertSame('\\foo\\bar', WindowsPath::dirname('\\foo\\bar\\baz'));
        $this->assertSame('\\foo bar', WindowsPath::dirname('\\foo bar\\baz'));
        $this->assertSame('c:', WindowsPath::dirname('c:'));
        $this->assertSame('c:', WindowsPath::dirname('c:foo'));
        $this->assertSame('c:', WindowsPath::dirname('c:foo\\'));
        $this->assertSame('c:foo', WindowsPath::dirname('c:foo\\bar'));
        $this->assertSame('c:foo', WindowsPath::dirname('c:foo\\bar\\'));
        $this->assertSame('c:foo\\bar', WindowsPath::dirname('c:foo\\bar\\baz'));
        $this->assertSame('c:foo bar', WindowsPath::dirname('c:foo bar\\baz'));
        $this->assertSame('.', WindowsPath::dirname('file:stream'));
        $this->assertSame('dir', WindowsPath::dirname('dir\\file:stream'));
        $this->assertSame('\\\\unc\\share', WindowsPath::dirname('\\\\unc\\share'));
        $this->assertSame('\\\\unc\\share\\', WindowsPath::dirname('\\\\unc\\share\\foo'));
        $this->assertSame('\\\\unc\\share\\', WindowsPath::dirname('\\\\unc\\share\\foo\\'));
        $this->assertSame('\\\\unc\\share\\foo', WindowsPath::dirname('\\\\unc\\share\\foo\\bar'));
        $this->assertSame('\\\\unc\\share\\foo', WindowsPath::dirname('\\\\unc\\share\\foo\\bar\\'));
        $this->assertSame('\\\\unc\\share\\foo\\bar', WindowsPath::dirname('\\\\unc\\share\\foo\\bar\\baz'));
        $this->assertSame('/a', WindowsPath::dirname('/a/b/'));
        $this->assertSame('/a', WindowsPath::dirname('/a/b'));
        $this->assertSame('/', WindowsPath::dirname('/a'));
        $this->assertSame('.', WindowsPath::dirname(''));
        $this->assertSame('/', WindowsPath::dirname('/'));
        $this->assertSame('/', WindowsPath::dirname('////'));
        $this->assertSame('.', WindowsPath::dirname('foo'));
    }
}
