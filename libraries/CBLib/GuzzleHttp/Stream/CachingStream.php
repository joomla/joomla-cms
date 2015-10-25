<?php
namespace GuzzleHttp\Stream;
use GuzzleHttp\Stream\Exception\SeekException;

/**
 * Stream decorator that can cache previously read bytes from a sequentially
 * read stream.
 */
class CachingStream implements StreamInterface, MetadataStreamInterface
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

	/*BB
	 * Calls flush() and closes the underlying stream.
	 *
	public function close()
	{
		// Allow the decorated stream to flush any buffered content on close.
		$this->flush();
		// Close the decorated stream.
		$this->stream->close();
	}
	*/

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

	/*BB
	public function getSize()
	{
		return $this->stream->getSize();
	}

	public function eof()
	{
		return $this->stream->eof();
	}
	*/

	public function tell()
	{
		return $this->stream->tell();
	}

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

	public function write($string)
	{
		return $this->stream->write($string);
	}
	*/

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

    /** @var StreamInterface Stream being wrapped */
    private $remoteStream;

    /** @var int Number of bytes to skip reading due to a write on the buffer */
    private $skipReadBytes = 0;

    /**
     * We will treat the buffer object as the body of the stream
     *
     * @param StreamInterface $stream Stream to cache
     * @param StreamInterface $target Optionally specify where data is cached
     */
    public function __construct(
        StreamInterface $stream,
        StreamInterface $target = null
    ) {
        $this->remoteStream = $stream;
        $this->stream = $target ?: new Stream(fopen('php://temp', 'r+'));
    }

    public function getSize()
    {
        return max($this->stream->getSize(), $this->remoteStream->getSize());
    }

    /**
     * {@inheritdoc}
     * @throws SeekException When seeking with SEEK_END or when seeking
     *     past the total size of the buffer stream
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($whence == SEEK_SET) {
            $byte = $offset;
        } elseif ($whence == SEEK_CUR) {
            $byte = $offset + $this->tell();
        } else {
            return false;
        }

        // You cannot skip ahead past where you've read from the remote stream
        if ($byte > $this->stream->getSize()) {
            throw new SeekException(
                $this,
                $byte,
                sprintf('Cannot seek to byte %d when the buffered stream only'
                    . ' contains %d bytes', $byte, $this->stream->getSize())
            );
        }

        return $this->stream->seek($byte);
    }

    public function read($length)
    {
        // Perform a regular read on any previously read data from the buffer
        $data = $this->stream->read($length);
        $remaining = $length - strlen($data);

        // More data was requested so read from the remote stream
        if ($remaining) {
            // If data was written to the buffer in a position that would have
            // been filled from the remote stream, then we must skip bytes on
            // the remote stream to emulate overwriting bytes from that
            // position. This mimics the behavior of other PHP stream wrappers.
            $remoteData = $this->remoteStream->read(
                $remaining + $this->skipReadBytes
            );

            if ($this->skipReadBytes) {
                $len = strlen($remoteData);
                $remoteData = substr($remoteData, $this->skipReadBytes);
                $this->skipReadBytes = max(0, $this->skipReadBytes - $len);
            }

            $data .= $remoteData;
            $this->stream->write($remoteData);
        }

        return $data;
    }

    public function write($string)
    {
        // When appending to the end of the currently read stream, you'll want
        // to skip bytes from being read from the remote stream to emulate
        // other stream wrappers. Basically replacing bytes of data of a fixed
        // length.
        $overflow = (strlen($string) + $this->tell()) - $this->remoteStream->tell();
        if ($overflow > 0) {
            $this->skipReadBytes += $overflow;
        }

        return $this->stream->write($string);
    }

    public function eof()
    {
        return $this->stream->eof() && $this->remoteStream->eof();
    }

    /**
     * Close both the remote stream and buffer stream
     */
    public function close()
    {
        $this->remoteStream->close() && $this->stream->close();
    }
}
