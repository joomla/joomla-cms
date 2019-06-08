<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Only super user can view this data
if (!Factory::getUser()->authorise('core.admin'))
{
	return;
}

// Boot component to ensure HTML helpers are loaded
Factory::getApplication()->bootComponent('com_privacy');

// Load the privacy component language file.
$lang = Factory::getLanguage();
$lang->load('com_privacy', JPATH_ADMINISTRATOR, null, false, true)
	|| $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy', null, false, true);

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

JLoader::register('ModPrivacyDashboardHelper', __DIR__ . '/helper.php');

$list            = ModPrivacyDashboardHelper::getData();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_privacy_dashboard', $params->get('layout', 'default'));
