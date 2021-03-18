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

use DateTimeImmutable;
use Psr\Cache\CacheItemPoolInterface;
use Throwable;

class SimpleMetadataStatementRepository implements MetadataStatementRepository
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var MetadataService[]
     */
    private $services = [];

    /**
     * @var SingleMetadata[]
     */
    private $singleStatements = [];

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function addService(string $name, MetadataService $service): void
    {
        $this->services[$name] = $service;
    }

    public function addSingleStatement(string $name, SingleMetadata $singleStatements): void
    {
        $this->singleStatements[$name] = $singleStatements;
    }

    public function findOneByAAGUID(string $aaguid): ?MetadataStatement
    {
        $metadataStatement = $this->findOneByAAGUIDFromServices($aaguid);
        if (null !== $metadataStatement) {
            return $metadataStatement;
        }

        return $this->findOneByAAGUIDFromSingleStatements($aaguid);
    }

    private function findOneByAAGUIDFromSingleStatements(string $aaguid): ?MetadataStatement
    {
        foreach ($this->singleStatements as $name => $singleStatement) {
            try {
                $singleCacheItem = $this->cacheItemPool->getItem(sprintf('MDS-%s', $name));
                if (!$singleCacheItem->isHit()) {
                    $metadataStatement = $singleStatement->getMetadataStatement();
                    $singleCacheItem->set($metadataStatement);
                    $this->cacheItemPool->save($singleCacheItem);
                } else {
                    $metadataStatement = $singleCacheItem->get();
                }

                if ($metadataStatement->getAaguid() === $aaguid) {
                    return $metadataStatement;
                }
            } catch (Throwable $throwable) {
                continue;
            }
        }

        return null;
    }

    private function findOneByAAGUIDFromServices(string $aaguid): ?MetadataStatement
    {
        foreach ($this->services as $name => $service) {
            try {
                $tocCacheItem = $this->cacheItemPool->getItem(sprintf('TOC-%s', $name));
                if (!$tocCacheItem->isHit()) {
                    $tableOfContent = $service->getMetadataTOCPayload();
                    $tocCacheItem->set($tableOfContent);
                    $this->cacheItemPool->save($tocCacheItem);
                    $needCacheUpdate = true;
                } else {
                    $tableOfContent = $tocCacheItem->get();
                    $nextUpdate = DateTimeImmutable::createFromFormat('Y-m-d', $tableOfContent->getNextUpdate());
                    if (false === $nextUpdate) {
                        $needCacheUpdate = true;
                    } else {
                        $needCacheUpdate = $nextUpdate->getTimestamp() < time();
                        if ($needCacheUpdate) {
                            $tableOfContent = $service->getMetadataTOCPayload();
                            $tocCacheItem->set($tableOfContent);
                            $this->cacheItemPool->save($tocCacheItem);
                        }
                    }
                }
            } catch (Throwable $throwable) {
                continue;
            }
            foreach ($tableOfContent->getEntries() as $entry) {
                $url = $entry->getUrl();
                if (null === $url) {
                    continue;
                }
                try {
                    $mdsCacheItem = $this->cacheItemPool->getItem(sprintf('MDS-%s', urlencode($url)));
                    if ($mdsCacheItem->isHit() && !$needCacheUpdate) {
                        $metadataStatement = $mdsCacheItem->get();
                    } else {
                        $metadataStatement = $service->getMetadataStatementFor($entry);
                        $mdsCacheItem->set($metadataStatement);
                        $this->cacheItemPool->save($mdsCacheItem);
                    }
                    if ($metadataStatement->getAaguid() === $aaguid) {
                        return $metadataStatement;
                    }
                } catch (Throwable $throwable) {
                    continue;
                }
            }
        }

        return null;
    }
}
