<?php

namespace Tuf\Metadata\Verifier;

use Tuf\Exception\MetadataException;
use Tuf\Metadata\FileInfoMetadataBase;
use Tuf\Metadata\MetadataBase;

/**
 * Helper methods for verifiers where another trusted metadata file is considered authoritative.
 */
trait TrustedAuthorityTrait
{
    /**
     * Trusted metadata which has information about the untrusted metadata.
     *
     * @var \Tuf\Metadata\FileInfoMetadataBase
     */
    protected $authority;

    /**
     * Sets the trusted metadata which has information about the untrusted metadata.
     *
     * @param FileInfoMetadataBase $authority
     *   The trusted (authoritative) metadata.
     */
    protected function setTrustedAuthority(FileInfoMetadataBase $authority): void
    {
        $authority->ensureIsTrusted();
        $this->authority = $authority;
    }

    /**
     * Verifies the hashes of untrusted metadata against hashes in the trusted metadata.
     *
     * @param \Tuf\Metadata\MetadataBase $untrustedMetadata
     *   The untrusted metadata.
     *
     * @throws \Tuf\Exception\MetadataException
     *   Thrown if the new metadata object cannot be verified.
     *
     * @return void
     */
    protected function checkAgainstHashesFromTrustedAuthority(MetadataBase $untrustedMetadata): void
    {
        $role = $untrustedMetadata->getRole();
        $fileInfo = $this->authority->getFileMetaInfo($role . '.json');
        if (isset($fileInfo['hashes'])) {
            foreach ($fileInfo['hashes'] as $algo => $hash) {
                if ($hash !== hash($algo, $untrustedMetadata->getSource())) {
                    /** @var \Tuf\Metadata\MetadataBase $authorityMetadata */
                    throw new MetadataException("The '{$role}' contents does not match hash '$algo' specified in the '{$this->authority->getType()}' metadata.");
                }
            }
        }
    }

    /**
     * Verifies the version of untrusted metadata against the version in trusted metadata.
     *
     * @param \Tuf\Metadata\MetadataBase $untrustedMetadata
     *   The untrusted metadata.
     *
     * @throws \Tuf\Exception\MetadataException
     *   Thrown if the new metadata object cannot be verified.
     *
     * @return void
     */
    protected function checkAgainstVersionFromTrustedAuthority(MetadataBase $untrustedMetadata): void
    {
        $role = $untrustedMetadata->getRole();
        $fileInfo = $this->authority->getFileMetaInfo($role . '.json');
        $expectedVersion = $fileInfo['version'];
        if ($expectedVersion !== $untrustedMetadata->getVersion()) {
            throw new MetadataException("Expected {$role} version {$expectedVersion} does not match actual version {$untrustedMetadata->getVersion()}.");
        }
    }
}
