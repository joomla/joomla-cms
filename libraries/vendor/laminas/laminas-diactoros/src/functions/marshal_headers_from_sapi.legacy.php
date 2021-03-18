<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function Laminas\Diactoros\marshalHeadersFromSapi as laminas_marshalHeadersFromSapi;

/**
 * @deprecated Use Laminas\Diactoros\marshalHeadersFromSapi instead
 */
function marshalHeadersFromSapi(array $server) : array
{
    return laminas_marshalHeadersFromSapi(...func_get_args());
}
