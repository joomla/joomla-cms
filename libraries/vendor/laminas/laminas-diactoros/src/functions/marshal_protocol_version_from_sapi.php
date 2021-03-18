<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros;

use function preg_match;

/**
 * Return HTTP protocol version (X.Y) as discovered within a `$_SERVER` array.
 *
 * @throws Exception\UnrecognizedProtocolVersionException if the
 *     $server['SERVER_PROTOCOL'] value is malformed.
 */
function marshalProtocolVersionFromSapi(array $server) : string
{
    if (! isset($server['SERVER_PROTOCOL'])) {
        return '1.1';
    }

    if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
        throw Exception\UnrecognizedProtocolVersionException::forVersion(
            (string) $server['SERVER_PROTOCOL']
        );
    }

    return $matches['version'];
}
