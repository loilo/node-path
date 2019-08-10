<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class RelativeTest extends TestCase
{
    use Helpers\MessageFormatterTrait;

    public function testWindowsRelative()
    {
        $tests = [
            ['c:/blah\\blah', 'd:/games', 'd:\\games'],
            ['c:/aaaa/bbbb', 'c:/aaaa', '..'],
            ['c:/aaaa/bbbb', 'c:/cccc', '..\\..\\cccc'],
            ['c:/aaaa/bbbb', 'c:/aaaa/bbbb', ''],
            ['c:/aaaa/bbbb', 'c:/aaaa/cccc', '..\\cccc'],
            ['c:/aaaa/', 'c:/aaaa/cccc', 'cccc'],
            ['c:/', 'c:\\aaaa\\bbbb', 'aaaa\\bbbb'],
            ['c:/aaaa/bbbb', 'd:\\', 'd:\\'],
            ['c:/AaAa/bbbb', 'c:/aaaa/bbbb', ''],
            ['c:/aaaaa/', 'c:/aaaa/cccc', '..\\aaaa\\cccc'],
            ['C:\\foo\\bar\\baz\\quux', 'C:\\', '..\\..\\..\\..'],
            ['C:\\foo\\test', 'C:\\foo\\test\\bar\\package.json', 'bar\\package.json'],
            ['C:\\foo\\bar\\baz-quux', 'C:\\foo\\bar\\baz', '..\\baz'],
            ['C:\\foo\\bar\\baz', 'C:\\foo\\bar\\baz-quux', '..\\baz-quux'],
            ['\\\\foo\\bar', '\\\\foo\\bar\\baz', 'baz'],
            ['\\\\foo\\bar\\baz', '\\\\foo\\bar', '..'],
            ['\\\\foo\\bar\\baz-quux', '\\\\foo\\bar\\baz', '..\\baz'],
            ['\\\\foo\\bar\\baz', '\\\\foo\\bar\\baz-quux', '..\\baz-quux'],
            ['C:\\baz-quux', 'C:\\baz', '..\\baz'],
            ['C:\\baz', 'C:\\baz-quux', '..\\baz-quux'],
            ['\\\\foo\\baz-quux', '\\\\foo\\baz', '..\\baz'],
            ['\\\\foo\\baz', '\\\\foo\\baz-quux', '..\\baz-quux'],
            ['C:\\baz', '\\\\foo\\bar\\baz', '\\\\foo\\bar\\baz'],
            ['\\\\foo\\bar\\baz', 'C:\\baz', 'C:\\baz']
        ];

        foreach ($tests as [ $from, $to, $expected ]) {
            $actual = WindowsPath::relative($from, $to);

            $this->assertSame($expected, $actual, $this->formatMessage(
                [ WindowsPath::class, 'relative' ],
                [ $from, $to ],
                $expected,
                $actual
            ));
        }
    }

    public function testPosixRelative()
    {
        $tests = [
            ['/var/lib', '/var', '..'],
            ['/var/lib', '/bin', '../../bin'],
            ['/var/lib', '/var/lib', ''],
            ['/var/lib', '/var/apache', '../apache'],
            ['/var/', '/var/lib', 'lib'],
            ['/', '/var/lib', 'var/lib'],
            ['/foo/test', '/foo/test/bar/package.json', 'bar/package.json'],
            ['/Users/a/web/b/test/mails', '/Users/a/web/b', '../..'],
            ['/foo/bar/baz-quux', '/foo/bar/baz', '../baz'],
            ['/foo/bar/baz', '/foo/bar/baz-quux', '../baz-quux'],
            ['/baz-quux', '/baz', '../baz'],
            ['/baz', '/baz-quux', '../baz-quux'],
            ['/page1/page2/foo', '/', '../../..']
        ];

        foreach ($tests as [ $from, $to, $expected ]) {
            $actual = PosixPath::relative($from, $to);

            $this->assertSame($expected, $actual, $this->formatMessage(
                [ PosixPath::class, 'relative' ],
                [ $from, $to ],
                $expected,
                $actual
            ));
        }
    }
}
