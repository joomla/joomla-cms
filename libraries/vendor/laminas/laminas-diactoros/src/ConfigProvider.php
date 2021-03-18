<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ConfigProvider
{
    /**
     * Retrieve configuration for laminas-diactoros.
     *
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies.
     * Maps factory interfaces to factories.
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
                RequestFactoryInterface::class => RequestFactory::class,
                ResponseFactoryInterface::class => ResponseFactory::class,
                StreamFactoryInterface::class => StreamFactory::class,
                ServerRequestFactoryInterface::class => ServerRequestFactory::class,
                UploadedFileFactoryInterface::class => UploadedFileFactory::class,
                UriFactoryInterface::class => UriFactory::class
            ],
        ];
    }
}
