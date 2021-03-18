<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros;

/**
 * Retrieve the request method from the SAPI parameters.
 */
function marshalMethodFromSapi(array $server) : string
{
    return $server['REQUEST_METHOD'] ?? 'GET';
}
