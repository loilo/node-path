<?php namespace Loilo\NodePath;

// phpcs:ignoreFile -- multiple class definitions are on purpose

if (DIRECTORY_SEPARATOR === '\\') {
    class Path extends WindowsPath
    {
    }
} else {
    class Path extends PosixPath
    {
    }
}
