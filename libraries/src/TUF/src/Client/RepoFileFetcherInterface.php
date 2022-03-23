<?php

namespace Tuf\Client;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * Defines an interface for fetching repo files.
 */
interface RepoFileFetcherInterface
{
    /**
     * Fetches a metadata file from the remote repo.
     *
     * @param string $fileName
     *   The name of the metadata file to fetch.
     * @param integer $maxBytes
     *   The maximum number of bytes to download.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     *   A promise representing the eventual result of the operation. If
     *   successful, the promise should wrap around an instance of
     *   \Psr\Http\Message\StreamInterface, which provides a stream of the
     *   retrieved data.
     */
    public function fetchMetadata(string $fileName, int $maxBytes): PromiseInterface;

    /**
     * Fetches a target file from the remote repo.
     *
     * @param string $fileName
     *   The name of the target to fetch.
     * @param integer $maxBytes
     *   The maximum number of bytes to download.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     *   A promise representing the eventual result of the operation. If
     *   successful, the promise should wrap around an instance of
     *   \Psr\Http\Message\StreamInterface, which provides a stream of the
     *   retrieved data.
     */
    public function fetchTarget(string $fileName, int $maxBytes): PromiseInterface;

    /**
     * Gets a file if it exists in the remote repo.
     *
     * @param string $fileName
     *   The file name to fetch.
     * @param integer $maxBytes
     *   The maximum number of bytes to download.
     *
     * @return string|null
     *   The contents of the file or null if it does not exist.
     */
    public function fetchMetadataIfExists(string $fileName, int $maxBytes): ?string;
}
