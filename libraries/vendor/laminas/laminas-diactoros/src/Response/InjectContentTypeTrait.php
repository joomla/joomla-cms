<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros\Response;

use function array_keys;
use function array_reduce;
use function strtolower;

trait InjectContentTypeTrait
{
    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @return array Headers with injected Content-Type
     */
    private function injectContentType(string $contentType, array $headers) : array
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);

        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;
    }
}
