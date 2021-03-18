<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros;

use function array_key_exists;
use function is_string;
use function strpos;
use function strtolower;
use function strtr;
use function substr;

/**
 * @param array $server Values obtained from the SAPI (generally `$_SERVER`).
 * @return array Header/value pairs
 */
function marshalHeadersFromSapi(array $server) : array
{
    $headers = [];
    foreach ($server as $key => $value) {
        if (! is_string($key)) {
            continue;
        }

        if ($value === '') {
            continue;
        }

        // Apache prefixes environment variables with REDIRECT_
        // if they are added by rewrite rules
        if (strpos($key, 'REDIRECT_') === 0) {
            $key = substr($key, 9);

            // We will not overwrite existing variables with the
            // prefixed versions, though
            if (array_key_exists($key, $server)) {
                continue;
            }
        }

        if (strpos($key, 'HTTP_') === 0) {
            $name = strtr(strtolower(substr($key, 5)), '_', '-');
            $headers[$name] = $value;
            continue;
        }

        if (strpos($key, 'CONTENT_') === 0) {
            $name = strtr(strtolower($key), '_', '-');
            $headers[$name] = $value;
            continue;
        }
    }

    return $headers;
}
