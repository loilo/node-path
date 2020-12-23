<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test\Helpers;

trait DetectWindowsTrait
{
    protected static $isWindows;

    public static function setUpBeforeClass(): void
    {
        static::$isWindows = DIRECTORY_SEPARATOR === '\\';
    }
}
