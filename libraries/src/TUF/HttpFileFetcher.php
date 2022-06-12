<?php

namespace Joomla\CMS\TUF;

use GuzzleHttp\Promise\FulfilledPromise;
use Joomla\Http\Http;
use Joomla\Http\HttpFactory;
use Joomla\Http\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Tuf\Client\RepoFileFetcherInterface;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\RepoFileNotFound;

/**
 * Defines a file fetcher that uses joomla/http to read a file over HTTPS.
 */
class HttpFileFetcher implements RepoFileFetcherInterface
{
	/**
	 * The HTTP client.
	 *
	 * @var \Joomla\Http\Http
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
	 * JHttpFileFetcher constructor.
	 * @param \Joomla\Http\Http $client
	 *   The HTTP client.
	 * @param string $metadataPrefix
	 *   The path prefix for metadata.
	 * @param string $targetsPrefix
	 *   The path prefix for targets.
	 */
	public function __construct(Http $client, string $metadataPrefix, string $targetsPrefix)
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
	public static function createFromUri(
		string $baseUri,
		string $metadataPrefix = '/metadata/',
		string $targetsPrefix = '/targets/'
	): self {
		$httpFactory = new HttpFactory();
		$client = $httpFactory->getHttp([], 'curl');

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
	public function fetchTarget(
		string $fileName,
		int $maxBytes,
		array $options = [],
		string $url = null
	): PromiseInterface {
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
	 * @return \Psr\Http\Message\StreamInterface
	 *   A promise representing the eventual result of the operation.
	 */
	protected function fetchFile(string $url, int $maxBytes, array $headers = []): PromiseInterface
	{
		// Create a progress callback to abort the download if it exceeds
		// $maxBytes. This will only work with cURL, so we also verify the
		// download size when request is finished.
		$progress = function (int $expectedBytes, int $downloadedBytes) use ($url, $maxBytes) {
			if ($expectedBytes > $maxBytes || $downloadedBytes > $maxBytes) {
				throw new DownloadSizeException("$url exceeded $maxBytes bytes");
			}
		};

		/** @var Response $response */
		$response = $this->client->get($url, $headers);
		$response->getBody()->rewind();

		if ($response->getStatusCode() === 404) {
			throw new RepoFileNotFound();
		}

		if ($response->getStatusCode() !== 200) {
			throw new \RuntimeException(
				"Invalid TUF repo response: " . $response->getBody()->getContents(),
				$response->getStatusCode()
			);
		}

		return new FulfilledPromise($response->getBody()->getContents());
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
