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

class SingleMetadata
{
    /**
     * @var MetadataStatement
     */
    private $statement;
    /**
     * @var string
     */
    private $data;
    /**
     * @var bool
     */
    private $isBare64Encoded;

    public function __construct(string $data, bool $isBare64Encoded)
    {
        $this->data = $data;
        $this->isBare64Encoded = $isBare64Encoded;
    }

    public function getMetadataStatement(): MetadataStatement
    {
        if (null === $this->statement) {
            $json = $this->data;
            if ($this->isBare64Encoded) {
                $json = base64_decode($this->data, true);
                Assertion::string($json, 'Unable to decode the data');
            }
            $statement = json_decode($json, true);
            Assertion::eq(JSON_ERROR_NONE, json_last_error(), 'Unable to decode the data');
            $this->statement = MetadataStatement::createFromArray($statement);
        }

        return $this->statement;
    }
}
