<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class ResolveTest extends TestCase
{
    use Helpers\DetectWindowsTrait;
    use Helpers\MessageFormatterTrait;

    public function testWindowsResolve()
    {
        $tests = [
            [['c:/blah\\blah', 'd:/games', 'c:../a'], 'c:\\blah\\a'],
            [['c:/ignore', 'd:\\a/b\\c/d', '\\e.exe'], 'd:\\e.exe'],
            [['c:/ignore', 'c:/some/file'], 'c:\\some\\file'],
            [['d:/ignore', 'd:some/dir//'], 'd:\\ignore\\some\\dir'],
            [['.'], getcwd()],
            [['//server/share', '..', 'relative\\'], '\\\\server\\share\\relative'],
            [['c:/', '//'], 'c:\\'],
            [['c:/', '//dir'], 'c:\\dir'],
            [['c:/', '//server/share'], '\\\\server\\share\\'],
            [['c:/', '//server//share'], '\\\\server\\share\\'],
            [['c:/', '///some//dir'], 'c:\\some\\dir'],
            [['C:\\foo\\tmp.3\\', '..\\tmp.3\\cycles\\root.js'], 'C:\\foo\\tmp.3\\cycles\\root.js']
        ];

        foreach ($tests as [ $args, $expected ]) {
            $actual = [ WindowsPath::resolve(...$args) ];

            if (!static::$isWindows) {
                $actual[] = str_replace('\\', '/', $actual[0]);
            }

            $this->assertContains($expected, $actual, $this->formatMessage(
                [ WindowsPath::class, 'resolve' ],
                $args,
                $expected,
                $actual[0]
            ));
        }
    }

    public function testPosixResolve()
    {
        $tests = [
            [['/var/lib', '../', 'file/'], '/var/file'],
            [['/var/lib', '/../', 'file/'], '/file'],
            [['a/b/c/', '../../..'], getcwd()],
            [['.'], getcwd()],
            [['/some/dir', '.', '/absolute/'], '/absolute'],
            [['/foo/tmp.3/', '../tmp.3/cycles/root.js'], '/foo/tmp.3/cycles/root.js']
        ];

        foreach ($tests as [ $args, $expected ]) {
            $actual = [ PosixPath::resolve(...$args) ];

            if (static::$isWindows) {
                $actual[] = str_replace('/', '\\', $actual[0]);
            }

            $this->assertContains($expected, $actual, $this->formatMessage(
                [ PosixPath::class, 'resolve' ],
                $args,
                $expected,
                $actual[0]
            ));
        }
    }
}
