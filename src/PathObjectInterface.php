<?php namespace Loilo\NodePath;

use ArrayAccess;

/**
 * Represents a path, dissected into its relevant parts
 */
interface PathObjectInterface extends ArrayAccess
{
    /**
     * Create a new instance from a path details array (opposite of `toArray()`)
     *
     * @param array $array
     * @return PathObjectInterface
     */
    public static function fromArray(array $array): PathObjectInterface;

    /**
     * Convert the path object to an associative array
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Returns the root of the path
     *
     * @return string
     */
    public function getRoot(): string;

    /**
     * Sets the root of the path
     *
     * @param string $root The root to set
     * @return void
     */
    public function setRoot(string $root): void;

    /**
     * Returns the directory name of the path
     *
     * @return string
     */
    public function getDir(): string;

    /**
     * Sets the directory name of the path
     *
     * @param string $dir The directory name to set
     * @return void
     */
    public function setDir(string $dir): void;

    /**
     * Returns the basename of the path
     *
     * @return string
     */
    public function getBase(): string;

    /**
     * Sets the basename of the path
     *
     * @param string $base The basename to set
     * @return void
     */
    public function setBase(string $base): void;

    /**
     * Returns the file extension (including the leading period) of the path
     *
     * @return string
     */
    public function getExt(): string;

    /**
     * Sets the file extension (including the leading period) of the path
     *
     * @param string $ext The file extension to set
     * @return void
     */
    public function setExt(string $ext): void;

    /**
     * Returns the name (without file extension) of the path
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Sets the name (without file extension) of the path
     *
     * @param string $name The name to set
     * @return void
     */
    public function setName(string $name): void;
}
