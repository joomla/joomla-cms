<?php

namespace GuzzleHttp\Post;

use GuzzleHttp\Stream\AppendStream;
use GuzzleHttp\Stream\MetadataStreamInterface;
use GuzzleHttp\Stream\StreamInterface;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\Utils;

/**
 * Stream that when read returns bytes for a streaming multipart/form-data body
 */
class MultipartBody implements StreamInterface
{
    //BB use StreamDecoratorTrait;
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

	/*BB
	public function isWritable()
	{
		return $this->stream->isWritable();
	}
	*/

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

	public function read($length)
	{
		return $this->stream->read($length);
	}

	public function write($string)
	{
		return $this->stream->write($string);
	}

	public function flush()
	{
		return $this->stream->flush();
	}

	/*BB
	 * Implement in subclasses to dynamically create streams when requested.
	 *
	 * @return StreamInterface
	 * @throws \BadMethodCallException
	 *
	protected function createStream()
	{
		throw new \BadMethodCallException('createStream() not implemented in '
			. get_class($this));
	}
	*/

	//BB end of use StreamDecoratorTrait

    private $boundary;

    /**
     * @param array  $fields   Associative array of field names to values where
     *                         each value is a string or array of strings.
     * @param array  $files    Associative array of PostFileInterface objects
     * @param string $boundary You can optionally provide a specific boundary
     * @throws \InvalidArgumentException
     */
    public function __construct(
        array $fields = array(),
        array $files = array(),
        $boundary = null
    ) {
        $this->boundary = $boundary ?: uniqid();
        $this->stream = $this->createStream($fields, $files);
    }

    /**
     * Get the boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    public function isWritable()
    {
        return false;
    }

    /**
     * Get the string needed to transfer a POST field
     */
    private function getFieldString($name, $value)
    {
        return sprintf(
            "--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r\n",
            $this->boundary,
            $name,
            $value
        );
    }

    /**
     * Get the headers needed before transferring the content of a POST file
     */
    private function getFileHeaders(PostFileInterface $file)
    {
        $headers = '';
        foreach ($file->getHeaders() as $key => $value) {
            $headers .= "{$key}: {$value}\r\n";
        }

        return "--{$this->boundary}\r\n" . trim($headers) . "\r\n\r\n";
    }

    /**
     * Create the aggregate stream that will be used to upload the POST data
     */
    private function createStream(array $fields, array $files)
    {
        $stream = new AppendStream();

        foreach ($fields as $name => $fieldValues) {
            foreach ((array) $fieldValues as $value) {
                $stream->addStream(
                    Stream::factory($this->getFieldString($name, $value))
                );
            }
        }

        foreach ($files as $file) {

            if (!$file instanceof PostFileInterface) {
                throw new \InvalidArgumentException('All POST fields must '
                    . 'implement PostFieldInterface');
            }

            $stream->addStream(
                Stream::factory($this->getFileHeaders($file))
            );
            $stream->addStream($file->getContent());
            $stream->addStream(Stream::factory("\r\n"));
        }

        // Add the trailing boundary
        $stream->addStream(Stream::factory("--{$this->boundary}--"));

        return $stream;
    }
}
