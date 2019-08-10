<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class JoinTest extends TestCase
{
    use Helpers\MessageFormatterTrait;

    protected $tests = [
        [['.', 'x/b', '..', '/b/c.js'], 'x/b/c.js'],
        [[], '.'],
        [['/.', 'x/b', '..', '/b/c.js'], '/x/b/c.js'],
        [['/foo', '../../../bar'], '/bar'],
        [['foo', '../../../bar'], '../../bar'],
        [['foo/', '../../../bar'], '../../bar'],
        [['foo/x', '../../../bar'], '../bar'],
        [['foo/x', './bar'], 'foo/x/bar'],
        [['foo/x/', './bar'], 'foo/x/bar'],
        [['foo/x/', '.', 'bar'], 'foo/x/bar'],
        [['./'], './'],
        [['.', './'], './'],
        [['.', '.', '.'], '.'],
        [['.', './', '.'], '.'],
        [['.', '/./', '.'], '.'],
        [['.', '/////./', '.'], '.'],
        [['.'], '.'],
        [['', '.'], '.'],
        [['', 'foo'], 'foo'],
        [['foo', '/bar'], 'foo/bar'],
        [['', '/foo'], '/foo'],
        [['', '', '/foo'], '/foo'],
        [['', '', 'foo'], 'foo'],
        [['foo', ''], 'foo'],
        [['foo/', ''], 'foo/'],
        [['foo', '', '/bar'], 'foo/bar'],
        [['./', '..', '/foo'], '../foo'],
        [['./', '..', '..', '/foo'], '../../foo'],
        [['.', '..', '..', '/foo'], '../../foo'],
        [['', '..', '..', '/foo'], '../../foo'],
        [['/'], '/'],
        [['/', '.'], '/'],
        [['/', '..'], '/'],
        [['/', '..', '..'], '/'],
        [[''], '.'],
        [['', ''], '.'],
        [[' /foo'], ' /foo'],
        [[' ', 'foo'], ' /foo'],
        [[' ', '.'], ' '],
        [[' ', '/'], ' /'],
        [[' ', ''], ' '],
        [['/', 'foo'], '/foo'],
        [['/', '/foo'], '/foo'],
        [['/', '//foo'], '/foo'],
        [['/', '', '/foo'], '/foo'],
        [['', '/', 'foo'], '/foo'],
        [['', '/', '/foo'], '/foo']
    ];

    public function testWindowsJoin(): void
    {
        // Add Windows-specific join tests
        $tests = array_merge($this->tests, [
            // Arguments                     result
            // UNC path expected
            [['//foo/bar'], '\\\\foo\\bar\\'],
            [['\\/foo/bar'], '\\\\foo\\bar\\'],
            [['\\\\foo/bar'], '\\\\foo\\bar\\'],
            // UNC path expected - server and share separate
            [['//foo', 'bar'], '\\\\foo\\bar\\'],
            [['//foo/', 'bar'], '\\\\foo\\bar\\'],
            [['//foo', '/bar'], '\\\\foo\\bar\\'],
            // UNC path expected - questionable
            [['//foo', '', 'bar'], '\\\\foo\\bar\\'],
            [['//foo/', '', 'bar'], '\\\\foo\\bar\\'],
            [['//foo/', '', '/bar'], '\\\\foo\\bar\\'],
            // UNC path expected - even more questionable
            [['', '//foo', 'bar'], '\\\\foo\\bar\\'],
            [['', '//foo/', 'bar'], '\\\\foo\\bar\\'],
            [['', '//foo/', '/bar'], '\\\\foo\\bar\\'],
            // No UNC path expected (no double slash in first component)
            [['\\', 'foo/bar'], '\\foo\\bar'],
            [['\\', '/foo/bar'], '\\foo\\bar'],
            [['', '/', '/foo/bar'], '\\foo\\bar'],
            // No UNC path expected (no non-slashes in first component -
            // questionable)
            [['//', 'foo/bar'], '\\foo\\bar'],
            [['//', '/foo/bar'], '\\foo\\bar'],
            [['\\\\', '/', '/foo/bar'], '\\foo\\bar'],
            [['//'], '\\'],
            // No UNC path expected (share name missing - questionable).
            [['//foo'], '\\foo'],
            [['//foo/'], '\\foo\\'],
            [['//foo', '/'], '\\foo\\'],
            [['//foo', '', '/'], '\\foo\\'],
            // No UNC path expected (too many leading slashes - questionable)
            [['///foo/bar'], '\\foo\\bar'],
            [['////foo', 'bar'], '\\foo\\bar'],
            [['\\\\\\/foo/bar'], '\\foo\\bar'],
            // Drive-relative vs drive-absolute paths. This merely describes the
            // status quo, rather than being obviously right
            [['c:'], 'c:.'],
            [['c:.'], 'c:.'],
            [['c:', ''], 'c:.'],
            [['', 'c:'], 'c:.'],
            [['c:.', '/'], 'c:.\\'],
            [['c:.', 'file'], 'c:file'],
            [['c:', '/'], 'c:\\'],
            [['c:', 'file'], 'c:\\file']
        ]);

        foreach ($tests as [$args, $expected]) {
            $actual = [ WindowsPath::join(...$args) ];

            // For non-Windows specific tests with the Windows join(), we need to try
            // replacing the slashes since the non-Windows specific tests' `expected`
            // use forward slashes
            $actual[] = str_replace('\\', '/', $actual[0]);

            $this->assertContains($expected, $actual, $this->formatMessage(
                [ WindowsPath::class, 'extname' ],
                $args,
                $expected,
                $actual[0]
            ));
        }
    }

    public function testPosixJoin(): void
    {
        foreach ($this->tests as [$args, $expected]) {
            $actual = PosixPath::join(...$args);

            $this->assertSame($expected, $actual, $this->formatMessage(
                [ PosixPath::class, 'join' ],
                $args,
                $expected,
                $actual
            ));
        }
    }
}
