<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!function_exists('curl_init')) {
	throw new Exception('EnvoiMoinsCher needs the CURL PHP extension.');
}

require(dirname(__FILE__) . '/webservice.php');
require(dirname(__FILE__) . '/carrier.php');
require(dirname(__FILE__) . '/carrierslist.php');
require(dirname(__FILE__) . '/contentcategory.php');
require(dirname(__FILE__) . '/country.php');
require(dirname(__FILE__) . '/listpoints.php');
require(dirname(__FILE__) . '/orderstatus.php');
require(dirname(__FILE__) . '/parcelpoint.php');
require(dirname(__FILE__) . '/quotation.php');
require(dirname(__FILE__) . '/service.php');
require(dirname(__FILE__) . '/user.php');

