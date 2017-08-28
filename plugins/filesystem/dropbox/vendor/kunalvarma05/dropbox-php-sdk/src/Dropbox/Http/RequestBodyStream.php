<?php
namespace Kunnu\Dropbox\Http;

use Kunnu\Dropbox\DropboxFile;

/**
 * RequestBodyStream
 */
class RequestBodyStream implements RequestBodyInterface
{

    /**
     * File to be sent with the Request
     *
     * @var \Kunnu\Dropbox\DropboxFile
     */
    protected $file;

    /**
     * Create a new RequestBodyStream instance
     *
     * @param \Kunnu\Dropbox\DropboxFile $file
     */
    public function __construct(DropboxFile $file)
    {
        $this->file = $file;
    }

    /**
     * Get the Body of the Request
     *
     * @return string
     */
    public function getBody()
    {
        return $this->file->getContents();
    }
}
