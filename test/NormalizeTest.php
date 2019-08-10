<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class NormalizeTest extends TestCase
{
    public function testNormalize(): void
    {
        $this->assertSame('fixtures\\b\\c.js', WindowsPath::normalize('./fixtures///b/../b/c.js'));
        $this->assertSame('\\bar', WindowsPath::normalize('/foo/../../../bar'));
        $this->assertSame('a\\b', WindowsPath::normalize('a//b//../b'));
        $this->assertSame('a\\b\\c', WindowsPath::normalize('a//b//./c'));
        $this->assertSame('a\\b', WindowsPath::normalize('a//b//.'));
        $this->assertSame('\\\\server\\share\\dir\\file.ext', WindowsPath::normalize('//server/share/dir/file.ext'));
        $this->assertSame('\\x\\y\\z', WindowsPath::normalize('/a/b/c/../../../x/y/z'));
        $this->assertSame('C:.', WindowsPath::normalize('C:'));
        $this->assertSame('C:..\\abc', WindowsPath::normalize('C:..\\abc'));
        $this->assertSame('C:..\\..\\def', WindowsPath::normalize('C:..\\..\\abc\\..\\def'));
        $this->assertSame('C:\\', WindowsPath::normalize('C:\\.'));
        $this->assertSame('file:stream', WindowsPath::normalize('file:stream'));
        $this->assertSame('bar\\', WindowsPath::normalize('bar\\foo..\\..\\'));
        $this->assertSame('bar', WindowsPath::normalize('bar\\foo..\\..'));
        $this->assertSame('bar\\baz', WindowsPath::normalize('bar\\foo..\\..\\baz'));
        $this->assertSame('bar\\foo..\\', WindowsPath::normalize('bar\\foo..\\'));
        $this->assertSame('bar\\foo..', WindowsPath::normalize('bar\\foo..'));
        $this->assertSame('..\\..\\bar', WindowsPath::normalize('..\\foo..\\..\\..\\bar'));
        $this->assertSame('..\\..\\bar', WindowsPath::normalize('..\\...\\..\\.\\...\\..\\..\\bar'));
        $this->assertSame('..\\..\\..\\..\\..\\bar', WindowsPath::normalize('../../../foo/../../../bar'));
        $this->assertSame('..\\..\\..\\..\\..\\..\\', WindowsPath::normalize('../../../foo/../../../bar/../../'));
        $this->assertSame('..\\..\\', WindowsPath::normalize('../foobar/barfoo/foo/../../../bar/../../'));
        $this->assertSame('..\\..\\..\\..\\baz', WindowsPath::normalize('../.../../foobar/../../../bar/../../baz'));
        $this->assertSame('foo\\bar\\baz', WindowsPath::normalize('foo/bar\\baz'));
        $this->assertSame('fixtures/b/c.js', PosixPath::normalize('./fixtures///b/../b/c.js'));
        $this->assertSame('/bar', PosixPath::normalize('/foo/../../../bar'));
        $this->assertSame('a/b', PosixPath::normalize('a//b//../b'));
        $this->assertSame('a/b/c', PosixPath::normalize('a//b//./c'));
        $this->assertSame('a/b', PosixPath::normalize('a//b//.'));
        $this->assertSame('/x/y/z', PosixPath::normalize('/a/b/c/../../../x/y/z'));
        $this->assertSame('/foo/bar', PosixPath::normalize('///..//./foo/.//bar'));
        $this->assertSame('bar/', PosixPath::normalize('bar/foo../../'));
        $this->assertSame('bar', PosixPath::normalize('bar/foo../..'));
        $this->assertSame('bar/baz', PosixPath::normalize('bar/foo../../baz'));
        $this->assertSame('bar/foo../', PosixPath::normalize('bar/foo../'));
        $this->assertSame('bar/foo..', PosixPath::normalize('bar/foo..'));
        $this->assertSame('../../bar', PosixPath::normalize('../foo../../../bar'));
        $this->assertSame('../../bar', PosixPath::normalize('../.../.././.../../../bar'));
        $this->assertSame('../../../../../bar', PosixPath::normalize('../../../foo/../../../bar'));
        $this->assertSame('../../../../../../', PosixPath::normalize('../../../foo/../../../bar/../../'));
        $this->assertSame('../../', PosixPath::normalize('../foobar/barfoo/foo/../../../bar/../../'));
        $this->assertSame('../../../../baz', PosixPath::normalize('../.../../foobar/../../../bar/../../baz'));
        $this->assertSame('foo/bar\\baz', PosixPath::normalize('foo/bar\\baz'));
    }
}
