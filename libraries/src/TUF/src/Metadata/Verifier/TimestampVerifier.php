<?php

namespace Tuf\Metadata\Verifier;

use Tuf\Metadata\MetadataBase;

/**
 * Verifier for timestamp metadata.
 */
class TimestampVerifier extends FileInfoVerifier
{
    /**
     * {@inheritdoc}
     */
    public function verify(MetadataBase $untrustedMetadata): void
    {
        // § 5.4.2
        $this->signatureVerifier->checkSignatures($untrustedMetadata);
        // If the timestamp or snapshot keys were rotating then the timestamp file
        // will not exist.
        if ($this->trustedMetadata) {
            // § 5.4.3
            $this->checkRollbackAttack($untrustedMetadata);
        }
        // § 5.4.4
        static::checkFreezeAttack($untrustedMetadata, $this->metadataExpiration);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRollbackAttack(MetadataBase $untrustedMetadata): void
    {
        // § 5.3.2.1
        parent::checkRollbackAttack($untrustedMetadata);
        // § 5.3.2.2
        /** @var \Tuf\Metadata\SnapshotMetadata $untrustedMetadata */
        $this->checkFileInfoVersions($untrustedMetadata);
    }
}
