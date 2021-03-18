<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros\Exception;

use RuntimeException;

class UnseekableStreamException extends RuntimeException implements ExceptionInterface
{
    public static function dueToConfiguration() : self
    {
        return new self('Stream is not seekable');
    }

    public static function dueToMissingResource() : self
    {
        return new self('No resource available; cannot seek position');
    }

    public static function dueToPhpError() : self
    {
        return new self('Error seeking within stream');
    }

    public static function forCallbackStream() : self
    {
        return new self('Callback streams cannot seek position');
    }
}
