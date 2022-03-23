<?php

namespace Tuf\Metadata\Verifier;

use Tuf\Client\SignatureVerifier;
use Tuf\Exception\FormatException;
use Tuf\Exception\Attack\FreezeAttackException;
use Tuf\Exception\Attack\RollbackAttackException;
use Tuf\Metadata\MetadataBase;

/**
 * A base class for metadata verifiers.
 */
abstract class VerifierBase
{
    /**
     * The trusted metadata, if any.
     *
     * @var \Tuf\Metadata\MetadataBase
     */
    protected $trustedMetadata;

    /**
     * The signature verifier.
     *
     * @var \Tuf\Client\SignatureVerifier
     */
    protected $signatureVerifier;

    /**
     * The time beyond which untrusted metadata will be considered expired.
     *
     * @var \DateTimeImmutable
     */
    protected $metadataExpiration;

    /**
     * VerifierBase constructor.
     *
     * @param \Tuf\Client\SignatureVerifier $signatureVerifier
     *   The signature verifier.
     * @param \DateTimeImmutable $metadataExpiration
     *   The time beyond which untrusted metadata is considered expired.
     * @param \Tuf\Metadata\MetadataBase|null $trustedMetadata
     *   The trusted metadata, if any.
     */
    public function __construct(SignatureVerifier $signatureVerifier, \DateTimeImmutable $metadataExpiration, ?MetadataBase $trustedMetadata = null)
    {
        $this->signatureVerifier = $signatureVerifier;
        $this->metadataExpiration = $metadataExpiration;
        if ($trustedMetadata) {
            $trustedMetadata->ensureIsTrusted();
        }
        $this->trustedMetadata = $trustedMetadata;
    }

    /**
     * Verify metadata according to the specification.
     *
     * @param \Tuf\Metadata\MetadataBase $untrustedMetadata
     *   The untrusted metadata to verify.
     *
     * @throws \Tuf\Exception\Attack\FreezeAttackException
     * @throws \Tuf\Exception\Attack\RollbackAttackException
     * @throws \Tuf\Exception\Attack\InvalidHashException
     * @throws \Tuf\Exception\Attack\SignatureThresholdException
     */
    abstract public function verify(MetadataBase $untrustedMetadata): void;

    /**
     * Checks for a rollback attack.
     *
     * Verifies that an incoming remote version of a metadata file is greater
     * than or equal to the last known version.
     *
     * @param \Tuf\Metadata\MetadataBase $untrustedMetadata
     *     The untrusted metadata.
     *
     * @return void
     *
     * @throws \Tuf\Exception\Attack\RollbackAttackException
     *     Thrown if a potential rollback attack is detected.
     */
    protected function checkRollbackAttack(MetadataBase $untrustedMetadata): void
    {
        $type = $this->trustedMetadata->getType();
        $remoteVersion = $untrustedMetadata->getVersion();
        $localVersion = $this->trustedMetadata->getVersion();
        if ($remoteVersion < $localVersion) {
            $message = "Remote $type metadata version \"$$remoteVersion\" " .
              "is less than previously seen $type version \"$$localVersion\"";
            throw new RollbackAttackException($message);
        }
    }

    /**
     * Checks for a freeze attack.
     *
     * Verifies that metadata has not expired, and assumes a potential freeze
     * attack if it has.
     *
     * @param \Tuf\Metadata\MetadataBase $metadata
     *     The metadata to check.
     * @param \DateTimeImmutable $expiration
     *     The metadata expiration.
     *
     * @return void
     *
     * @throws \Tuf\Exception\Attack\FreezeAttackException Thrown if a potential freeze attack is detected.
     */
    protected static function checkFreezeAttack(MetadataBase $metadata, \DateTimeImmutable $expiration): void
    {
        $metadataExpiration = static::metadataTimestampToDatetime($metadata->getExpires());
        if ($metadataExpiration < $expiration) {
            $format = "Remote %s metadata expired on %s";
            throw new FreezeAttackException(sprintf($format, $metadata->getRole(), $metadataExpiration->format('c')));
        }
    }

    /**
     * Converts a metadata timestamp string into an immutable DateTime object.
     *
     * @param string $timestamp
     *     The timestamp string in the metadata.
     *
     * @return \DateTimeImmutable
     *     An immutable DateTime object for the given timestamp.
     *
     * @throws FormatException
     *     Thrown if the timestamp string format is not valid.
     */
    protected static function metadataTimestampToDateTime(string $timestamp): \DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat("Y-m-d\TH:i:sT", $timestamp);
        if ($dateTime === false) {
            throw new FormatException($timestamp, "Could not be interpreted as a DateTime");
        }
        return $dateTime;
    }
}
