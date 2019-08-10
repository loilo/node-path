<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class ExtnameTest extends TestCase
{
    use Helpers\MessageFormatterTrait;

    /**
     * Tests to perform on both implementations
     *
     * @var array
     */
    protected $tests = [
        __FILE__ => '.php',
        '' => '',
        '/path/to/file' => '',
        '/path/to/file.ext' => '.ext',
        '/path.to/file.ext' => '.ext',
        '/path.to/file' => '',
        '/path.to/.file' => '',
        '/path.to/.file.ext' => '.ext',
        '/path/to/f.ext' => '.ext',
        '/path/to/..ext' => '.ext',
        '/path/to/..' => '',
        'file' => '',
        'file.ext' => '.ext',
        '.file' => '',
        '.file.ext' => '.ext',
        '/file' => '',
        '/file.ext' => '.ext',
        '/.file' => '',
        '/.file.ext' => '.ext',
        '.path/file.ext' => '.ext',
        'file.ext.ext' => '.ext',
        'file.' => '.',
        '.' => '',
        './' => '',
        '.file.ext' => '.ext',
        '.file' => '',
        '.file.' => '.',
        '.file..' => '.',
        '..' => '',
        '../' => '',
        '..file.ext' => '.ext',
        '..file' => '.file',
        '..file.' => '.',
        '..file..' => '.',
        '...' => '.',
        '...ext' => '.ext',
        '....' => '.',
        'file.ext/' => '.ext',
        'file.ext//' => '.ext',
        'file/' => '',
        'file//' => '',
        'file./' => '.',
        'file.//' => '.',
    ];

    public function testWindows()
    {
        foreach ($this->tests as $input => $expected) {
            $input = str_replace('/', '\\', $input);

            $actual = WindowsPath::extname($input);

            $this->assertSame($expected, $actual, $this->formatMessage(
                [ WindowsPath::class, 'extname' ],
                [ $input ],
                $expected,
                $actual
            ));
        }
    }

    public function testPosix()
    {
        foreach ($this->tests as $input => $expected) {
            $actual = PosixPath::extname($input);

            $this->assertSame($expected, $actual, $this->formatMessage(
                [ PosixPath::class, 'extname' ],
                [ $input ],
                $expected,
                $actual
            ));
        }
    }

    public function testWindowsAbsolute()
    {
        foreach ($this->tests as $input => $expected) {
            $input = 'C:' . str_replace('/', '\\', $input);
            $actual = WindowsPath::extname($input);

            $this->assertSame($expected, $actual, $this->formatMessage(
                [ WindowsPath::class, 'extname' ],
                [ $input ],
                $expected,
                $actual
            ));
        }
    }

    public function testBackslashHandling()
    {
        // On Windows, backslash is a path separator.
        $this->assertSame('', WindowsPath::extname('.\\'));
        $this->assertSame('', WindowsPath::extname('..\\'));
        $this->assertSame('.ext', WindowsPath::extname('file.ext\\'));
        $this->assertSame('.ext', WindowsPath::extname('file.ext\\\\'));
        $this->assertSame('', WindowsPath::extname('file\\'));
        $this->assertSame('', WindowsPath::extname('file\\\\'));
        $this->assertSame('.', WindowsPath::extname('file.\\'));
        $this->assertSame('.', WindowsPath::extname('file.\\\\'));

        // On *nix, backslash is a valid name component like any other character.
        $this->assertSame('', PosixPath::extname('.\\'));
        $this->assertSame('.\\', PosixPath::extname('..\\'));
        $this->assertSame('.ext\\', PosixPath::extname('file.ext\\'));
        $this->assertSame('.ext\\\\', PosixPath::extname('file.ext\\\\'));
        $this->assertSame('', PosixPath::extname('file\\'));
        $this->assertSame('', PosixPath::extname('file\\\\'));
        $this->assertSame('.\\', PosixPath::extname('file.\\'));
        $this->assertSame('.\\\\', PosixPath::extname('file.\\\\'));
    }
}
