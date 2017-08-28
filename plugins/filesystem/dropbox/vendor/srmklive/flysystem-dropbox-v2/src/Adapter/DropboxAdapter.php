<?php

namespace Srmklive\Dropbox\Adapter;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Config;
use LogicException;
use Srmklive\Dropbox\Client\DropboxClient;
use Srmklive\Dropbox\Exceptions\BadRequest;

class DropboxAdapter extends AbstractAdapter
{
    use NotSupportingVisibilityTrait;

    /** @var \Srmklive\Dropbox\Client\DropboxClient */
    protected $client;

    public function __construct(DropboxClient $client, $prefix = '')
    {
        $this->client = $client;

        $this->setPathPrefix($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, 'add');
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->upload($path, $resource, 'add');
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, 'overwrite');
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->upload($path, $resource, 'overwrite');
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        $path = $this->applyPathPrefix($path);
        $newPath = $this->applyPathPrefix($newPath);

        try {
            $this->client->move($path, $newPath);
        } catch (BadRequest $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $path = $this->applyPathPrefix($path);
        $newpath = $this->applyPathPrefix($newpath);

        try {
            $this->client->copy($path, $newpath);
        } catch (BadRequest $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);

        try {
            $this->client->delete($location);
        } catch (BadRequest $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return $this->delete($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $path = $this->applyPathPrefix($dirname);

        try {
            $object = $this->client->createFolder($path);
        } catch (BadRequest $e) {
            return false;
        }

        return $this->normalizeResponse($object);
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (!$object = $this->readStream($path)) {
            return false;
        }

        $object['contents'] = stream_get_contents($object['stream']);
        fclose($object['stream']);
        unset($object['stream']);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $stream = $this->client->download($path);
        } catch (BadRequest $e) {
            return false;
        }

        return compact('stream');
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $location = $this->applyPathPrefix($directory);

        $result = $this->client->listFolder($location, $recursive);

        if (!count($result['entries'])) {
            return [];
        }

        return array_map(function ($entry) {
            $path = $this->removePathPrefix($entry['path_display']);

            return $this->normalizeResponse($entry, $path);
        }, $result['entries']);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $object = $this->client->getMetadata($path);
        } catch (BadRequest $e) {
            return false;
        }

        return $this->normalizeResponse($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        throw new LogicException("The Dropbox API v2 does not support mimetypes. Given path: `{$path}`.");
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    public function getTemporaryLink($path)
    {
        return $this->client->getTemporaryLink($path);
    }

    public function getThumbnail($path, $format = 'jpeg', $size = 'w64h64')
    {
        return $this->client->getThumbnail($path, $format, $size);
    }

    /**
     * {@inheritdoc}
     */
    public function applyPathPrefix($path)
    {
        $path = parent::applyPathPrefix($path);

        return '/'.trim($path, '/');
    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string          $path
     * @param resource|string $contents
     * @param string          $mode
     *
     * @return array|false file metadata
     */
    protected function upload($path, $contents, $mode)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $object = $this->client->upload($path, $contents, $mode);
        } catch (BadRequest $e) {
            return false;
        }

        return $this->normalizeResponse($object);
    }

    /**
     * Parse response from Dropbox.
     *
     * @param array|\Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function normalizeResponse($response)
    {
        $normalizedPath = ltrim($this->removePathPrefix($response['path_display']), '/');

        $normalizedResponse = ['path' => $normalizedPath];

        if (isset($response['server_modified'])) {
            $normalizedResponse['timestamp'] = strtotime($response['server_modified']);
        }

        if (isset($response['size'])) {
            $normalizedResponse['size'] = $response['size'];
            $normalizedResponse['bytes'] = $response['size'];
        }

        $type = ($response['.tag'] === 'folder' ? 'dir' : 'file');
        $normalizedResponse['type'] = $type;

        return $normalizedResponse;
    }
}
