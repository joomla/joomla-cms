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

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class MetadataServiceFactory
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(ClientInterface $httpClient, RequestFactoryInterface $requestFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    public function create(string $serviceUri, array $additionalQueryStringValues = [], array $additionalHeaders = [], ?ClientInterface $client = null): MetadataService
    {
        $client = $client ?? $this->httpClient;

        return new MetadataService($serviceUri, $client, $this->requestFactory, $additionalQueryStringValues, $additionalHeaders);
    }
}
