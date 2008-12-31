<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$serverinfo = $params->get( 'serverinfo' );
$siteinfo 	= $params->get( 'siteinfo' );

$list = modStatsHelper::getList($params);
require(JModuleHelper::getLayoutPath('mod_stats'));
