<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function Laminas\Diactoros\normalizeServer as laminas_normalizeServer;

/**
 * @deprecated Use Laminas\Diactoros\normalizeServer instead
 */
function normalizeServer(array $server, callable $apacheRequestHeaderCallback = null) : array
{
    return laminas_normalizeServer(...func_get_args());
}
