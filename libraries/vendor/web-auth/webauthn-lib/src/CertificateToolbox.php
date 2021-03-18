<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Webauthn;

use Assert\Assertion;
use InvalidArgumentException;
use Symfony\Component\Process\Process;
use Webauthn\AttestationStatement\AttestationStatement;
use Webauthn\MetadataService\MetadataStatement;
use Webauthn\MetadataService\MetadataStatementRepository;

class CertificateToolbox
{
    public static function checkChain(array $certificates, array $trustedCertificates = []): void
    {
        $certificates = array_unique(array_merge($certificates, $trustedCertificates));
        if (0 === \count($certificates)) {
            return;
        }
        self::checkCertificatesValidity($certificates);
        $filenames = [];

        $leafFilename = tempnam(sys_get_temp_dir(), 'webauthn-leaf-');
        Assertion::string($leafFilename, 'Unable to get a temporary filename');

        $leafCertificate = array_shift($certificates);
        $result = file_put_contents($leafFilename, $leafCertificate);
        Assertion::integer($result, 'Unable to write temporary data');
        $filenames[] = $leafFilename;

        $processArguments = [];

        if (0 !== \count($certificates)) {
            $caFilename = tempnam(sys_get_temp_dir(), 'webauthn-ca-');
            Assertion::string($caFilename, 'Unable to get a temporary filename');

            $caCertificate = array_pop($certificates);
            $result = file_put_contents($caFilename, $caCertificate);
            Assertion::integer($result, 'Unable to write temporary data');

            $processArguments[] = '-CAfile';
            $processArguments[] = $caFilename;
            $filenames[] = $caFilename;
        }

        if (0 !== \count($certificates)) {
            $untrustedFilename = tempnam(sys_get_temp_dir(), 'webauthn-untrusted-');
            Assertion::string($untrustedFilename, 'Unable to get a temporary filename');

            foreach ($certificates as $certificate) {
                $result = file_put_contents($untrustedFilename, $certificate, FILE_APPEND);
                Assertion::integer($result, 'Unable to write temporary data');
                $result = file_put_contents($untrustedFilename, PHP_EOL, FILE_APPEND);
                Assertion::integer($result, 'Unable to write temporary data');
            }
            $processArguments[] = '-untrusted';
            $processArguments[] = $untrustedFilename;
            $filenames[] = $untrustedFilename;
        }

        $processArguments[] = $leafFilename;
        array_unshift($processArguments, 'openssl', 'verify');

        $process = new Process($processArguments);
        $process->start();
        while ($process->isRunning()) {
        }
        foreach ($filenames as $filename) {
            $result = unlink($filename);
            Assertion::true($result, 'Unable to delete temporary file');
        }

        if (!$process->isSuccessful()) {
            throw new InvalidArgumentException('Invalid certificate or certificate chain. Error is: '.$process->getErrorOutput());
        }
    }

    public static function checkAttestationMedata(AttestationStatement $attestationStatement, string $aaguid, array $certificates, MetadataStatementRepository $metadataStatementRepository): array
    {
        $metadataStatement = $metadataStatementRepository->findOneByAAGUID($aaguid);
        if (null === $metadataStatement) {
            //Check certificate CA chain
            self::checkChain($certificates);

            return $certificates;
        }

        //FIXME: to decide later if relevant
        /*Assertion::eq('fido2', $metadataStatement->getProtocolFamily(), sprintf('The protocol family of the authenticator "%s" should be "fido2". Got "%s".', $aaguid, $metadataStatement->getProtocolFamily()));
        if (null !== $metadataStatement->getAssertionScheme()) {
            Assertion::eq('FIDOV2', $metadataStatement->getAssertionScheme(), sprintf('The assertion scheme of the authenticator "%s" should be "FIDOV2". Got "%s".', $aaguid, $metadataStatement->getAssertionScheme()));
        }*/

        // Check Attestation Type is allowed
        if (0 !== \count($metadataStatement->getAttestationTypes())) {
            $type = self::getAttestationType($attestationStatement);
            Assertion::inArray($type, $metadataStatement->getAttestationTypes(), 'Invalid attestation statement. The attestation type is not allowed for this authenticator');
        }

        $attestationRootCertificates = $metadataStatement->getAttestationRootCertificates();
        if (0 === \count($attestationRootCertificates)) {
            self::checkChain($certificates);

            return $certificates;
        }

        foreach ($attestationRootCertificates as $key => $attestationRootCertificate) {
            $attestationRootCertificates[$key] = self::fixPEMStructure($attestationRootCertificate);
        }

        //Check certificate CA chain
        self::checkChain($certificates, $attestationRootCertificates);

        return $certificates;
    }

    private static function getAttestationType(AttestationStatement $attestationStatement): int
    {
        switch ($attestationStatement->getType()) {
            case AttestationStatement::TYPE_BASIC:
                return MetadataStatement::ATTESTATION_BASIC_FULL;
            case AttestationStatement::TYPE_SELF:
                return MetadataStatement::ATTESTATION_BASIC_SURROGATE;
            case AttestationStatement::TYPE_ATTCA:
                return MetadataStatement::ATTESTATION_ATTCA;
            case AttestationStatement::TYPE_ECDAA:
                return MetadataStatement::ATTESTATION_ECDAA;
            default:
                throw new InvalidArgumentException('Invalid attestation type');
        }
    }

    public static function fixPEMStructure(string $certificate): string
    {
        $pemCert = '-----BEGIN CERTIFICATE-----'.PHP_EOL;
        $pemCert .= chunk_split($certificate, 64, PHP_EOL);
        $pemCert .= '-----END CERTIFICATE-----'.PHP_EOL;

        return $pemCert;
    }

    public static function convertDERToPEM(string $certificate): string
    {
        $derCertificate = self::unusedBytesFix($certificate);

        return self::fixPEMStructure(base64_encode($derCertificate));
    }

    public static function convertAllDERToPEM(array $certificates): array
    {
        $certs = [];
        foreach ($certificates as $publicKey) {
            $certs[] = self::convertDERToPEM($publicKey);
        }

        return $certs;
    }

    private static function unusedBytesFix(string $certificate): string
    {
        $certificateHash = hash('sha256', $certificate);
        if (\in_array($certificateHash, self::getCertificateHashes(), true)) {
            $certificate[mb_strlen($certificate, '8bit') - 257] = "\0";
        }

        return $certificate;
    }

    /**
     * @param string[] $certificates
     */
    private static function checkCertificatesValidity(array $certificates): void
    {
        foreach ($certificates as $certificate) {
            $parsed = openssl_x509_parse($certificate);
            Assertion::isArray($parsed, 'Unable to read the certificate');
            Assertion::keyExists($parsed, 'validTo_time_t', 'The certificate has no validity period');
            Assertion::keyExists($parsed, 'validFrom_time_t', 'The certificate has no validity period');
            Assertion::lessOrEqualThan(time(), $parsed['validTo_time_t'], 'The certificate expired');
            Assertion::greaterOrEqualThan(time(), $parsed['validFrom_time_t'], 'The certificate is not usable yet');
        }
    }

    /**
     * @return string[]
     */
    private static function getCertificateHashes(): array
    {
        return [
            '349bca1031f8c82c4ceca38b9cebf1a69df9fb3b94eed99eb3fb9aa3822d26e8',
            'dd574527df608e47ae45fbba75a2afdd5c20fd94a02419381813cd55a2a3398f',
            '1d8764f0f7cd1352df6150045c8f638e517270e8b5dda1c63ade9c2280240cae',
            'd0edc9a91a1677435a953390865d208c55b3183c6759c9b5a7ff494c322558eb',
            '6073c436dcd064a48127ddbf6032ac1a66fd59a0c24434f070d4e564c124c897',
            'ca993121846c464d666096d35f13bf44c1b05af205f9b4a1e00cf6cc10c5e511',
        ];
    }
}
