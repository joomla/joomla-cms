<?php

namespace Tuf\Metadata\Verifier;

use Tuf\Client\SignatureVerifier;
use Tuf\Exception\Attack\RollbackAttackException;
use Tuf\Metadata\MetadataBase;
use Tuf\Metadata\TimestampMetadata;

/**
 * Verifier for snapshot metadata.
 */
class SnapshotVerifier extends FileInfoVerifier
{
    use TrustedAuthorityTrait;

    /**
     * SnapshotVerifier constructor.
     *
     * @param \Tuf\Client\SignatureVerifier $signatureVerifier
     *   The signature verifier.
     * @param \DateTimeImmutable $metadataExpiration
     *   The time beyond which untrusted metadata is considered expired.
     * @param \Tuf\Metadata\MetadataBase|null $trustedMetadata
     *   The trusted metadata, if any.
     * @param \Tuf\Metadata\TimestampMetadata|null $timestampMetadata
     *   The trusted timestamp metadata, if there is any.
     */
    public function __construct(SignatureVerifier $signatureVerifier, \DateTimeImmutable $metadataExpiration, MetadataBase $trustedMetadata = null, TimestampMetadata $timestampMetadata = null)
    {
        parent::__construct($signatureVerifier, $metadataExpiration, $trustedMetadata);
        $this->setTrustedAuthority($timestampMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(MetadataBase $untrustedMetadata): void
    {
        // § 5.5.2
        $this->checkAgainstHashesFromTrustedAuthority($untrustedMetadata);

        // § 5.5.3
        $this->signatureVerifier->checkSignatures($untrustedMetadata);

        // § 5.5.4
        $this->checkAgainstVersionFromTrustedAuthority($untrustedMetadata);

        // If the timestamp or snapshot keys were rotating then the snapshot file
        // will not exist.
        if ($this->trustedMetadata) {
            // § 5.5.5
            $this->checkRollbackAttack($untrustedMetadata);
        }

        // § 5.5.6
        static::checkFreezeAttack($untrustedMetadata, $this->metadataExpiration);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRollbackAttack(MetadataBase $untrustedMetadata): void
    {
        // TUF-SPEC-v1.0.16 Section 5.4.4
        /** @var TimestampMetadata $untrustedMetadata */
        $this->checkFileInfoVersions($untrustedMetadata);
        $localMetaFileInfos = $this->trustedMetadata->getSigned()['meta'];
        foreach ($localMetaFileInfos as $fileName => $localFileInfo) {
            /** @var \Tuf\Metadata\SnapshotMetadata|\Tuf\Metadata\TimestampMetadata $untrustedMetadata */
            if (!$untrustedMetadata->getFileMetaInfo($fileName, true)) {
                // § 5.5.5
                // Any targets metadata filename that was listed in the trusted snapshot metadata file, if any, MUST
                // continue to be listed in the new snapshot metadata file.
                throw new RollbackAttackException("Remote snapshot metadata file references '$fileName' but this is not present in the remote file");
            }
        }
    }
}
