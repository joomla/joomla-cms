<?php

/**
 * @package        Joomla.Build
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs          :disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

echo <<< TEXT
Update FIDO Cache version 1.0

Distributed under the GNU General Public License version 2, or at your option
any later version published by the Free Software Foundation.

TEXT;

if (!isset($fullPath)) {
    $fullPath = \dirname(__DIR__);
}

$cache    = rtrim($fullPath, '\\/') . '/build/fido/fido.jwt';
$filePath = rtrim($fullPath, '\\/') . '/plugins/system/webauthn/fido.jwt';

if (is_file($cache) && filemtime($cache) > (time() - 864000)) {
    echo "The file $cache already exists and is current; copy this file to the plugin folder.\n";

    copy($cache, $filePath);

    exit(0);
}

echo "Fetching FIDO metadata statements...\n";

$context = stream_context_create([
    'http' => [
        'method'          => 'GET',
        'follow_location' => 1,
        'timeout'         => 5.0,
    ],
]);

$rawJwt = @file_get_contents('https://mds.fidoalliance.org/', false, $context);

if ($rawJwt === false) {
    echo "Could not get an updated fido.jwt file.\n";

    return;
}

echo "Saving JWT file in the plugin directory...\n";

file_put_contents($cache, $rawJwt);

echo "File saved: $cache\n";

copy($cache, $filePath);

echo "File copied: $filePath\n";
