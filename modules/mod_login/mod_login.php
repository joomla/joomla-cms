<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Login\Site\Helper\LoginHelper;

$params->def('greeting', 1);

$type             = LoginHelper::getType();
$return           = LoginHelper::getReturnUrl($params, $type);
$twofactormethods = AuthenticationHelper::getTwoFactorMethods();
$user             = $app->getIdentity();
$layout           = $params->get('layout', 'default');

// Logged users must load the logout sublayout
if (!$user->guest)
{
	$layout .= '_logout';
}

require ModuleHelper::getLayoutPath('mod_login', $layout);
