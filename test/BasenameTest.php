<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\Path;
use Loilo\NodePath\PosixPath;
use Loilo\NodePath\WindowsPath;
use PHPUnit\Framework\TestCase;

class BasenameTest extends TestCase
{
    public function testBasenameDetection(): void
    {
        $this->assertSame('BasenameTest.php', Path::basename(__FILE__));
        $this->assertSame('BasenameTest', Path::basename(__FILE__, '.php'));
        $this->assertSame('', Path::basename('.js', '.js'));
        $this->assertSame('', Path::basename(''));
        $this->assertSame('basename.ext', Path::basename('/dir/basename.ext'));
        $this->assertSame('basename.ext', Path::basename('/basename.ext'));
        $this->assertSame('basename.ext', Path::basename('basename.ext'));
        $this->assertSame('basename.ext', Path::basename('basename.ext/'));
        $this->assertSame('basename.ext', Path::basename('basename.ext//'));
        $this->assertSame('bbb', Path::basename('aaa/bbb', '/bbb'));
        $this->assertSame('bbb', Path::basename('aaa/bbb', 'a/bbb'));
        $this->assertSame('bbb', Path::basename('aaa/bbb', 'bbb'));
        $this->assertSame('bbb', Path::basename('aaa/bbb//', 'bbb'));
        $this->assertSame('b', Path::basename('aaa/bbb', 'bb'));
        $this->assertSame('bb', Path::basename('aaa/bbb', 'b'));
        $this->assertSame('bbb', Path::basename('/aaa/bbb', '/bbb'));
        $this->assertSame('bbb', Path::basename('/aaa/bbb', 'a/bbb'));
        $this->assertSame('bbb', Path::basename('/aaa/bbb', 'bbb'));
        $this->assertSame('bbb', Path::basename('/aaa/bbb//', 'bbb'));
        $this->assertSame('b', Path::basename('/aaa/bbb', 'bb'));
        $this->assertSame('bb', Path::basename('/aaa/bbb', 'b'));
        $this->assertSame('bbb', Path::basename('/aaa/bbb'));
        $this->assertSame('aaa', Path::basename('/aaa/'));
        $this->assertSame('b', Path::basename('/aaa/b'));
        $this->assertSame('b', Path::basename('/a/b'));
        $this->assertSame('a', Path::basename('//a'));
        $this->assertSame('', Path::basename('a', 'a'));

        // On Windows a backslash acts as a path separator.
        $this->assertSame('basename.ext', WindowsPath::basename('\\dir\\basename.ext'));
        $this->assertSame('basename.ext', WindowsPath::basename('\\basename.ext'));
        $this->assertSame('basename.ext', WindowsPath::basename('basename.ext'));
        $this->assertSame('basename.ext', WindowsPath::basename('basename.ext\\'));
        $this->assertSame('basename.ext', WindowsPath::basename('basename.ext\\\\'));
        $this->assertSame('foo', WindowsPath::basename('foo'));
        $this->assertSame('bbb', WindowsPath::basename('aaa\\bbb', '\\bbb'));
        $this->assertSame('bbb', WindowsPath::basename('aaa\\bbb', 'a\\bbb'));
        $this->assertSame('bbb', WindowsPath::basename('aaa\\bbb', 'bbb'));
        $this->assertSame('bbb', WindowsPath::basename('aaa\\bbb\\\\\\\\', 'bbb'));
        $this->assertSame('b', WindowsPath::basename('aaa\\bbb', 'bb'));
        $this->assertSame('bb', WindowsPath::basename('aaa\\bbb', 'b'));
        $this->assertSame('', WindowsPath::basename('C:'));
        $this->assertSame('.', WindowsPath::basename('C:.'));
        $this->assertSame('', WindowsPath::basename('C:\\'));
        $this->assertSame('base.ext', WindowsPath::basename('C:\\dir\\base.ext'));
        $this->assertSame('basename.ext', WindowsPath::basename('C:\\basename.ext'));
        $this->assertSame('basename.ext', WindowsPath::basename('C:basename.ext'));
        $this->assertSame('basename.ext', WindowsPath::basename('C:basename.ext\\'));
        $this->assertSame('basename.ext', WindowsPath::basename('C:basename.ext\\\\'));
        $this->assertSame('foo', WindowsPath::basename('C:foo'));
        $this->assertSame('file:stream', WindowsPath::basename('file:stream'));
        $this->assertSame('', WindowsPath::basename('a', 'a'));

        // On unix a backslash is just treated as any other character.
        $this->assertSame('\\dir\\basename.ext', PosixPath::basename('\\dir\\basename.ext'));
        $this->assertSame('\\basename.ext', PosixPath::basename('\\basename.ext'));
        $this->assertSame('basename.ext', PosixPath::basename('basename.ext'));
        $this->assertSame('basename.ext\\', PosixPath::basename('basename.ext\\'));
        $this->assertSame('basename.ext\\\\', PosixPath::basename('basename.ext\\\\'));
        $this->assertSame('foo', PosixPath::basename('foo'));

        // POSIX filenames may include control characters
        // c.f. http://www.dwheeler.com/essays/fixing-unix-linux-filenames.html
        $controlCharFilename = 'Icon' . chr(13);
        $this->assertSame($controlCharFilename, PosixPath::basename("/a/b/$controlCharFilename"));
    }
}
