<?php

namespace Tuf\Metadata\Verifier;

use Tuf\Client\SignatureVerifier;
use Tuf\Metadata\MetadataBase;
use Tuf\Metadata\SnapshotMetadata;

/**
 * Verifier for targets metadata.
 */
class TargetsVerifier extends VerifierBase
{
    use TrustedAuthorityTrait;

    /**
     * TargetsVerifier constructor.
     *
     * @param SignatureVerifier $signatureVerifier
     *   The signature verifier.
     * @param \DateTimeImmutable $expiration
     *   The time beyond which untrusted metadata will be considered expired.
     * @param MetadataBase|null $trustedMetadata
     *   The trusted metadata, if any.
     * @param SnapshotMetadata|null $snapshotMetadata
     *   The trusted snapshot metadata, if any.
     */
    public function __construct(SignatureVerifier $signatureVerifier, \DateTimeImmutable $expiration, MetadataBase $trustedMetadata = null, SnapshotMetadata $snapshotMetadata = null)
    {
        parent::__construct($signatureVerifier, $expiration, $trustedMetadata);
        $this->setTrustedAuthority($snapshotMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(MetadataBase $untrustedMetadata): void
    {
        // § 5.6.2
        $this->checkAgainstHashesFromTrustedAuthority($untrustedMetadata);

        // § 5.6.3
        $this->signatureVerifier->checkSignatures($untrustedMetadata);

        // § 5.6.4
        $this->checkAgainstVersionFromTrustedAuthority($untrustedMetadata);

        // § 5.6.5
        static::checkFreezeAttack($untrustedMetadata, $this->metadataExpiration);
    }
}
