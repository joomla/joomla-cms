<?php
namespace GuzzleHttp\Stream;

/**
 * PHP stream implementation
 */
class Stream implements MetadataStreamInterface
{
    private $stream;
    private $size;
    private $seekable;
    private $readable;
    private $writable;
    private $uri;

    /** @var array Hash of readable and writable stream types */
    private static $readWriteHash = array(
        'read' => array(
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true
		),
        'write' => array(
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
		)
	);

    /**
     * Create a new stream based on the input type
     *
     * @param resource|string|StreamInterface $resource Entity body data
     * @param int                             $size     Size of the data contained in the resource
     *
     * @return Stream
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function factory($resource = '', $size = null)
    {
        $type = gettype($resource);

        if ($type == 'string') {
            $stream = fopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }
            return new self($stream);
        }

        if ($type == 'resource') {
            return new self($resource, $size);
        }

        if ($resource instanceof StreamInterface) {
            return $resource;
        }

        if ($type == 'object' && method_exists($resource, '__toString')) {
            return self::factory((string) $resource, $size);
        }

        throw new \InvalidArgumentException('Invalid resource type: ' . $type);
    }

    /**
     * @param resource $stream Stream resource to wrap
     * @param int      $size   Size of the stream in bytes. Only pass if the
     *                         size cannot be obtained from the stream.
     *
     * @throws \InvalidArgumentException if the stream is not a stream resource
     */
    public function __construct($stream, $size = null)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        $this->size = $size;
        $this->stream = $stream;
        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = isset(self::$readWriteHash['read'][$meta['mode']]);
        $this->writable = isset(self::$readWriteHash['write'][$meta['mode']]);
        $this->uri = isset($meta['uri']) ? $meta['uri'] : null;
    }

    /**
     * Closes the stream when the destructed
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __toString()
    {
        if (!$this->stream) {
            return '';
        }

        $this->seek(0);

        return (string) stream_get_contents($this->stream);
    }

    public function getContents($maxLength = -1)
    {
        return $this->stream
            ? stream_get_contents($this->stream, $maxLength)
            : '';
    }

    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->detach();
    }

    public function detach()
    {
        $result = $this->stream;
        $this->stream = $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!$this->stream) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function isSeekable()
    {
        return $this->seekable;
    }

    public function eof()
    {
        return $this->stream && feof($this->stream);
    }

    public function tell()
    {
        return $this->stream ? ftell($this->stream) : false;
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->seekable
            ? fseek($this->stream, $offset, $whence) === 0
            : false;
    }

    public function read($length)
    {
        return $this->readable ? fread($this->stream, $length) : '';
    }

    public function write($string)
    {
        // We can't know the size after writing anything
        $this->size = null;

        return $this->writable ? fwrite($this->stream, $string) : false;
    }

    public function flush()
    {
        return $this->stream ? fflush($this->stream) : false;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @param string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *                          no key is provided. Returns a specific key
     *                          value if a key is provided and the value is
     *                          found, or null if the key is not found.
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     */
    public function getMetadata($key = null)
    {
        $meta = $this->stream ? stream_get_meta_data($this->stream) : array();

        return !$key ? $meta : (isset($meta[$key]) ? $meta[$key] : null);
    }
}
