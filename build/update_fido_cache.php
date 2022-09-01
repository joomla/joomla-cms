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

echo "Fetching FIDO metadata statements...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'follow_location' => 1,
        'timeout' => 5.0,
    ]
]);

$rawJwt = @file_get_contents('https://mds.fidoalliance.org/', false, $context);

if ($rawJwt === false) {
    echo "Could not get an updated fido.jwt file.\n";

    exit (1);
}

echo "Saving JWT file in the plugin directory...\n";

if (!isset($fullPath))
{
    $fullPath = dirname(__DIR__);
}

$filePath = rtrim($fullPath, '\\/') . '/plugins/system/webauthn/fido.jwt';

file_put_contents($filePath, $rawJwt);

echo "File saved: $filePath\n";
