<?php namespace Loilo\NodePath;

use InvalidArgumentException;

/**
 * {@inheritdoc}
 */
class PathObject implements PathObjectInterface
{
    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $array): PathObjectInterface
    {
        $instance = new PathObject();
        $instance->setBase((string) ($array['base'] ?? ''));
        $instance->setDir((string) ($array['dir'] ?? ''));
        $instance->setExt((string) ($array['ext'] ?? ''));
        $instance->setName((string) ($array['name'] ?? ''));
        $instance->setRoot((string) ($array['root'] ?? ''));
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'base' => $this->base,
            'dir' => $this->dir,
            'ext' => $this->ext,
            'name' => $this->name,
            'root' => $this->root,
        ];
    }

    /**
     * @var string
     */
    protected $root = '';

    /**
     * @var string
     */
    protected $dir = '';

    /**
     * @var string
     */
    protected $base = '';

    /**
     * @var string
     */
    protected $ext = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * {@inheritdoc}
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoot(string $root): void
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * {@inheritdoc}
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * {@inheritdoc}
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * {@inheritdoc}
     */
    public function setBase(string $base): void
    {
        $this->base = $base;
    }

    /**
     * {@inheritdoc}
     */
    public function getExt(): string
    {
        return $this->ext;
    }

    /**
     * {@inheritdoc}
     */
    public function setExt(string $ext): void
    {
        $this->ext = $ext;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return in_array($offset, [ 'base', 'dir', 'ext', 'name', 'root' ]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            trigger_error('Undefined index: ' . $offset);
        }

        return $this->{$offset};
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetExists($offset)) {
            throw new InvalidArgumentException('Invalid property to set: ' . $offset);
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value of %s[%s] must a string',
                static::class,
                json_encode($offset)
            ));
        }

        $this->{$offset} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = '';
        }
    }
}
