<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the login functions only once
JLoader::register('ModLoginHelper', __DIR__ . '/helper.php');

$langs            = ModLoginHelper::getLanguageList();
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$return           = ModLoginHelper::getReturnUri();

require JModuleHelper::getLayoutPath('mod_login', $params->get('layout', 'default'));
