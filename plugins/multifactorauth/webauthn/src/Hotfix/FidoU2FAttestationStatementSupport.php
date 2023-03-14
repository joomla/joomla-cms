<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright   (C) 2014-2019 Spomky-Labs
 * @license     This software may be modified and distributed under the terms
 *              of the MIT license.
 *              See libraries/vendor/web-auth/webauthn-lib/LICENSE
 */

namespace Joomla\Plugin\Multifactorauth\Webauthn\Hotfix;

use Assert\Assertion;
use CBOR\Decoder;
use CBOR\MapObject;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Key\Ec2Key;
use Webauthn\AttestationStatement\AttestationStatement;
use Webauthn\AttestationStatement\AttestationStatementSupport;
use Webauthn\AuthenticatorData;
use Webauthn\CertificateToolbox;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\StringStream;
use Webauthn\TrustPath\CertificateTrustPath;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * We had to fork the key attestation support object from the WebAuthn server package to address an
 * issue with PHP 8.
 *
 * We are currently using an older version of the WebAuthn library (2.x) which was written before
 * PHP 8 was developed. We cannot upgrade the WebAuthn library to a newer major version because of
 * Joomla's Semantic Versioning promise.
 *
 * The FidoU2FAttestationStatementSupport class forces an assertion on the result of the
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
final class FidoU2FAttestationStatementSupport implements AttestationStatementSupport
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
        return 'fido-u2f';
    }

    /**
     * @param   array  $attestation Obvious
     *
     * @return AttestationStatement
     * @throws \Assert\AssertionFailedException
     *
     * @since   4.2.0
     */
    public function load(array $attestation): AttestationStatement
    {
        Assertion::keyExists($attestation, 'attStmt', 'Invalid attestation object');

        foreach (['sig', 'x5c'] as $key) {
            Assertion::keyExists($attestation['attStmt'], $key, sprintf('The attestation statement value "%s" is missing.', $key));
        }

        $certificates = $attestation['attStmt']['x5c'];
        Assertion::isArray($certificates, 'The attestation statement value "x5c" must be a list with one certificate.');
        Assertion::count($certificates, 1, 'The attestation statement value "x5c" must be a list with one certificate.');
        Assertion::allString($certificates, 'The attestation statement value "x5c" must be a list with one certificate.');

        reset($certificates);
        $certificates = CertificateToolbox::convertAllDERToPEM($certificates);
        $this->checkCertificate($certificates[0]);

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
        Assertion::eq(
            $authenticatorData->getAttestedCredentialData()->getAaguid()->toString(),
            '00000000-0000-0000-0000-000000000000',
            'Invalid AAGUID for fido-u2f attestation statement. Shall be "00000000-0000-0000-0000-000000000000"'
        );

        if ($this->metadataStatementRepository !== null) {
            CertificateToolbox::checkAttestationMedata(
                $attestationStatement,
                $authenticatorData->getAttestedCredentialData()->getAaguid()->toString(),
                [],
                $this->metadataStatementRepository
            );
        }

        $trustPath = $attestationStatement->getTrustPath();
        Assertion::isInstanceOf($trustPath, CertificateTrustPath::class, 'Invalid trust path');
        $dataToVerify = "\0";
        $dataToVerify .= $authenticatorData->getRpIdHash();
        $dataToVerify .= $clientDataJSONHash;
        $dataToVerify .= $authenticatorData->getAttestedCredentialData()->getCredentialId();
        $dataToVerify .= $this->extractPublicKey($authenticatorData->getAttestedCredentialData()->getCredentialPublicKey());

        return openssl_verify($dataToVerify, $attestationStatement->get('sig'), $trustPath->getCertificates()[0], OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * @param   string|null  $publicKey Obvious
     *
     * @return  string
     * @throws  \Assert\AssertionFailedException
     * @since   4.2.0
     */
    private function extractPublicKey(?string $publicKey): string
    {
        Assertion::notNull($publicKey, 'The attested credential data does not contain a valid public key.');

        $publicKeyStream = new StringStream($publicKey);
        $coseKey = $this->decoder->decode($publicKeyStream);
        Assertion::true($publicKeyStream->isEOF(), 'Invalid public key. Presence of extra bytes.');
        $publicKeyStream->close();
        Assertion::isInstanceOf($coseKey, MapObject::class, 'The attested credential data does not contain a valid public key.');

        $coseKey = $coseKey->getNormalizedData();
        $ec2Key = new Ec2Key($coseKey + [Ec2Key::TYPE => 2, Ec2Key::DATA_CURVE => Ec2Key::CURVE_P256]);

        return "\x04" . $ec2Key->x() . $ec2Key->y();
    }

    /**
     * @param   string  $publicKey Obvious
     *
     * @return  void
     * @throws  \Assert\AssertionFailedException
     * @since   4.2.0
     */
    private function checkCertificate(string $publicKey): void
    {
        try {
            $resource = openssl_pkey_get_public($publicKey);

            if (version_compare(PHP_VERSION, '8.0', 'lt')) {
                Assertion::isResource($resource, 'Unable to read the certificate');
            } else {
                /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
                Assertion::isInstanceOf($resource, \OpenSSLAsymmetricKey::class, 'Unable to read the certificate');
            }
        } catch (\Throwable $throwable) {
            throw new \InvalidArgumentException('Invalid certificate or certificate chain', 0, $throwable);
        }

        $details = openssl_pkey_get_details($resource);
        Assertion::keyExists($details, 'ec', 'Invalid certificate or certificate chain');
        Assertion::keyExists($details['ec'], 'curve_name', 'Invalid certificate or certificate chain');
        Assertion::eq($details['ec']['curve_name'], 'prime256v1', 'Invalid certificate or certificate chain');
        Assertion::keyExists($details['ec'], 'curve_oid', 'Invalid certificate or certificate chain');
        Assertion::eq($details['ec']['curve_oid'], '1.2.840.10045.3.1.7', 'Invalid certificate or certificate chain');
    }
}
