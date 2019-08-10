<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class IsAbsoluteTest extends TestCase
{
    public function testAbsoluteDetection(): void
    {
        $this->assertTrue(WindowsPath::isAbsolute('/'));
        $this->assertTrue(WindowsPath::isAbsolute('//'));
        $this->assertTrue(WindowsPath::isAbsolute('//server'));
        $this->assertTrue(WindowsPath::isAbsolute('//server/file'));
        $this->assertTrue(WindowsPath::isAbsolute('\\\\server\\file'));
        $this->assertTrue(WindowsPath::isAbsolute('\\\\server'));
        $this->assertTrue(WindowsPath::isAbsolute('\\\\'));
        $this->assertFalse(WindowsPath::isAbsolute('c'));
        $this->assertFalse(WindowsPath::isAbsolute('c:'));
        $this->assertTrue(WindowsPath::isAbsolute('c:\\'));
        $this->assertTrue(WindowsPath::isAbsolute('c:/'));
        $this->assertTrue(WindowsPath::isAbsolute('c://'));
        $this->assertTrue(WindowsPath::isAbsolute('C:/Users/'));
        $this->assertTrue(WindowsPath::isAbsolute('C:\\Users\\'));
        $this->assertFalse(WindowsPath::isAbsolute('C:cwd/another'));
        $this->assertFalse(WindowsPath::isAbsolute('C:cwd\\another'));
        $this->assertFalse(WindowsPath::isAbsolute('directory/directory'));
        $this->assertFalse(WindowsPath::isAbsolute('directory\\directory'));

        $this->assertTrue(PosixPath::isAbsolute('/home/foo'));
        $this->assertTrue(PosixPath::isAbsolute('/home/foo/..'));
        $this->assertFalse(PosixPath::isAbsolute('bar/'));
        $this->assertFalse(PosixPath::isAbsolute('./baz'));
    }
}
