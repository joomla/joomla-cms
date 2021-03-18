<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros\Exception;

use Throwable;
use UnexpectedValueException;

class DeserializationException extends UnexpectedValueException implements ExceptionInterface
{
    public static function forInvalidHeader() : self
    {
        throw new self('Invalid header detected');
    }

    public static function forInvalidHeaderContinuation() : self
    {
        throw new self('Invalid header continuation');
    }

    public static function forRequestFromArray(Throwable $previous) : self
    {
        return new self('Cannot deserialize request', $previous->getCode(), $previous);
    }

    public static function forResponseFromArray(Throwable $previous) : self
    {
        return new self('Cannot deserialize response', $previous->getCode(), $previous);
    }

    public static function forUnexpectedCarriageReturn() : self
    {
        throw new self('Unexpected carriage return detected');
    }

    public static function forUnexpectedEndOfHeaders() : self
    {
        throw new self('Unexpected end of headers');
    }

    public static function forUnexpectedLineFeed() : self
    {
        throw new self('Unexpected line feed detected');
    }
}
