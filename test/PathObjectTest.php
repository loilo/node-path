<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test;

use Loilo\NodePath\PathObject;
use PHPUnit\Framework\TestCase;

class PathObjectTest extends TestCase
{
    use Helpers\DetectWindowsTrait;

    public function testDefaultPathObject()
    {
        $path = new PathObject();
        $this->assertSame('', $path->getRoot());
        $this->assertSame('', $path->getDir());
        $this->assertSame('', $path->getBase());
        $this->assertSame('', $path->getExt());
        $this->assertSame('', $path->getName());
    }

    public function testSetProperties()
    {
        $path = new PathObject();

        $path->setRoot('a');
        $this->assertSame('a', $path->getRoot());

        $path->setDir('b');
        $this->assertSame('b', $path->getDir());

        $path->setBase('c');
        $this->assertSame('c', $path->getBase());

        $path->setExt('d');
        $this->assertSame('d', $path->getExt());

        $path->setName('e');
        $this->assertSame('e', $path->getName());
    }

    public function testCastToArray()
    {
        $path = new PathObject();
        $path->setRoot('a');
        $path->setDir('b');
        $path->setBase('c');
        $path->setExt('d');
        $path->setName('e');

        $this->assertEquals([
            'root' => 'a',
            'dir' => 'b',
            'base' => 'c',
            'ext' => 'd',
            'name' => 'e'
        ], $path->toArray());
    }

    public function testCreateFromArray()
    {
        $path = PathObject::fromArray([
            'root' => 'a',
            'dir' => 'b',
            'base' => 'c',
            'ext' => 'd',
            'name' => 'e'
        ]);

        $this->assertSame('a', $path->getRoot());
        $this->assertSame('b', $path->getDir());
        $this->assertSame('c', $path->getBase());
        $this->assertSame('d', $path->getExt());
        $this->assertSame('e', $path->getName());
    }

    public function testPartialCreateFromArray()
    {
        $path = PathObject::fromArray([
            'root' => 'a',
            'dir' => 'b'
        ]);

        $this->assertSame('a', $path->getRoot());
        $this->assertSame('b', $path->getDir());
        $this->assertSame('', $path->getBase());
        $this->assertSame('', $path->getExt());
        $this->assertSame('', $path->getName());
    }
}
