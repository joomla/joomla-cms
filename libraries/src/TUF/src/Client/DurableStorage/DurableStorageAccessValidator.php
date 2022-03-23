<?php


namespace Tuf\Client\DurableStorage;

/**
 * Class DurableStorageAccessValidator
 *
 * Thin wrapper around ArrayAccess inserted between tuf and the backend \ArrayAccess to limit the valid inputs.
 */
class DurableStorageAccessValidator implements \ArrayAccess
{
    /**
     * The durable storage.
     *
     * @var \ArrayAccess $backend
     */
    protected $backend;

    /**
     * Constructs a new DurableStorageAccessValidator.
     *
     * @param \ArrayAccess $backend
     *     The durable storage to validate.
     */
    public function __construct(\ArrayAccess $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Verifies that a given offset is valid.
     *
     * This is meant as a security measure to reduce the likelihood of
     * undesired storage behavior. For example, a filesystem storage can't be
     * tricked into executing in a different directory.
     *
     * @param mixed $offset
     *     The ArrayAccess offset.
     *
     * @return void
     *
     * @throws \OutOfBoundsException
     *     Thrown if the offset is not a string, or if it is not a valid
     *     filename (characters other than alphanumeric characters, periods,
     *     underscores, or hyphens).
     */
    protected function throwIfInvalidOffset($offset): void
    {
        //if (! is_string($offset) || !preg_match("|^[\w._-]+$|", $offset)) {
		if (! is_string($offset)) {
            throw new \OutOfBoundsException("Array offset '$offset' is not a valid durable storage key.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->throwIfInvalidOffset($offset);
        return $this->backend->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->throwIfInvalidOffset($offset);
        return $this->backend->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->throwIfInvalidOffset($offset);
        // @todo Consider enforcing an application-configurable maximum length
        //     here. https://github.com/php-tuf/php-tuf/issues/27
        if (! is_string($value)) {
            $format = "Cannot store %s at offset $offset: only strings are allowed in durable storage.";
            throw new \UnexpectedValueException(sprintf($format, gettype($value)));
        }
        $this->backend->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->throwIfInvalidOffset($offset);
        $this->backend->offsetUnset($offset);
    }
}
