<?php

namespace Srmklive\Dropbox\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Collection;
use Srmklive\Dropbox\Exceptions\BadRequest;

class DropboxClient
{
    const THUMBNAIL_FORMAT_JPEG = 'jpeg';
    const THUMBNAIL_FORMAT_PNG = 'png';

    const THUMBNAIL_SIZE_XS = 'w32h32';
    const THUMBNAIL_SIZE_S = 'w64h64';
    const THUMBNAIL_SIZE_M = 'w128h128';
    const THUMBNAIL_SIZE_L = 'w640h480';
    const THUMBNAIL_SIZE_XL = 'w1024h768';

    /** @var \GuzzleHttp\Client */
    protected $client;

    /**
     * Dropbox OAuth access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Dropbox API v2 Url.
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * Dropbox content API v2 url for uploading content.
     *
     * @var string
     */
    protected $apiContentUrl;

    /**
     * Dropbox API v2 endpoint.
     *
     * @var string
     */
    protected $apiEndpoint;

    /**
     * @var mixed
     */
    protected $content;

    /**
     * Collection containing Dropbox API request data.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $request;

    /**
     * DropboxClient constructor.
     *
     * @param string             $token
     * @param \GuzzleHttp\Client $client
     */
    public function __construct($token, HttpClient $client = null)
    {
        $this->setAccessToken($token);

        $this->setClient($client);

        $this->apiUrl = 'https://api.dropboxapi.com/2/';
        $this->apiContentUrl = 'https://content.dropboxapi.com/2/';
    }

    /**
     * Set Http Client.
     *
     * @param \GuzzleHttp\Client $client
     */
    protected function setClient(HttpClient $client = null)
    {
        if ($client instanceof HttpClient) {
            $this->client = $client;
        } else {
            $this->client = new HttpClient([
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
            ]);
        }
    }

    /**
     * Set Dropbox OAuth access token.
     *
     * @param string $token
     */
    protected function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * Copy a file or folder to a different location in the user's Dropbox.
     *
     * If the source path is a folder all its contents will be copied.
     *
     * @param string $fromPath
     * @param string $toPath
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy
     */
    public function copy($fromPath, $toPath)
    {
        $this->setupRequest([
            'from_path' => $this->normalizePath($fromPath),
            'to_path'   => $this->normalizePath($toPath),
        ]);

        $this->apiEndpoint = 'files/copy';

        return $this->doDropboxApiRequest();
    }

    /**
     * Create a folder at a given path.
     *
     * @param string $path
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-create_folder
     */
    public function createFolder($path)
    {
        $this->setupRequest([
            'path' => $this->normalizePath($path),
        ]);

        $this->apiEndpoint = 'files/create_folder';

        $response = $this->doDropboxApiRequest();
        $response['.tag'] = 'folder';

        return $response;
    }

    /**
     * Delete the file or folder at a given path.
     *
     * If the path is a folder, all its contents will be deleted too.
     * A successful response indicates that the file or folder was deleted.
     *
     * @param string $path
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-delete
     */
    public function delete($path)
    {
        $this->setupRequest([
            'path' => $this->normalizePath($path),
        ]);

        $this->apiEndpoint = 'files/delete';

        return $this->doDropboxApiRequest();
    }

    /**
     * Download a file from a user's Dropbox.
     *
     * @param string $path
     *
     * @return resource
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-download
     */
    public function download($path)
    {
        $this->setupRequest([
            'path' => $this->normalizePath($path),
        ]);

        $this->apiEndpoint = 'files/download';

        $response = $this->doDropboxApiContentRequest();

        return StreamWrapper::getResource($response->getBody());
    }

    /**
     * Returns the metadata for a file or folder.
     *
     * Note: Metadata for the root folder is unsupported.
     *
     * @param string $path
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_metadata
     */
    public function getMetaData($path)
    {
        $this->setupRequest([
            'path' => $this->normalizePath($path),
            'include_media_info' => true,
        ]);

        $this->apiEndpoint = 'files/get_metadata';

        return $this->doDropboxApiRequest();
    }

    /**
     * Get a temporary link to stream content of a file.
     *
     * This link will expire in four hours and afterwards you will get 410 Gone.
     * Content-Type of the link is determined automatically by the file's mime type.
     *
     * @param string $path
     *
     * @return string
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_temporary_link
     */
    public function getTemporaryLink($path)
    {
        $this->setupRequest([
            'path' => $this->normalizePath($path),
        ]);

        $this->apiEndpoint = 'files/get_temporary_link';

        $response = $this->doDropboxApiRequest();

        return $response['link'];
    }

    /**
     * Get a thumbnail for an image.
     *
     * This method currently supports files with the following file extensions:
     * jpg, jpeg, png, tiff, tif, gif and bmp.
     *
     * Photos that are larger than 20MB in size won't be converted to a thumbnail.
     *
     * @param string $path
     * @param string $format
     * @param string $size
     *
     * @return string
     */
    public function getThumbnail($path, $format = 'jpeg', $size = 'w64h64')
    {
        $this->setupRequest([
            'path'   => $this->normalizePath($path),
            'format' => $format,
            'size'   => $size,
        ]);

        $this->apiEndpoint = 'files/get_thumbnail';

        $response = $this->doDropboxApiContentRequest();

        return (string) $response->getBody();
    }

    /**
     * Starts returning the contents of a folder.
     *
     * If the result's ListFolderResult.has_more field is true, call
     * list_folder/continue with the returned ListFolderResult.cursor to retrieve more entries.
     *
     * Note: auth.RateLimitError may be returned if multiple list_folder or list_folder/continue calls
     * with same parameters are made simultaneously by same API app for same user. If your app implements
     * retry logic, please hold off the retry until the previous request finishes.
     *
     * @param string $path
     * @param bool   $recursive
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder
     */
    public function listFolder($path = '', $media_info = true ,$recursive = false)
    {
        $this->setupRequest([
            'path'      => $this->normalizePath($path),
            'recursive' => $recursive,
	        'include_media_info' => $media_info,
        ]);

        $this->apiEndpoint = 'files/list_folder';

        return $this->doDropboxApiRequest();
    }

    /**
     * Once a cursor has been retrieved from list_folder, use this to paginate through all files and
     * retrieve updates to the folder, following the same rules as documented for list_folder.
     *
     * @param string $cursor
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder-continue
     */
    public function listFolderContinue($cursor = '')
    {
        $this->setupRequest([
            'cursor' => $cursor,
        ]);

        $this->apiEndpoint = 'files/list_folder/continue';

        return $this->doDropboxApiRequest();
    }

    /**
     * Move a file or folder to a different location in the user's Dropbox.
     *
     * If the source path is a folder all its contents will be moved.
     *
     * @param string $fromPath
     * @param string $toPath
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-move
     */
    public function move($fromPath, $toPath)
    {
        $this->setupRequest([
            'from_path' => $this->normalizePath($fromPath),
            'to_path'   => $this->normalizePath($toPath),
        ]);

        $this->apiEndpoint = 'files/move';

        return $this->doDropboxApiRequest();
    }

    /**
     * Create a new file with the contents provided in the request.
     *
     * Do not use this to upload a file larger than 150 MB. Instead, create an upload session with upload_session/start.
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload
     *
     * @param string          $path
     * @param string|resource $contents
     * @param string|array    $mode
     *
     * @return array
     */
    public function upload($path, $contents, $mode = 'add')
    {
        $this->setupRequest([
            'path' => $this->normalizePath($path),
            'mode' => $mode,
        ]);

        $this->content = $contents;

        $this->apiEndpoint = 'files/upload';

        $response = $this->doDropboxApiContentRequest();

        $metadata = \GuzzleHttp\json_decode($response->getBody(), true);
        $metadata['.tag'] = 'file';

        return $metadata;
    }

    /**
     * Set Dropbox API request data.
     *
     * @param array $request
     */
    protected function setupRequest($request)
    {
        $this->request = new Collection($request);
    }

    /**
     * Perform Dropbox API request.
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function doDropboxApiRequest()
    {
        try {
            $response = $this->client->post("{$this->apiUrl}{$this->apiEndpoint}", [
                'json' => $this->request->toArray(),
            ]);
        } catch (HttpClientException $exception) {
            throw $this->determineException($exception);
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Perform Dropbox API request.
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function doDropboxApiContentRequest()
    {
        $headers = [];
        $headers['Dropbox-API-Arg'] = json_encode(
            $this->request->toArray()
        );

        if (!empty($this->content)) {
            $headers['Content-Type'] = 'application/octet-stream';
        }

        $body = !empty($this->content) ? $this->content : '';

        try {
            $response = $this->client->post("{$this->apiContentUrl}{$this->apiEndpoint}", [
                'headers' => $headers,
                'body'    => $body,
            ]);
        } catch (HttpClientException $exception) {
            throw $this->determineException($exception);
        }

        return $response;
    }

    /**
     * Normalize path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = (trim($path, '/') === '') ? '' : '/'.$path;

        return str_replace('//', '/', $path);
    }

    /**
     * Catch Dropbox API request exception.
     *
     * @param HttpClientException $exception
     *
     * @return \Exception
     */
    protected function determineException(HttpClientException $exception)
    {
        if (!empty($exception->getResponse()) && in_array($exception->getResponse()->getStatusCode(), [400, 409])) {
            return new BadRequest($exception->getResponse());
        }

        return $exception;
    }
}
