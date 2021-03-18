<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function Laminas\Diactoros\marshalMethodFromSapi as laminas_marshalMethodFromSapi;

/**
 * @deprecated Use Laminas\Diactoros\marshalMethodFromSapi instead
 */
function marshalMethodFromSapi(array $server) : string
{
    return laminas_marshalMethodFromSapi(...func_get_args());
}
