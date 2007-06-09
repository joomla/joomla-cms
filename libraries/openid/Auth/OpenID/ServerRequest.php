<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * OpenID Server Request
 *
 * @see Auth_OpenID_Server
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * Imports
 */
require_once "Auth/OpenID.php";

/**
 * Object that holds the state of a request to the OpenID server
 *
 * With accessor functions to get at the internal request data.
 *
 * @see Auth_OpenID_Server
 * @package OpenID
 */
class Auth_OpenID_ServerRequest {
    function Auth_OpenID_ServerRequest()
    {
        $this->mode = null;
    }
}

?>