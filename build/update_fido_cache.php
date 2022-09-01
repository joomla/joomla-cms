<?php
/**
 * @package        Joomla.Build
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs          :disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Http\HttpFactory;
use Lcobucci\JWT\Configuration;
use Webauthn\MetadataService\MetadataStatement;

echo <<< TEXT
Update FIDO Cache version 1.0

Distributed under the GNU General Public License version 2, or at your option
any later version published by the Free Software Foundation.

TEXT;

echo "Loading Joomla libraries...\n";

// Set flag that this is a parent file.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the Platform with legacy libraries.
require_once JPATH_LIBRARIES . '/bootstrap.php';

$fullPath = JPATH_BASE;

$possibleFile = end($argv);

echo "Fetching FIDO metadata statements...\n";

$http     = HttpFactory::getHttp();

try
{
    $response = $http->get('https://mds.fidoalliance.org/', [], 5);
    $rawJwt   = ($response->code < 200 || $response->code > 299) ? false : $response->body;
}
catch (\Throwable $e)
{
    echo "Cannot download FIDO metadata statements from https://mds.fidoalliance.org/";

    exit(1);
}

try
{
    $jwtConfig = Configuration::forUnsecuredSigner();
    $token     = $jwtConfig->parser()->parse($rawJwt);
}
catch (Exception $e)
{
    echo "Invalid FIDO metadata statements.";

    exit(2);
}

if (!($token instanceof \Lcobucci\JWT\Token\Plain))
{
    echo "Invalid FIDO metadata statements.";

    exit(3);
}

echo "Saving JWT file in the plugin directory...\n";

$filePath = rtrim($fullPath, '\\/') . '/plugins/system/webauthn/fido.jwt';

file_put_contents($filePath, $rawJwt);

echo "All done!";
