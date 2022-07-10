<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     MIT; see libraries/vendor/web-auth/webauthn-lib/LICENSE
 */

namespace Joomla\Plugin\System\Webauthn\Hotfix;

use Assert\Assertion;
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithms;
use Cose\Key\Ec2Key;
use Cose\Key\Key;
use Cose\Key\RsaKey;
use FG\ASN1\ASNObject;
use FG\ASN1\ExplicitlyTaggedObject;
use FG\ASN1\Universal\OctetString;
use FG\ASN1\Universal\Sequence;
use Webauthn\AttestationStatement\AttestationStatement;
use Webauthn\AttestationStatement\AttestationStatementSupport;
use Webauthn\AuthenticatorData;
use Webauthn\CertificateToolbox;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\StringStream;
use Webauthn\TrustPath\CertificateTrustPath;

/**
 * We had to fork the key attestation support object from the WebAuthn server package to address an
 * issue with PHP 8.
 *
 * We are currently using an older version of the WebAuthn library (2.x) which was written before
 * PHP 8 was developed. We cannot upgrade the WebAuthn library to a newer major version because of
 * Joomla's Semantic Versioning promise.
 *
 * The AndroidKeyAttestationStatementSupport class forces an assertion on the result of the
 * openssl_pkey_get_public() function, assuming it will return a resource. However, starting with
 * PHP 8.0 this function returns an OpenSSLAsymmetricKey object and the assertion fails. As a
 * result, you cannot use Android or FIDO U2F keys with WebAuthn.
 *
 * The assertion check is in a private method, therefore we have to fork both attestation support
 * class to change the assertion. The assertion takes place through a third party library we cannot
 * (and should not!) modify.
 *
 * @since   4.2.0
 *
 * @deprecated 5.0 We will upgrade the WebAuthn library to version 3 or later and this will go away.
 */
final class AndroidKeyAttestationStatementSupport implements AttestationStatementSupport
{
    /**
     * @var   Decoder
     * @since 4.2.0
     */
    private $decoder;

    /**
     * @var   MetadataStatementRepository|null
     * @since 4.2.0
     */
    private $metadataStatementRepository;

    /**
     * @param   Decoder|null                      $decoder                      Obvious
     * @param   MetadataStatementRepository|null  $metadataStatementRepository  Obvious
     *
     * @since   4.2.0
     */
    public function __construct(
        ?Decoder $decoder = null,
        ?MetadataStatementRepository $metadataStatementRepository = null
    ) {
        if ($decoder !== null) {
            @trigger_error('The argument "$decoder" is deprecated since 2.1 and will be removed in v3.0. Set null instead', E_USER_DEPRECATED);
        }

        if ($metadataStatementRepository === null) {
            @trigger_error(
                'Setting "null" for argument "$metadataStatementRepository" is deprecated since 2.1 and will be mandatory in v3.0.',
                E_USER_DEPRECATED
            );
        }

        $this->decoder = $decoder ?? new Decoder(new TagObjectManager(), new OtherObjectManager());
        $this->metadataStatementRepository = $metadataStatementRepository;
    }

    /**
     * @return  string
     * @since   4.2.0
     */
    public function name(): string
    {
        return 'android-key';
    }

    /**
     * @param   array  $attestation Obvious
     *
     * @return  AttestationStatement
     * @throws  \Assert\AssertionFailedException
     * @since   4.2.0
     */
    public function load(array $attestation): AttestationStatement
    {
        Assertion::keyExists($attestation, 'attStmt', 'Invalid attestation object');

        foreach (['sig', 'x5c', 'alg'] as $key) {
            Assertion::keyExists($attestation['attStmt'], $key, sprintf('The attestation statement value "%s" is missing.', $key));
        }

        $certificates = $attestation['attStmt']['x5c'];

        Assertion::isArray($certificates, 'The attestation statement value "x5c" must be a list with at least one certificate.');
        Assertion::greaterThan(\count($certificates), 0, 'The attestation statement value "x5c" must be a list with at least one certificate.');
        Assertion::allString($certificates, 'The attestation statement value "x5c" must be a list with at least one certificate.');

        $certificates = CertificateToolbox::convertAllDERToPEM($certificates);

        return AttestationStatement::createBasic($attestation['fmt'], $attestation['attStmt'], new CertificateTrustPath($certificates));
    }

    /**
     * @param   string                $clientDataJSONHash    Obvious
     * @param   AttestationStatement  $attestationStatement  Obvious
     * @param   AuthenticatorData     $authenticatorData     Obvious
     *
     * @return  boolean
     * @throws  \Assert\AssertionFailedException
     * @since   4.2.0
     */
    public function isValid(
        string $clientDataJSONHash,
        AttestationStatement $attestationStatement,
        AuthenticatorData $authenticatorData
    ): bool {
        $trustPath = $attestationStatement->getTrustPath();
        Assertion::isInstanceOf($trustPath, CertificateTrustPath::class, 'Invalid trust path');

        $certificates = $trustPath->getCertificates();

        if ($this->metadataStatementRepository !== null) {
            $certificates = CertificateToolbox::checkAttestationMedata(
                $attestationStatement,
                $authenticatorData->getAttestedCredentialData()->getAaguid()->toString(),
                $certificates,
                $this->metadataStatementRepository
            );
        }

        // Decode leaf attestation certificate
        $leaf = $certificates[0];
        $this->checkCertificateAndGetPublicKey($leaf, $clientDataJSONHash, $authenticatorData);

        $signedData = $authenticatorData->getAuthData() . $clientDataJSONHash;
        $alg = $attestationStatement->get('alg');

        return openssl_verify($signedData, $attestationStatement->get('sig'), $leaf, Algorithms::getOpensslAlgorithmFor((int) $alg)) === 1;
    }

    /**
     * @param   string             $certificate        Obvious
     * @param   string             $clientDataHash     Obvious
     * @param   AuthenticatorData  $authenticatorData  Obvious
     *
     * @return  void
     * @throws  \Assert\AssertionFailedException
     * @throws  \FG\ASN1\Exception\ParserException
     * @since   4.2.0
     */
    private function checkCertificateAndGetPublicKey(
        string $certificate,
        string $clientDataHash,
        AuthenticatorData $authenticatorData
    ): void {
        $resource = openssl_pkey_get_public($certificate);

        if (version_compare(PHP_VERSION, '8.0', 'lt')) {
            Assertion::isResource($resource, 'Unable to read the certificate');
        } else {
            /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
            Assertion::isInstanceOf($resource, \OpenSSLAsymmetricKey::class, 'Unable to read the certificate');
        }

        $details = openssl_pkey_get_details($resource);
        Assertion::isArray($details, 'Unable to read the certificate');

        // Check that authData publicKey matches the public key in the attestation certificate
        $attestedCredentialData = $authenticatorData->getAttestedCredentialData();
        Assertion::notNull($attestedCredentialData, 'No attested credential data found');
        $publicKeyData = $attestedCredentialData->getCredentialPublicKey();
        Assertion::notNull($publicKeyData, 'No attested public key found');
        $publicDataStream = new StringStream($publicKeyData);
        $coseKey = $this->decoder->decode($publicDataStream)->getNormalizedData(false);
        Assertion::true($publicDataStream->isEOF(), 'Invalid public key data. Presence of extra bytes.');
        $publicDataStream->close();
        $publicKey = Key::createFromData($coseKey);

        Assertion::true(($publicKey instanceof Ec2Key) || ($publicKey instanceof RsaKey), 'Unsupported key type');
        Assertion::eq($publicKey->asPEM(), $details['key'], 'Invalid key');

        $certDetails = openssl_x509_parse($certificate);

        // Find Android KeyStore Extension with OID “1.3.6.1.4.1.11129.2.1.17” in certificate extensions
        Assertion::keyExists($certDetails, 'extensions', 'The certificate has no extension');
        Assertion::isArray($certDetails['extensions'], 'The certificate has no extension');
        Assertion::keyExists(
            $certDetails['extensions'],
            '1.3.6.1.4.1.11129.2.1.17',
            'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is missing'
        );
        $extension = $certDetails['extensions']['1.3.6.1.4.1.11129.2.1.17'];
        $extensionAsAsn1 = ASNObject::fromBinary($extension);
        Assertion::isInstanceOf($extensionAsAsn1, Sequence::class, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        $objects = $extensionAsAsn1->getChildren();

        // Check that attestationChallenge is set to the clientDataHash.
        Assertion::keyExists($objects, 4, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        Assertion::isInstanceOf($objects[4], OctetString::class, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        Assertion::eq($clientDataHash, hex2bin(($objects[4])->getContent()), 'The client data hash is not valid');

        // Check that both teeEnforced and softwareEnforced structures don’t contain allApplications(600) tag.
        Assertion::keyExists($objects, 6, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        $softwareEnforcedFlags = $objects[6];
        Assertion::isInstanceOf($softwareEnforcedFlags, Sequence::class, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        $this->checkAbsenceOfAllApplicationsTag($softwareEnforcedFlags);

        Assertion::keyExists($objects, 7, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        $teeEnforcedFlags = $objects[6];
        Assertion::isInstanceOf($teeEnforcedFlags, Sequence::class, 'The certificate extension "1.3.6.1.4.1.11129.2.1.17" is invalid');
        $this->checkAbsenceOfAllApplicationsTag($teeEnforcedFlags);
    }

    /**
     * @param   Sequence  $sequence Obvious
     *
     * @return  void
     * @throws  \Assert\AssertionFailedException
     * @since   4.2.0
     */
    private function checkAbsenceOfAllApplicationsTag(Sequence $sequence): void
    {
        foreach ($sequence->getChildren() as $tag) {
            Assertion::isInstanceOf($tag, ExplicitlyTaggedObject::class, 'Invalid tag');

            /**
             * @var ExplicitlyTaggedObject $tag It is silly that I have to do that for PHPCS to be happy.
             */
            Assertion::notEq(600, (int) $tag->getTag(), 'Forbidden tag 600 found');
        }
    }
}
