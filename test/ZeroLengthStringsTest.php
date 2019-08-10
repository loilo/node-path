<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\Path;
use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class ZeroLengthStringsTest extends TestCase
{
    public function testZeroLengthStrings(): void
    {
        $cwd = getcwd();

        // Join will internally ignore all the zero-length strings and it will return
        // '.' if the joined string is a zero-length string.
        $this->assertSame('.', PosixPath::join(''));
        $this->assertSame('.', PosixPath::join('', ''));
        $this->assertSame('.', WindowsPath::join(''));
        $this->assertSame('.', WindowsPath::join('', ''));
        $this->assertSame($cwd, Path::join($cwd));
        $this->assertSame($cwd, Path::join($cwd, ''));

        // Normalize will return '.' if the input is a zero-length string
        $this->assertSame('.', PosixPath::normalize(''));
        $this->assertSame('.', WindowsPath::normalize(''));
        $this->assertSame($cwd, Path::normalize($cwd));

        // Since '' is not a valid path in any of the common environments, return false
        $this->assertFalse(PosixPath::isAbsolute(''));
        $this->assertFalse(WindowsPath::isAbsolute(''));

        // Resolve, internally ignores all the zero-length strings and returns the
        // current working directory
        $this->assertSame($cwd, Path::resolve(''));
        $this->assertSame($cwd, Path::resolve('', ''));

        // Relative, internally calls resolve. So, '' is actually the current directory
        $this->assertSame('', Path::relative('', $cwd));
        $this->assertSame('', Path::relative($cwd, ''));
        $this->assertSame('', Path::relative($cwd, $cwd));
    }
}
