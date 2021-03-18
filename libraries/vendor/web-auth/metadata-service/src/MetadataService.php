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

use function League\Uri\build;
use function League\Uri\build_query;
use function League\Uri\parse;
use function League\Uri\parse_query;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class MetadataService
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var array
     */
    private $additionalQueryStringValues;

    /**
     * @var array
     */
    private $additionalHeaders;
    /**
     * @var string
     */
    private $serviceUri;

    public function __construct(string $serviceUri, ClientInterface $httpClient, RequestFactoryInterface $requestFactory, array $additionalQueryStringValues = [], array $additionalHeaders = [])
    {
        $this->serviceUri = $serviceUri;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->additionalQueryStringValues = $additionalQueryStringValues;
        $this->additionalHeaders = $additionalHeaders;
    }

    public function getMetadataStatementFor(MetadataTOCPayloadEntry $entry): MetadataStatement
    {
        $uri = $this->buildUri($entry->getUrl());

        return MetadataStatementFetcher::fetchMetadataStatement($uri, true, $this->httpClient, $this->requestFactory, $this->additionalHeaders);
    }

    public function getMetadataTOCPayload(): MetadataTOCPayload
    {
        $uri = $this->buildUri($this->serviceUri);

        return MetadataStatementFetcher::fetchTableOfContent($uri, $this->httpClient, $this->requestFactory, $this->additionalHeaders);
    }

    private function buildUri(string $uri): string
    {
        $parsedUri = parse($uri);
        $queryString = $parsedUri['query'];
        $query = parse_query($queryString ?? '');
        foreach ($this->additionalQueryStringValues as $k => $v) {
            $query[$k] = $v;
        }
        $parsedUri['query'] = build_query($query);

        return build($parsedUri);
    }
}
