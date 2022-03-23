<?php

namespace Tuf\Client;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\StreamInterface;
use Tuf\Client\DurableStorage\DurableStorageAccessValidator;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\MetadataException;
use Tuf\Exception\NotFoundException;
use Tuf\Exception\Attack\DenialOfServiceAttackException;
use Tuf\Exception\Attack\InvalidHashException;
use Tuf\Helper\Clock;
use Tuf\Metadata\Factory as MetadataFactory;
use Tuf\Metadata\RootMetadata;
use Tuf\Metadata\SnapshotMetadata;
use Tuf\Metadata\TargetsMetadata;
use Tuf\Metadata\TimestampMetadata;
use Tuf\Metadata\Verifier\UniversalVerifier;
use Tuf\Metadata\Verifier\RootVerifier;

/**
 * Class Updater
 *
 * @package Tuf\Client
 */
class Updater
{

    const MAX_ROOT_DOWNLOADS = 1024;

    /**
     * The maximum number of bytes to download if the remote file size is not
     * known.
     */
    const MAXIMUM_DOWNLOAD_BYTES = 100000;

    /**
     * The maximum number of target roles supported.
     *
     * § 5.6.7.1
     */
    const MAXIMUM_TARGET_ROLES = 100;

    /**
     * @var \array[][]
     */
    protected $mirrors;

    /**
     * The permanent storage (e.g., filesystem storage) for the client metadata.
     *
     * @var \ArrayAccess
     */
    protected $durableStorage;

    /**
     * The repo file fetcher.
     *
     * @var \Tuf\Client\RepoFileFetcherInterface
     */
    protected $repoFileFetcher;

    /**
     * Whether the repo has been refreshed or not.
     *
     * @see ::download()
     * @see ::refresh()
     *
     * @var bool
     */
    protected $isRefreshed = false;

    /**
     * @var \Tuf\Client\SignatureVerifier
     */
    protected $signatureVerifier;

    /**
     * @var \Tuf\Helper\Clock
     */
    protected $clock;

    /**
     * The time after which metadata should be considered expired.
     *
     * @var \DateTimeImmutable
     */
    private $metadataExpiration;

    /**
     * The trusted metadata factory.
     *
     * @var \Tuf\Metadata\Factory
     */
    protected $metadataFactory;

    /**
     * The verifier factory.
     *
     * @var \Tuf\Metadata\Verifier\UniversalVerifier
     */
    protected $universalVerifier;

    /**
     * Updater constructor.
     *
     * @param \Tuf\Client\RepoFileFetcherInterface $repoFileFetcher
     *     The repo fetcher.
     * @param mixed[][] $mirrors
     *     A nested array of mirrors to use for fetching signing data from the
     *     repository. Each child array contains information about the mirror:
     *     - url_prefix: (string) The URL for the mirror.
     *     - metadata_path: (string) The path within the repository for signing
     *       metadata.
     *     - targets_path: (string) The path within the repository for targets
     *       (the actual update data that has been signed).
     *     - confined_target_dirs: (array) @todo What is this for?
     *       https://github.com/php-tuf/php-tuf/issues/161
     * @param \ArrayAccess $durableStorage
     *     An implementation of \ArrayAccess that stores its contents durably,
     *     as in to disk or a database. Values written for a given repository
     *     should be exposed to future instantiations of the Updater that
     *     interact with the same repository.
     *
     *
     */
    public function __construct(RepoFileFetcherInterface $repoFileFetcher, array $mirrors, \ArrayAccess $durableStorage)
    {
        $this->repoFileFetcher = $repoFileFetcher;
        $this->mirrors = $mirrors;
        $this->durableStorage = new DurableStorageAccessValidator($durableStorage);
        $this->clock = new Clock();
        $this->metadataFactory = new MetadataFactory($this->durableStorage);
    }

    /**
     * Gets the type for the file name.
     *
     * @param string $fileName
     *   The file name.
     *
     * @return string
     *   The type.
     */
    private static function getFileNameType(string $fileName): string
    {
        $parts = explode('.', $fileName);
        array_pop($parts);
        return array_pop($parts);
    }

    /**
     * @todo Add docs. See python comments:
     *     https://github.com/theupdateframework/tuf/blob/1cf085a360aaad739e1cc62fa19a2ece270bb693/tuf/client/updater.py#L999
     *     https://github.com/php-tuf/php-tuf/issues/162
     * @todo The Python implementation has an optional flag to "unsafely update
     *     root if necessary". Do we need it?
     *     https://github.com/php-tuf/php-tuf/issues/21
     *
     * @param bool $force
     *   (optional) If false, return early if this updater has already been
     *   refreshed. Defaults to false.
     *
     * @return boolean
     *     TRUE if the data was successfully refreshed.
     *
     * @see https://github.com/php-tuf/php-tuf/issues/21
     *
     * @throws \Tuf\Exception\MetadataException
     *   Throw if an upated root metadata file is not valid.
     * @throws \Tuf\Exception\Attack\FreezeAttackException
     *   Throw if a freeze attack is detected.
     * @throws \Tuf\Exception\Attack\RollbackAttackException
     *   Throw if a rollback attack is detected.
     * @throws \Tuf\Exception\Attack\SignatureThresholdException
     *   Thrown if the signature threshold has not be reached.
     */
    public function refresh(bool $force = false): bool
    {
        if ($force) {
            $this->isRefreshed = false;
            $this->metadataExpiration = null;
        }
        if ($this->isRefreshed) {
            return true;
        }

        // § 5.1
        $this->metadataExpiration = $this->getUpdateStartTime();

        // § 5.2
        /** @var \Tuf\Metadata\RootMetadata $rootData */
        $rootData = $this->metadataFactory->load('root');

        $this->signatureVerifier = SignatureVerifier::createFromRootMetadata($rootData);
        $this->universalVerifier = new UniversalVerifier($this->metadataFactory, $this->signatureVerifier, $this->metadataExpiration);

        // § 5.3
        $this->updateRoot($rootData);

        // § 5.4
        $newTimestampData = $this->updateTimestamp();

        $snapshotInfo = $newTimestampData->getFileMetaInfo('snapshot.json');
        $snapShotVersion = $snapshotInfo['version'];

        // § 5.5
        if ($rootData->supportsConsistentSnapshots()) {
            // § 5.5.1
            $newSnapshotContents = $this->fetchFile("$snapShotVersion.snapshot.json");

            $newSnapshotData = SnapshotMetadata::createFromJson($newSnapshotContents);

            $this->universalVerifier->verify(SnapshotMetadata::TYPE, $newSnapshotData);

            // § 5.5.7
			// TODO: here change .json to _json
            $this->durableStorage['snapshot_json'] = $newSnapshotContents;
        } else {
            // @todo Add support for not using consistent snapshots in
            //    https://github.com/php-tuf/php-tuf/issues/97
            throw new \UnexpectedValueException("Currently only repos using consistent snapshots are supported.");
        }

        // § 5.6
        if ($rootData->supportsConsistentSnapshots()) {
            $this->fetchAndVerifyTargetsMetadata('targets');
        } else {
            // @todo Add support for not using consistent snapshots in
            //    https://github.com/php-tuf/php-tuf/issues/97
            throw new \UnexpectedValueException("Currently only repos using consistent snapshots are supported.");
        }
        $this->isRefreshed = true;
        return true;
    }

    /**
     * Updates the timestamp role, per section 5.3 of the TUF spec.
     */
    private function updateTimestamp(): TimestampMetadata
    {
        // § 5.4.1
        $newTimestampContents = $this->fetchFile('timestamp.json');
        $newTimestampData = TimestampMetadata::createFromJson($newTimestampContents);

        $this->universalVerifier->verify(TimestampMetadata::TYPE, $newTimestampData);

        // § 5.4.5: Persist timestamp metadata
		// TODO: here change .json to _json
        $this->durableStorage['timestamp_json'] = $newTimestampContents;

        return $newTimestampData;
    }



    /**
     * Updates the root metadata if needed.
     *
     * @param \Tuf\Metadata\RootMetadata $rootData
     *   The current root metadata.
     *
     * @return void
     *@throws \Tuf\Exception\Attack\FreezeAttackException
     *   Throw if a freeze attack is detected.
     * @throws \Tuf\Exception\Attack\RollbackAttackException
     *   Throw if a rollback attack is detected.
     * @throws \Tuf\Exception\Attack\SignatureThresholdException
     *   Thrown if an updated root file is not signed with the need signatures.
     *
     * @throws \Tuf\Exception\MetadataException
     *   Throw if an upated root metadata file is not valid.
     */
    private function updateRoot(RootMetadata &$rootData): void
    {
        // § 5.3.1 needs no action, since we currently require consistent
        // snapshots.
        $rootsDownloaded = 0;
        $originalRootData = $rootData;
        // § 5.3.2 and 5.3.3
        $nextVersion = $rootData->getVersion() + 1;
        while ($nextRootContents = $this->repoFileFetcher->fetchMetadataIfExists("$nextVersion.root.json", static::MAXIMUM_DOWNLOAD_BYTES)) {
            $rootsDownloaded++;
            if ($rootsDownloaded > static::MAX_ROOT_DOWNLOADS) {
                throw new DenialOfServiceAttackException("The maximum number root files have already been downloaded: " . static::MAX_ROOT_DOWNLOADS);
            }
            $nextRoot = RootMetadata::createFromJson($nextRootContents);
            $this->universalVerifier->verify(RootMetadata::TYPE, $nextRoot);

            // § 5.3.6 Needs no action. The expiration of the new (intermediate)
            // root metadata file does not matter yet, because we will check for
            // it in § 5.3.10.
            // § 5.3.7
            $rootData = $nextRoot;

            // § 5.3.8
			// TODO: here change .json to _json
            $this->durableStorage['root_json'] = $nextRootContents;
            // § 5.3.9: repeat from § 5.3.2.
            $nextVersion = $rootData->getVersion() + 1;
        }
        // § 5.3.10
        RootVerifier::checkFreezeAttack($rootData, $this->metadataExpiration);

        // § 5.3.11: Delete the trusted timestamp and snapshot files if either
        // file has rooted keys.
        if ($rootsDownloaded &&
           (static::hasRotatedKeys($originalRootData, $rootData, 'timestamp')
           || static::hasRotatedKeys($originalRootData, $rootData, 'snapshot'))) {
            unset($this->durableStorage['timestamp_json'], $this->durableStorage['snapshot_json']);
        }
        // § 5.3.12 needs no action because we currently require consistent
        // snapshots.
    }

    /**
     * Determines if the new root metadata has rotated keys for a role.
     *
     * @param \Tuf\Metadata\RootMetadata $previousRootData
     *   The previous root metadata.
     * @param \Tuf\Metadata\RootMetadata $newRootData
     *   The new root metadta.
     * @param string $role
     *   The role to check for rotated keys.
     *
     * @return boolean
     *   True if the keys for the role have been rotated, otherwise false.
     */
    private static function hasRotatedKeys(RootMetadata $previousRootData, RootMetadata $newRootData, string $role): bool
    {
        $previousRole = $previousRootData->getRoles()[$role] ?? null;
        $newRole = $newRootData->getRoles()[$role] ?? null;
        if ($previousRole && $newRole) {
            return !$previousRole->keysMatch($newRole);
        }
        return false;
    }

    /**
     * Synchronously fetches a file from the remote repo.
     *
     * @param string $fileName
     *   The name of the file to fetch.
     * @param integer $maxBytes
     *   (optional) The maximum number of bytes to download.
     *
     * @return string
     *   The contents of the fetched file.
     */
    private function fetchFile(string $fileName, int $maxBytes = self::MAXIMUM_DOWNLOAD_BYTES): string
    {
        return $this->repoFileFetcher->fetchMetadata($fileName, $maxBytes)
            ->then(function (StreamInterface $data) use ($fileName, $maxBytes) {
                $this->checkLength($data, $maxBytes, $fileName);
                return $data;
            })
            ->wait();
    }

    /**
     * Verifies the length of a data stream.
     *
     * @param \Psr\Http\Message\StreamInterface $data
     *   The data stream to check.
     * @param int $maxBytes
     *   The maximum acceptable length of the stream, in bytes.
     * @param string $fileName
     *   The filename associated with the stream.
     *
     * @throws \Tuf\Exception\DownloadSizeException
     *   If the stream's length exceeds $maxBytes in size.
     */
    protected function checkLength(StreamInterface $data, int $maxBytes, string $fileName): void
    {
        $error = new DownloadSizeException("$fileName exceeded $maxBytes bytes");
        $size = $data->getSize();

        if (isset($size)) {
            if ($size > $maxBytes) {
                throw $error;
            }
        } else {
            // @todo Handle non-seekable streams.
            // https://github.com/php-tuf/php-tuf/issues/169
            $data->rewind();
            $data->read($maxBytes);

            // If we reached the end of the stream, we didn't exceed the
            // maximum number of bytes.
            if ($data->eof() === false) {
                throw $error;
            }
            $data->rewind();
        }
    }

    /**
     * Verifies a stream of data against a known TUF target.
     *
     * @param string $target
     *   The path of the target file. Needs to be known to the most recent
     *   targets metadata downloaded in ::refresh().
     * @param \Psr\Http\Message\StreamInterface $data
     *   A stream pointing to the downloaded target data.
     *
     * @throws \Tuf\Exception\MetadataException
     *   If the target has no trusted hash(es).
     * @throws \Tuf\Exception\Attack\InvalidHashException
     *   If the data stream does not match the known hash(es) for the target.
     */
    protected function verify(string $target, StreamInterface $data): void
    {
        $this->refresh();

        $targetsMetadata = $this->getMetadataForTarget($target);
        if ($targetsMetadata === null) {
            throw new NotFoundException($target, 'Target');
        }
        $maxBytes = $targetsMetadata->getLength($target) ?? static::MAXIMUM_DOWNLOAD_BYTES;
        $this->checkLength($data, $maxBytes, $target);

        $hashes = $targetsMetadata->getHashes($target);
        if (count($hashes) === 0) {
            // § 5.7.2
            throw new MetadataException("No trusted hashes are available for '$target'");
        }
        foreach ($hashes as $algo => $hash) {
            // If the stream has a URI that refers to a file, use
            // hash_file() to verify it. Otherwise, read the entire stream
            // as a string and use hash() to verify it.
            $uri = $data->getMetadata('uri');
            if ($uri && file_exists($uri)) {
                $streamHash = hash_file($algo, $uri);
            } else {
                $streamHash = hash($algo, $data->getContents());
                $data->rewind();
            }

            if ($hash !== $streamHash) {
                throw new InvalidHashException($data, "Invalid $algo hash for $target");
            }
        }
    }

    /**
     * Downloads a target file, verifies it, and returns its contents.
     *
     * @param string $target
     *   The path of the target file. Needs to be known to the most recent
     *   targets metadata downloaded in ::refresh().
     * @param mixed ...$extra
     *   Additional arguments to pass to the file fetcher.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     *   A promise representing the eventual verified result of the download
     *   operation.
     */
    public function download(string $target, ...$extra): PromiseInterface
    {
        $this->refresh();

        $targetsMetadata = $this->getMetadataForTarget($target);
        if ($targetsMetadata === null) {
            return new RejectedPromise(new NotFoundException($target, 'Target'));
        }

        // If the target isn't known, immediately return a rejected promise.
        try {
            $length = $targetsMetadata->getLength($target) ?? static::MAXIMUM_DOWNLOAD_BYTES;
        } catch (NotFoundException $e) {
            return new RejectedPromise($e);
        }

        return $this->repoFileFetcher->fetchTarget($target, $length, ...$extra)
            ->then(function (StreamInterface $stream) use ($target) {
                $this->verify($target, $stream);
                return $stream;
            });
    }

    /**
     * Gets a target metadata object that contains the specified target, if any.
     *
     * @param string $target
     *   The path of the target file.
     *
     * @return \Tuf\Metadata\TargetsMetadata|null
     *   The targets metadata with information about the desired target, or null if no relevant metadata is found.
     */
    protected function getMetadataForTarget(string $target): ?TargetsMetadata
    {
        // Search the top level targets metadata.
        /** @var \Tuf\Metadata\TargetsMetadata $targetsMetadata */
        $targetsMetadata = $this->metadataFactory->load('targets');
        if ($targetsMetadata->hasTarget($target)) {
            return $targetsMetadata;
        }
        // Recursively search any delegated roles.
        return $this->searchDelegatedRolesForTarget($targetsMetadata, $target, ['targets']);
    }

    /**
     * Fetches and verifies a targets metadata file.
     *
     * The metadata file will be stored as '$role_json'.
     *
     * @param string $role
     *   The role name. This may be 'targets' or a delegated role.
     */
    private function fetchAndVerifyTargetsMetadata(string $role): void
    {
        $newSnapshotData = $this->metadataFactory->load('snapshot');
        $targetsVersion = $newSnapshotData->getFileMetaInfo($role. ".json")['version'];
        // § 5.6.1
        $newTargetsContent = $this->fetchFile("$targetsVersion.$role.json");
        $newTargetsData = TargetsMetadata::createFromJson($newTargetsContent, $role);
        $this->universalVerifier->verify(TargetsMetadata::TYPE, $newTargetsData);
        // § 5.5.6
		// TODO: here change .json to _json
        $this->durableStorage[$role . "_json"] = $newTargetsContent;
    }

    /**
     * Returns the time that the update began.
     *
     * @return \DateTimeImmutable
     *   The time that the update began.
     */
    private function getUpdateStartTime(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($this->clock->getCurrentTime());
    }

    /**
     * Searches delegated roles for metadata concerning a specific target.
     *
     * @param \Tuf\Metadata\TargetsMetadata|null $targetsMetadata
     *   The targets metadata to search.
     * @param string $target
     *   The path of the target file.
     * @param string[] $searchedRoles
     *   The roles that have already been searched. This is for internal use only and should not be passed by calling code.
     * @param bool $terminated
     *   (optional) For internal recursive calls only. This will be set to true if a terminating delegation is found in
     *   the search.
     *
     *
     * @return \Tuf\Metadata\TargetsMetadata|null
     *   The target metadata that contains the metadata for the target or null if the target is not found.
     */
    private function searchDelegatedRolesForTarget(TargetsMetadata $targetsMetadata, string $target, array $searchedRoles, bool &$terminated = false): ?TargetsMetadata
    {
        foreach ($targetsMetadata->getDelegatedKeys() as $keyId => $delegatedKey) {
            $this->signatureVerifier->addKey($keyId, $delegatedKey);
        }
        foreach ($targetsMetadata->getDelegatedRoles() as $delegatedRole) {
            $delegatedRoleName = $delegatedRole->getName();
            if (in_array($delegatedRoleName, $searchedRoles, true)) {
                // § 5.6.7.1
                // If this role has been visited before, skip it (to avoid cycles in the delegation graph).
                continue;
            }
            // § 5.6.7.1
            if (count($searchedRoles) > static::MAXIMUM_TARGET_ROLES) {
                return null;
            }

            $this->signatureVerifier->addRole($delegatedRole);
            // Targets must match the paths of all roles in the delegation chain, so if the path does not match,
            // do not evaluate this role or any roles it delegates to.
            if ($delegatedRole->matchesPath($target)) {
                $this->fetchAndVerifyTargetsMetadata($delegatedRoleName);
                /** @var \Tuf\Metadata\TargetsMetadata $delegatedTargetsMetadata */
                $delegatedTargetsMetadata = $this->metadataFactory->load($delegatedRoleName);
                if ($delegatedTargetsMetadata->hasTarget($target)) {
                    return $delegatedTargetsMetadata;
                }
                $searchedRoles[] = $delegatedRoleName;
                // § 5.6.7.2.1
                // Recursively search the list of delegations in order of appearance.
                $delegatedRolesMetadataSearchResult = $this->searchDelegatedRolesForTarget($delegatedTargetsMetadata, $target, $searchedRoles, $terminated);
                if ($terminated || $delegatedRolesMetadataSearchResult) {
                    return $delegatedRolesMetadataSearchResult;
                }

                // If $delegatedRole is terminating then we do not search any of the next delegated roles after it
                // in the delegations from $targetsMetadata.
                if ($delegatedRole->isTerminating()) {
                    $terminated = true;
                    // § 5.6.7.2.2
                    // If the role is terminating then abort searching for a target.
                    return null;
                }
            }
        }
        return null;
    }
}
