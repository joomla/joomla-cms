<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Polyfill\Php56 as p;

if (PHP_VERSION_ID < 50600) {
    if (!function_exists('hash_equals')) {
        function hash_equals($knownString, $userInput) { return p\Php56::hash_equals($knownString, $userInput); }
    }
    if (extension_loaded('ldap') && !function_exists('ldap_escape')) {
        define('LDAP_ESCAPE_FILTER', 1);
        define('LDAP_ESCAPE_DN', 2);

        function ldap_escape($subject, $ignore = '', $flags = 0) { return p\Php56::ldap_escape($subject, $ignore, $flags); }
    }
}
