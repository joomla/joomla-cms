<?php

namespace Tuf\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\RepoFileNotFound;

/**
 * Defines a file fetcher that uses Guzzle to read a file over HTTPS.
 */
class GuzzleFileFetcher implements RepoFileFetcherInterface
{
    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * The path prefix for metadata.
     *
     * @var string|null
     */
    private $metadataPrefix;

    /**
     * The path prefix for targets.
     *
     * @var string|null
     */
    private $targetsPrefix;

    /**
     * GuzzleFileFetcher constructor.
     *
     * @param \GuzzleHttp\ClientInterface $client
     *   The HTTP client.
     * @param string $metadataPrefix
     *   The path prefix for metadata.
     * @param string $targetsPrefix
     *   The path prefix for targets.
     */
    public function __construct(ClientInterface $client, string $metadataPrefix, string $targetsPrefix)
    {
        $this->client = $client;
        $this->metadataPrefix = $metadataPrefix;
        $this->targetsPrefix = $targetsPrefix;
    }

    /**
     * Creates an instance of this class with a specific base URI.
     *
     * @param string $baseUri
     *   The base URI from which to fetch files.
     * @param string $metadataPrefix
     *   (optional) The path prefix for metadata. Defaults to '/metadata/'.
     * @param string $targetsPrefix
     *   (optional) The path prefix for targets. Defaults to '/targets/'.
     *
     * @return static
     *   A new instance of this class.
     */
    public static function createFromUri(string $baseUri, string $metadataPrefix = '/metadata/', string $targetsPrefix = '/targets/'): self
    {
        $client = new Client(['base_uri' => $baseUri]);
        return new static($client, $metadataPrefix, $targetsPrefix);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata(string $fileName, int $maxBytes): PromiseInterface
    {
        return $this->fetchFile($this->metadataPrefix . $fileName, $maxBytes);
    }

    /**
     * {@inheritDoc}
     *
     * @param array $options
     *   (optional) Additional request options to pass to the Guzzle client.
     *   See \GuzzleHttp\RequestOptions.
     * @param string $url
     *   (optional) An arbitrary URL from which the target should be downloaded.
     *   If passed, takes precedence over $fileName.
     */
    public function fetchTarget(string $fileName, int $maxBytes, array $options = [], string $url = null): PromiseInterface
    {
        $location = $url ?: $this->targetsPrefix . $fileName;
        return $this->fetchFile($location, $maxBytes, $options);
    }

    /**
     * Fetches a file from a URL.
     *
     * @param string $url
     *   The URL of the file to fetch.
     * @param integer $maxBytes
     *   The maximum number of bytes to download.
     * @param array $options
     *   (optional) Additional request options to pass to the Guzzle client.
     *   See \GuzzleHttp\RequestOptions.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     *   A promise representing the eventual result of the operation.
     */
    protected function fetchFile(string $url, int $maxBytes, array $options = []): PromiseInterface
    {
        // Create a progress callback to abort the download if it exceeds
        // $maxBytes. This will only work with cURL, so we also verify the
        // download size when request is finished.
        $progress = function (int $expectedBytes, int $downloadedBytes) use ($url, $maxBytes) {
            if ($expectedBytes > $maxBytes || $downloadedBytes > $maxBytes) {
                throw new DownloadSizeException("$url exceeded $maxBytes bytes");
            }
        };
        $options += [RequestOptions::PROGRESS => $progress];

        return $this->client->requestAsync('GET', $url, $options)
            ->then(
                function (ResponseInterface $response) {
                    return new ResponseStream($response);
                },
                $this->onRejected($url)
            );
    }

    /**
     * Creates a callback function for when the promise is rejected.
     *
     * @param string $fileName
     *   The file name being fetched from the remote repo.
     *
     * @return \Closure
     *   The callback function.
     */
    private function onRejected(string $fileName): \Closure
    {
        return function (\Throwable $e) use ($fileName) {
            if ($e instanceof ClientException) {
                if ($e->getCode() === 404) {
                    throw new RepoFileNotFound("$fileName not found", 0, $e);
                } else {
                    // Re-throwing the original exception will blow away the
                    // backtrace, so wrap the exception in a more generic one to aid
                    // in debugging.
                    throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
                }
            }
            throw $e;
        };
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadataIfExists(string $fileName, int $maxBytes): ?string
    {
        try {
            return $this->fetchMetadata($fileName, $maxBytes)->wait();
        } catch (RepoFileNotFound $exception) {
            return null;
        }
    }
}
