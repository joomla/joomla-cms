<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

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
 * @since  __DEPLOY_VERSION__
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
	 * The base URI for requests
	 *
	 * @var string|null
	 */
	private $baseUri;

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
	 *
	 * @param   \Joomla\Http\Http  $client          The HTTP client.
	 * @param   string             $metadataPrefix  The path prefix for metadata.
	 * @param   string             $targetsPrefix   The path prefix for targets.
	 */
	public function __construct(Http $client, string $metadataPrefix, string $targetsPrefix, $baseUri)
	{
		$this->client = $client;
		$this->metadataPrefix = $metadataPrefix;
		$this->targetsPrefix = $targetsPrefix;
		$this->baseUri = $baseUri;
	}

	/**
	 * Creates an instance of this class with a specific base URI.
	 *
	 * @param   string  $baseUri         The base URI from which to fetch files.
	 * @param   string  $metadataPrefix  (optional) The path prefix for metadata. Defaults to '/metadata/'.
	 * @param   string  $targetsPrefix   (optional) The path prefix for targets. Defaults to '/targets/'.
	 *
	 * @return  static  A new instance of this class.
	 */
	public static function createFromUri(
		string $baseUri,
		string $metadataPrefix = '/metadata/',
		string $targetsPrefix = '/targets/'
	): self {
		$httpFactory = new HttpFactory();
		$client = $httpFactory->getHttp([], 'curl');

		return new static($client, $metadataPrefix, $targetsPrefix, $baseUri);
	}

	/**
	 * Fetches a metadata file from the remote repo.
	 *
	 * @param  string   $fileName  The name of the metadata file to fetch.
	 * @param  integer  $maxBytes  The maximum number of bytes to download.
	 *
	 * @return  \GuzzleHttp\Promise\PromiseInterface  A promise wrapping a StreamInterface instanfe
	 */
	public function fetchMetadata(string $fileName, int $maxBytes): PromiseInterface
	{
		return $this->fetchFile($this->metadataPrefix . $fileName, $maxBytes);
	}

	/**
	 * Fetches a target file from the remote repo.
	 *
	 * @param   array   $options  (optional) Additional request options to pass to the http client
	 * @param   string  $url      An arbitrary URL from which the target should be downloaded.
	 *
	 * @return  PromiseInterface
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
	 * @param   string   $url       The URL of the file to fetch.
	 * @param   integer  $maxBytes  The maximum number of bytes to download.
	 * @param   array    $options   Additional request options to pass to the http client
	 *
	 * @return  PromiseInterface    A promise representing the eventual result of the operation.
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

		$headers = (!empty($options['headers'])) ? $options['headers'] : [];

		/** @var Response $response */
		$response = $this->client->get($this->baseUri . $url, $headers);
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

		return new FulfilledPromise($response->getBody());
	}

	/**
	 * Gets a file if it exists in the remote repo.
	 *
	 * @param   string   $fileName   The file name to fetch.
	 * @param   integer  $maxBytes   The maximum number of bytes to download.
	 *
	 * @return  string|null  The contents of the file or null if it does not exist.
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
