<?php

namespace Tuf\Client\DurableStorage;

/**
 * Defines a simple filesystem-based storage for fetched PHP-TUF metadata.
 *
 * Applications might want to provide an alternative implementation with
 * better performance and error handling.
 */
class FileStorage implements \ArrayAccess
{
    /**
     * @var string $basePath
     *     The path on the filesystem to this durable storage's files.
     */
    protected $basePath;

    /**
     * Constructs a new FileStorage instance.
     *
     * @param string $basePath
     *     The path on the filesystem to this durable storage's files.
     *
     * @throws \RuntimeException
     *     Thrown if the base path is not an accessible, existing directory.
     */
    public function __construct(string $basePath)
    {
        if (! is_dir($basePath)) {
            throw new \RuntimeException("Cannot initialize filesystem local state: '$basePath' is not a directory.");
        }

        $this->basePath = $basePath;
    }

    /**
     * Returns a full path for an item in the storage.
     *
     * @param mixed $offset
     *     The ArrayAccess offset for the item.
     *
     * @return string
     *     The full path for the item in the storage.
     */
    protected function pathWithBasePath($offset): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . $offset;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return file_exists($this->pathWithBasePath($offset));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return file_get_contents($this->pathWithBasePath($offset));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        file_put_contents($this->pathWithBasePath($offset), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        @unlink($this->pathWithBasePath($offset));
    }
}
