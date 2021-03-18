<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros\Exception;

use UnexpectedValueException;

use function sprintf;

class UnrecognizedProtocolVersionException extends UnexpectedValueException implements ExceptionInterface
{
    public static function forVersion(string $version) : self
    {
        return new self(sprintf('Unrecognized protocol version (%s)', $version));
    }
}
