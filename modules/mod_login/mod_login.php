<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the login functions only once
require_once __DIR__ . '/helper.php';

$params->def('greeting', 1);

$type	          = ModLoginHelper::getType();
$return	          = ModLoginHelper::getReturnURL($params, $type);
$twofactormethods = ModLoginHelper::getTwoFactorMethods();
$user	          = JFactory::getUser();
$layout           = $params->get('layout', 'default');

if (!$user->guest)
{
	// Logged users must load the logout sublayout
	$layout .= '_logout';
}
else {
	// Guests can see Two Factor methods
	$twofactormethods = ModLoginHelper::getTwoFactorMethods();
}

require JModuleHelper::getLayoutPath('mod_login', $layout);
