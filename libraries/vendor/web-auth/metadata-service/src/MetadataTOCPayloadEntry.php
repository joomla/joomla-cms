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

namespace Webauthn\MetadataService;

use Assert\Assertion;

class MetadataTOCPayloadEntry
{
    /**
     * @var string|null
     */
    private $aaid;

    /**
     * @var string|null
     */
    private $aaguid;

    /**
     * @var string[]
     */
    private $attestationCertificateKeyIdentifiers = [];

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var BiometricStatusReport[]
     */
    private $biometricStatusReports = [];

    /**
     * @var StatusReport[]
     */
    private $statusReports = [];

    /**
     * @var string
     */
    private $timeOfLastStatusChange;

    /**
     * @var string
     */
    private $rogueListURL;

    /**
     * @var string
     */
    private $rogueListHash;

    public function getAaid(): ?string
    {
        return $this->aaid;
    }

    public function getAaguid(): ?string
    {
        return $this->aaguid;
    }

    public function getAttestationCertificateKeyIdentifiers(): array
    {
        return $this->attestationCertificateKeyIdentifiers;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getBiometricStatusReports(): array
    {
        return $this->biometricStatusReports;
    }

    /**
     * @return StatusReport[]
     */
    public function getStatusReports(): array
    {
        return $this->statusReports;
    }

    public function getTimeOfLastStatusChange(): string
    {
        return $this->timeOfLastStatusChange;
    }

    public function getRogueListURL(): string
    {
        return $this->rogueListURL;
    }

    public function getRogueListHash(): string
    {
        return $this->rogueListHash;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->aaid = $data['aaid'] ?? null;
        $object->aaguid = $data['aaguid'] ?? null;
        $object->attestationCertificateKeyIdentifiers = $data['attestationCertificateKeyIdentifiers'] ?? null;
        $object->hash = $data['hash'] ?? null;
        $object->url = $data['url'] ?? null;
        $object->biometricStatusReports = isset($data['biometricStatusReports']) ? BiometricStatusReport::createFromArray($data['biometricStatusReports']) : null;
        $object->statusReports = [];
        if (isset($data['statusReports'])) {
            Assertion::isArray($data['statusReports'], 'Invalid status report');
            foreach ($data['statusReports'] as $k => $statusReport) {
                $object->statusReports[$k] = StatusReport::createFromArray($statusReport);
            }
        }
        $object->timeOfLastStatusChange = $data['timeOfLastStatusChange'] ?? null;
        $object->rogueListURL = $data['rogueListURL'] ?? null;
        $object->rogueListHash = $data['rogueListHash'] ?? null;

        return $object;
    }
}
