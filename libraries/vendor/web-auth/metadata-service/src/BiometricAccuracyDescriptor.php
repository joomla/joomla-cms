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

class BiometricAccuracyDescriptor
{
    /**
     * @var int|null
     */
    private $selfAttestedFRR;

    /**
     * @var int|null
     */
    private $selfAttestedFAR;

    /**
     * @var int|null
     */
    private $maxTemplates;

    /**
     * @var int|null
     */
    private $maxRetries;

    /**
     * @var int|null
     */
    private $blockSlowdown;

    /**
     * @return int
     */
    public function getSelfAttestedFRR(): ?int
    {
        return $this->selfAttestedFRR;
    }

    /**
     * @return int
     */
    public function getSelfAttestedFAR(): ?int
    {
        return $this->selfAttestedFAR;
    }

    /**
     * @return int|null
     */
    public function getMaxTemplates(): ?int
    {
        return $this->maxTemplates;
    }

    /**
     * @return int|null
     */
    public function getMaxRetries(): ?int
    {
        return $this->maxRetries;
    }

    /**
     * @return int|null
     */
    public function getBlockSlowdown(): ?int
    {
        return $this->blockSlowdown;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->selfAttestedFRR = $data['selfAttestedFRR'] ?? null;
        $object->selfAttestedFAR = $data['selfAttestedFAR'] ?? null;
        $object->maxTemplates = $data['maxTemplates'] ?? null;
        $object->maxRetries = $data['maxRetries'] ?? null;
        $object->blockSlowdown = $data['blockSlowdown'] ?? null;

        return $object;
    }
}
