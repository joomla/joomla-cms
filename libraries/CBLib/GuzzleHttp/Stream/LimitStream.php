<?php
namespace GuzzleHttp\Stream;
use GuzzleHttp\Stream\Exception\SeekException;

/**
 * Decorator used to return only a subset of a stream
 */
class LimitStream implements StreamInterface, MetadataStreamInterface
{
	//BB use StreamDecoratorTrait;
	/** @var StreamInterface Decorated stream */
	private $stream;

	/*BB
	 * @param StreamInterface $stream Stream to decorate
	 *
	public function __construct(StreamInterface $stream)
	{
		$this->stream = $stream;
	}
	*/

	/**
	 * Magic method used to create a new stream if streams are not added in
	 * the constructor of a decorator (e.g., LazyOpenStream).
	 */
	public function __get($name)
	{
		if ($name == 'stream') {
			$this->stream = $this->createStream();
			return $this->stream;
		}

		throw new \UnexpectedValueException("$name not found on class");
	}

	public function __toString()
	{
		try {
			$this->seek(0);
			return $this->getContents();
		} catch (\Exception $e) {
			// Really, PHP? https://bugs.php.net/bug.php?id=53648
			trigger_error('StreamDecorator::__toString exception: '
				. (string) $e, E_USER_ERROR);
			return '';
		}
	}

	public function getContents($maxLength = -1)
	{
		return Utils::copyToString($this, $maxLength);
	}

	/**
	 * Allow decorators to implement custom methods
	 *
	 * @param string $method Missing method name
	 * @param array  $args   Method arguments
	 *
	 * @return mixed
	 */
	public function __call($method, array $args)
	{
		$result = call_user_func_array(array($this->stream, $method), $args);

		// Always return the wrapped object if the result is a return $this
		return $result === $this->stream ? $this : $result;
	}

	/**
	 * Calls flush() and closes the underlying stream.
	 */
	public function close()
	{
		// Allow the decorated stream to flush any buffered content on close.
		$this->flush();
		// Close the decorated stream.
		$this->stream->close();
	}

	public function getMetadata($key = null)
	{
		return $this->stream instanceof MetadataStreamInterface
			? $this->stream->getMetadata($key)
			: null;
	}

	public function detach()
	{
		return $this->stream->detach();
	}

	/*
	public function getSize()
	{
		return $this->stream->getSize();
	}

	public function eof()
	{
		return $this->stream->eof();
	}

	public function tell()
	{
		return $this->stream->tell();
	}
	*/

	public function isReadable()
	{
		return $this->stream->isReadable();
	}

	public function isWritable()
	{
		return $this->stream->isWritable();
	}

	public function isSeekable()
	{
		return $this->stream->isSeekable();
	}

	/*BB
	 * Calls flush() and seeks to the specified position in the stream.
	 *
	 * {@inheritdoc}
	 *
	public function seek($offset, $whence = SEEK_SET)
	{
		// Flush the stream before seeking to allow decorators to flush their
		// state before losing their position in the stream.
		// see: https://github.com/php/php-src/blob/8b66d64b2343bc4fd8aeabb690024edb850a0155/main/streams/streams.c#L1312
		$this->flush();

		return $this->stream->seek($offset, $whence);
	}

	public function read($length)
	{
		return $this->stream->read($length);
	}
	*/

	public function write($string)
	{
		return $this->stream->write($string);
	}

	public function flush()
	{
		return $this->stream->flush();
	}

	/**
	 * Implement in subclasses to dynamically create streams when requested.
	 *
	 * @return StreamInterface
	 * @throws \BadMethodCallException
	 */
	protected function createStream()
	{
		throw new \BadMethodCallException('createStream() not implemented in '
			. get_class($this));
	}

	//BB end StreamDecoratorTrait

    /** @var int Offset to start reading from */
    private $offset;

    /** @var int Limit the number of bytes that can be read */
    private $limit;

    /**
     * @param StreamInterface $stream Stream to wrap
     * @param int             $limit  Total number of bytes to allow to be read
     *                                from the stream. Pass -1 for no limit.
     * @param int|null        $offset Position to seek to before reading (only
     *                                works on seekable streams).
     */
    public function __construct(
        StreamInterface $stream,
        $limit = -1,
        $offset = 0
    ) {
        $this->stream = $stream;
        $this->setLimit($limit);
        $this->setOffset($offset);
    }

    public function eof()
    {
        if ($this->limit == -1) {
            return $this->stream->eof();
        }

        $tell = $this->stream->tell();

        return $tell === false ||
            ($tell >= $this->offset + $this->limit) ||
            $this->stream->eof();
    }

    /**
     * Returns the size of the limited subset of data
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (null === ($length = $this->stream->getSize())) {
            return null;
        } elseif ($this->limit == -1) {
            return $length - $this->offset;
        } else {
            return min($this->limit, $length - $this->offset);
        }
    }

    /**
     * Allow for a bounded seek on the read limited stream
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($whence != SEEK_SET) {
            return false;
        }

        if ($offset < 0) {
            $offset = $this->offset;
        } else {
            $offset += $this->offset;
        }

        if ($this->limit !== -1 && $offset > ($this->offset + $this->limit)) {
            $offset = $this->offset + $this->limit;
        }

        return $this->stream->seek($offset);
    }

    /**
     * Give a relative tell()
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->stream->tell() - $this->offset;
    }

    /**
     * Set the offset to start limiting from
     *
     * @param int $offset Offset to seek to and begin byte limiting from
     *
     * @return self
     * @throws SeekException
     */
    public function setOffset($offset)
    {
        $current = $this->stream->tell();

        if ($current !== $offset) {
            // If the stream cannot seek to the offset position, then read to it
            if (!$this->stream->seek($offset)) {
                if ($current > $offset) {
                    throw new SeekException($this, $offset);
                } else {
                    $this->stream->read($offset - $current);
                }
            }
        }

        $this->offset = $offset;

        return $this;
    }

    /**
     * Set the limit of bytes that the decorator allows to be read from the
     * stream.
     *
     * @param int $limit Number of bytes to allow to be read from the stream.
     *                   Use -1 for no limit.
     * @return self
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function read($length)
    {
        if ($this->limit == -1) {
            return $this->stream->read($length);
        }

        // Check if the current position is less than the total allowed
        // bytes + original offset
        $remaining = ($this->offset + $this->limit) - $this->stream->tell();
        if ($remaining > 0) {
            // Only return the amount of requested data, ensuring that the byte
            // limit is not exceeded
            return $this->stream->read(min($remaining, $length));
        } else {
            return false;
        }
    }
}
