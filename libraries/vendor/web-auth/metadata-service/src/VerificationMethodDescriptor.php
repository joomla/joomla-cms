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

class VerificationMethodDescriptor
{
    public const USER_VERIFY_PRESENCE = 0x00000001;
    public const USER_VERIFY_FINGERPRINT = 0x00000002;
    public const USER_VERIFY_PASSCODE = 0x00000004;
    public const USER_VERIFY_VOICEPRINT = 0x00000008;
    public const USER_VERIFY_FACEPRINT = 0x00000010;
    public const USER_VERIFY_LOCATION = 0x00000020;
    public const USER_VERIFY_EYEPRINT = 0x00000040;
    public const USER_VERIFY_PATTERN = 0x00000080;
    public const USER_VERIFY_HANDPRINT = 0x00000100;
    public const USER_VERIFY_NONE = 0x00000200;
    public const USER_VERIFY_ALL = 0x00000400;

    /**
     * @var int
     */
    private $userVerification;

    /**
     * @var CodeAccuracyDescriptor|null
     */
    private $caDesc;

    /**
     * @var BiometricAccuracyDescriptor|null
     */
    private $baDesc;

    /**
     * @var PatternAccuracyDescriptor|null
     */
    private $paDesc;

    public function getUserVerification(): int
    {
        return $this->userVerification;
    }

    public function getCaDesc(): ?CodeAccuracyDescriptor
    {
        return $this->caDesc;
    }

    public function getBaDesc(): ?BiometricAccuracyDescriptor
    {
        return $this->baDesc;
    }

    public function getPaDesc(): ?PatternAccuracyDescriptor
    {
        return $this->paDesc;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->userVerification = $data['userVerification'] ?? null;
        $object->caDesc = isset($data['caDesc']) ? CodeAccuracyDescriptor::createFromArray($data['caDesc']) : null;
        $object->baDesc = isset($data['baDesc']) ? BiometricAccuracyDescriptor::createFromArray($data['baDesc']) : null;
        $object->paDesc = isset($data['paDesc']) ? PatternAccuracyDescriptor::createFromArray($data['paDesc']) : null;

        return $object;
    }
}
