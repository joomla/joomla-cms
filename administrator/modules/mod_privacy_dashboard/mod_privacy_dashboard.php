<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Only super user can view this data
if (!JFactory::getUser()->authorise('core.admin'))
{
	return;
}

// Load the privacy component language file.
$lang = JFactory::getLanguage();
$lang->load('com_privacy', JPATH_ADMINISTRATOR, null, false, true)
	|| $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy', null, false, true);

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

JLoader::register('ModPrivacyDashboardHelper', __DIR__ . '/helper.php');

$list            = ModPrivacyDashboardHelper::getData();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_privacy_dashboard', $params->get('layout', 'default'));
