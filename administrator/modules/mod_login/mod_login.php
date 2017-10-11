<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Login\Administrator\Helper\LoginHelper;

$langs            = LoginHelper::getLanguageList();
$twofactormethods = AuthenticationHelper::getTwoFactorMethods();
$return           = LoginHelper::getReturnUri();

require ModuleHelper::getLayoutPath('mod_login', $params->get('layout', 'default'));
