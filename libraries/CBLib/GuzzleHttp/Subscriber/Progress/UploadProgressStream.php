<?php
namespace GuzzleHttp\Subscriber\Progress;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Stream\MetadataStreamInterface;
use GuzzleHttp\Stream\StreamInterface;
use GuzzleHttp\Stream\Utils;

/**
 * Adds upload progress events to a stream.
 *
 * The supplied callable is invoked each time data is read from the stream.
 * The callable is provided the expected number of bytes to upload followed
 * by the total number of uploaded bytes.
 */
class UploadProgressStream implements StreamInterface
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

	/**
	 * Calls flush() and seeks to the specified position in the stream.
	 *
	 * {@inheritdoc}
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		// Flush the stream before seeking to allow decorators to flush their
		// state before losing their position in the stream.
		// see: https://github.com/php/php-src/blob/8b66d64b2343bc4fd8aeabb690024edb850a0155/main/streams/streams.c#L1312
		$this->flush();

		return $this->stream->seek($offset, $whence);
	}

	/*
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

    private $reachedEnd;
    private $client;
    private $request;

    /**
     * @param StreamInterface  $stream  Stream to wrap
     * @param callable         $notify  Function to invoke as data is read
     * @param ClientInterface  $client  Client sending the request
     * @param RequestInterface $request Request being sent
     */
    public function __construct(
        StreamInterface $stream,
        $notify,
        ClientInterface $client,
        RequestInterface $request
    ) {
        $this->stream = $stream;
        $this->notify = $notify;
        $this->client = $client;
        $this->request = $request;
    }

    public function read($length)
    {
        $result = $this->stream->read($length);

        if (!$result) {
            $this->reachedEnd = true;
        } elseif (!$this->reachedEnd) {
            call_user_func(
                $this->notify,
                $this->getSize(),
                $this->tell(),
                $this->client,
                $this->request
            );
        }

        return $result;
    }
}
