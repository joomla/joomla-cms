<?php
namespace Kunnu\Dropbox;

use Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * DropboxFile
 */
class DropboxFile
{
    const MODE_READ = 'r';

    const MODE_WRITE = 'w';

    /**
     * Path of the file to upload
     *
     * @var string
     */
    protected $path;

    /**
     * The maximum bytes to read. Defaults to -1 (read all the remaining buffer).
     *
     * @var int
     */
    protected $maxLength = -1;

    /**
     * Seek to the specified offset before reading.
     * If this number is negative, no seeking will
     * occur and reading will start from the current.
     *
     * @var int
     */
    protected $offset = -1;

    /**
     * File Stream
     *
     * @var \GuzzleHttp\Psr7\Stream
     */
    protected $stream;

    /**
     * The type of access
     *
     * @var string
     */
    protected $mode;

    /**
     * Flag to see if we created an instance using a stream
     *
     * @var bool
     */
    private $isStream = false;
    
    /**
     * Create a new DropboxFile instance
     *
     * @param string $filePath Path of the file to upload
     * @param string $mode     The type of access
     */
    public function __construct($filePath, $mode = self::MODE_READ)
    {
        $this->path = $filePath;
        $this->mode = $mode;
    }

    /**
     * Closes the stream when destructed.
     */
    public function __destruct()
    {
        $this->close();
    }
    
    
    /**
     * Create a new DropboxFile instance using a file stream
     *
     * @param $fileName
     * @param $resource
     * @param string $mode
     * @return DropboxFile
     * @throws DropboxClientException
     */
    public static function createByStream($fileName, $resource, $mode = self::MODE_READ)
    {
        // create a new stream and set it to the dropbox file
        $stream = \GuzzleHttp\Psr7\stream_for($resource);
        if (!$stream) {
            throw new DropboxClientException('Failed to create DropboxFile instance. Unable to open the given resource.');
        }

        // Try to get the file path from the stream (we'll need this for uploading bigger files)
        $filePath = $stream->getMetadata('uri');
        if (!is_null($filePath)) {
            $fileName = $filePath;
        }

        $dropboxFile = new self($fileName, $mode);
        $dropboxFile->setStream($stream);

        return $dropboxFile;
    }

    /**
     * Create a new DropboxFile instance using a file path
     *
     * This behaves the same as the constructor but was added in order to
     * match the syntax of the static createByStream function
     *
     * @see DropboxFile::createByStream()
     *
     * @param $filePath
     * @param $mode
     * @return DropboxFile
     */
    public static function createByPath($filePath, $mode)
    {
        return new self($filePath, $mode);
    }

    /**
     * Close the file stream
     */
    public function close()
    {
        if ($this->stream) {
            $this->stream->close();
        }
    }

    /**
     * Set the offset to start reading
     * the data from the stream
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Set the Max Length till where to read
     * the data from the stream.
     *
     * @param int $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }
    
    /**
     * Manually set the stream for this DropboxFile instance
     *
     * @param $stream
     */
    public function setStream($stream)
    {
        $this->isStream = true;
        $this->stream = $stream;
    }

    /**
     * Return the contents of the file
     *
     * @return string
     */
    public function getContents()
    {
        $stream = $this->getStream();
        // If an offset is provided
        if ($this->offset !== -1) {
            // Seek to the offset
            $stream->seek($this->offset);
        }

        // If a max length is provided
        if ($this->maxLength !== -1) {
            // Read from the offset till the maxLength
            return $stream->read($this->maxLength);
        }

        return $stream->getContents();
    }

    /**
     * Get the Open File Stream
     *
     * @return \GuzzleHttp\Psr7\Stream
     */
    public function getStream()
    {
        if (!$this->stream) {
            $this->open();
        }
        return $this->stream;
    }

    /**
     * Opens the File Stream
     *
     * @throws DropboxClientException
     *
     * @return void
     */
    public function open()
    {
        // File was created from a stream so don't open it again
        if ($this->stream && $this->isStream === true) {
            return;
        }
        
        if (!$this->isRemoteFile($this->path)) {
            if (self::MODE_READ === $this->mode && !is_readable($this->path)) {
                throw new DropboxClientException('Failed to create DropboxFile instance. Unable to read resource: ' . $this->path . '.');
            }
            if (self::MODE_WRITE === $this->mode && file_exists($this->path) && !is_writable($this->path)) {
                throw new DropboxClientException('Failed to create DropboxFile instance. Unable to write resource: ' . $this->path . '.');
            }
        }

        $this->stream = \GuzzleHttp\Psr7\stream_for(fopen($this->path, $this->mode));

        if (!$this->stream) {
            throw new DropboxClientException('Failed to create DropboxFile instance. Unable to open resource: ' . $this->path . '.');
        }
    }

    /**
     * Returns true if the path to the file is remote
     *
     * @param string $pathToFile
     *
     * @return boolean
     */
    protected function isRemoteFile($pathToFile)
    {
        return preg_match('/^(https?|ftp):\/\/.*/', $pathToFile) === 1;
    }

    /**
     * Get the name of the file
     *
     * @return string
     */
    public function getFileName()
    {
        return basename($this->path);
    }

    /**
     * Get the path of the file
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->path;
    }

    /**
     * Get the mode of the file stream
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get the size of the file
     *
     * @return int
     */
    public function getSize()
    {
        return $this->getStream()->getSize();
    }

    /**
     * Get mimetype of the file
     *
     * @return string
     */
    public function getMimetype()
    {
        return \GuzzleHttp\Psr7\mimetype_from_filename($this->path) ?: 'text/plain';
    }
}
