<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use InvalidArgumentException;
use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class ParseFormatTest extends TestCase
{
    use Helpers\MessageFormatterTrait;

    public function testWindowsTrailingRemoval()
    {
        $tests = [
            '.\\' => [
                'root' => '',
                'dir' => '',
                'base' => '.',
                'ext' => '',
                'name' => '.'
            ],
            '\\\\' => [
                'root' => '\\',
                'dir' => '\\',
                'base' => '',
                'ext' => '',
                'name' => ''
            ],
            '\\\\' => [
                'root' => '\\',
                'dir' => '\\',
                'base' => '',
                'ext' => '',
                'name' => ''
            ],
            'c:\\foo\\\\\\' => [
                'root' => 'c:\\',
                'dir' => 'c:\\',
                'base' => 'foo',
                'ext' => '',
                'name' => 'foo'
            ],
            'D:\\foo\\\\\\bar.baz' => [
                'root' => 'D:\\',
                'dir' => 'D:\\foo\\\\',
                'base' => 'bar.baz',
                'ext' => '.baz',
                'name' => 'bar'
            ]
        ];

        foreach ($tests as $element => $expected) {
            $actual = WindowsPath::parse($element)->toArray();

            $this->assertEquals($actual, $expected, $this->formatMessage(
                [ WindowsPath::class, 'parse' ],
                [ $element ],
                $expected,
                $actual
            ));
        }
    }

    public function testPosixTrailingRemoval()
    {
        $tests = [
            './' => [
                'root' => '',
                'dir' => '',
                'base' => '.',
                'ext' => '',
                'name' => '.'
            ],
            '//' => [
                'root' => '/',
                'dir' => '/',
                'base' => '',
                'ext' => '',
                'name' => ''
            ],
            '///' => [
                'root' => '/',
                'dir' => '/',
                'base' => '',
                'ext' => '',
                'name' => ''
            ],
            '/foo///' => [
                'root' => '/',
                'dir' => '/',
                'base' => 'foo',
                'ext' => '',
                'name' => 'foo'
            ],
            '/foo///bar.baz' => [
                'root' => '/',
                'dir' => '/foo//',
                'base' => 'bar.baz',
                'ext' => '.baz',
                'name' => 'bar'
            ]
        ];

        foreach ($tests as $element => $expected) {
            $actual = PosixPath::parse($element)->toArray();

            $this->assertEquals($actual, $expected, $this->formatMessage(
                [ PosixPath::class, 'parse' ],
                [ $element ],
                $expected,
                $actual
            ));
        }
    }

    public function testWindowsSpecialCaseFormat()
    {
        $tests = [
            [['dir' => 'some\\dir'], 'some\\dir\\'],
            [['base' => 'index.html'], 'index.html'],
            [['root' => 'C:\\'], 'C:\\'],
            [['name' => 'index', 'ext' => '.html'], 'index.html'],
            [
                ['dir' => 'some\\dir', 'name' => 'index', 'ext' => '.html'],
                'some\\dir\\index.html'
            ],
            [['root' => 'C:\\', 'name' => 'index', 'ext' => '.html'], 'C:\\index.html'],
            [[], '']
        ];

        foreach ($tests as [ $element, $expected ]) {
            $this->assertSame($expected, WindowsPath::format($element));
        }
    }

    public function testPosixSpecialCaseFormat()
    {
        $tests = [
            [['dir' => 'some/dir'], 'some/dir/'],
            [['base' => 'index.html'], 'index.html'],
            [['root' => '/'], '/'],
            [['name' => 'index', 'ext' => '.html'], 'index.html'],
            [
                ['dir' => 'some/dir', 'name' => 'index', 'ext' => '.html'],
                'some/dir/index.html'
            ],
            [['root' => '/', 'name' => 'index', 'ext' => '.html'], '/index.html'],
            [[], '']
        ];

        foreach ($tests as [ $element, $expected ]) {
            $this->assertSame($expected, PosixPath::format($element));
        }
    }

    public function testWindowsParseReformat()
    {
        $this->checkParseFormat(WindowsPath::class, [
            // [path => root]
            'C:\\path\\dir\\index.html' => 'C:\\',
            'C:\\another_path\\DIR\\1\\2\\33\\\\index' => 'C:\\',
            'another_path\\DIR with spaces\\1\\2\\33\\index' => '',
            '\\' => '\\',
            '\\foo\\C:' => '\\',
            'file' => '',
            'file:stream' => '',
            '.\\file' => '',
            'C:' => 'C:',
            'C:.' => 'C:',
            'C:..' => 'C:',
            'C:abc' => 'C:',
            'C:\\' => 'C:\\',
            'C:\\abc' => 'C:\\' ,
            '' => '',

            // unc
            '\\\\server\\share\\file_path' => '\\\\server\\share\\',
            '\\\\server two\\shared folder\\file path.zip' => '\\\\server two\\shared folder\\',
            '\\\\teela\\admin$\\system32' => '\\\\teela\\admin$\\',
            '\\\\?\\UNC\\server\\share' => '\\\\?\\UNC\\',
        ]);
    }

    public function testPosixParseFormat()
    {
        $this->checkParseFormat(PosixPath::class, [
            // [path => root]
            '/home/user/dir/file.txt' => '/',
            '/home/user/a dir/another File.zip' => '/',
            '/home/user/a dir//another&File.' => '/',
            '/home/user/a$$$dir//another File.zip' => '/',
            'user/dir/another File.zip' => '',
            'file' => '',
            '.\\file' => '',
            './file' => '',
            'C:\\foo' => '',
            '/' => '/',
            '' => '',
            '.' => '',
            '..' => '',
            '/foo' => '/',
            '/foo.' => '/',
            '/foo.bar' => '/',
            '/.' => '/',
            '/.foo' => '/',
            '/.foo.bar' => '/',
            '/foo/bar.baz' => '/',
        ]);
    }

    protected function checkParseFormat($implementation, $paths)
    {
        foreach ($paths as $element => $root) {
            $output = $implementation::parse($element);

            $this->assertIsString($output['root']);
            $this->assertIsString($output['dir']);
            $this->assertIsString($output['base']);
            $this->assertIsString($output['ext']);
            $this->assertIsString($output['name']);
            $this->assertSame($element, $implementation::format($output));
            $this->assertSame($root, $output['root']);

            if (strlen($output['root']) > 0) {
                $this->assertStringStartsWith($output['root'], $output['dir']);
            }

            $this->assertSame($output['dir'] ? $implementation::dirname($element) : '', $output['dir']);
            $this->assertSame($implementation::basename($element), $output['base']);
            $this->assertSame($implementation::extname($element), $output['ext']);
        }
    }

    public function testWindowsSpecialCaseParseFormat()
    {
        $this->assertEquals([
            'base' => 't',
            'name' => 't',
            'root' => '',
            'dir' => '',
            'ext' => ''
        ], WindowsPath::parse('t')->toArray());

        $this->assertEquals([
            'root' => '/',
            'dir' => '/foo',
            'base' => 'bar',
            'ext' => '',
            'name' => 'bar'
        ], WindowsPath::parse('/foo/bar')->toArray());
    }

    public function testWindowsFormatNull()
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPath::format(null);
    }

    public function testWindowsFormatEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPath::format('');
    }

    public function testWindowsFormatString()
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPath::format('string');
    }

    public function testWindowsFormatTrue()
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPath::format(true);
    }

    public function testWindowsFormatFalse()
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPath::format(false);
    }

    public function testWindowsFormatNumber()
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPath::format(1);
    }

    public function testPosixFormatNull()
    {
        $this->expectException(InvalidArgumentException::class);
        PosixPath::format(null);
    }

    public function testPosixFormatEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        PosixPath::format('');
    }

    public function testPosixFormatString()
    {
        $this->expectException(InvalidArgumentException::class);
        PosixPath::format('string');
    }

    public function testPosixFormatTrue()
    {
        $this->expectException(InvalidArgumentException::class);
        PosixPath::format(true);
    }

    public function testPosixFormatFalse()
    {
        $this->expectException(InvalidArgumentException::class);
        PosixPath::format(false);
    }

    public function testPosixFormatNumber()
    {
        $this->expectException(InvalidArgumentException::class);
        PosixPath::format(1);
    }
}
