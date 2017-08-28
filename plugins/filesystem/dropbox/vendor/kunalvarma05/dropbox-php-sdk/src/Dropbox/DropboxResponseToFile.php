<?php
namespace Kunnu\Dropbox;

class DropboxResponseToFile extends DropboxResponse
{
    /**
     * @var DropboxFile
     */
    protected $file;

    /**
     * Create a new DropboxResponse instance
     *
     * @param DropboxRequest $request
     * @param DropboxFile $file
     * @param int|null    $httpStatusCode
     * @param array       $headers
     */
    public function __construct(DropboxRequest $request, DropboxFile $file, $httpStatusCode = null, array $headers = [])
    {
        parent::__construct($request, null, $httpStatusCode, $headers);
        $this->file = $file;
    }

    public function getBody()
    {
        return $this->file->getContents();
    }

    public function getFilePath()
    {
        return $this->file->getFilePath();
    }
}
