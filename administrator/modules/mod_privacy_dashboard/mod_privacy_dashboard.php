<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the privacy component language file.
$lang = JFactory::getLanguage();
$lang->load('com_privacy', JPATH_ADMINISTRATOR);
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

// Include the mod_privacy_dashboard functions only once
JLoader::register('ModPrivacyDashboardHelper', __DIR__ . '/helper.php');

$list            = ModPrivacyDashboardHelper::getData($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_privacy_dashboard', $params->get('layout', 'default'));
