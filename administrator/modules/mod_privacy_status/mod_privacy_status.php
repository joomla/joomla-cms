<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_status
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Privacy\Administrator\Helper\PrivacyHelper;
use Joomla\Module\PrivacyStatus\Administrator\Helper\PrivacyStatusHelper;

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

$privacyPolicyInfo      = PrivacyStatusHelper::getPrivacyPolicyInfo();
$requestFormPublished   = PrivacyStatusHelper::getRequestFormPublished();
$privacyConsentPluginId = PrivacyHelper::getPrivacyConsentPluginId();
$sendMailEnabled        = (bool) Factory::getConfig()->get('mailonline', 1);
$numberOfUrgentRequests = PrivacyStatusHelper::getNumberUrgentRequests();
$urgentRequestDays      = (int) ComponentHelper::getParams('com_privacy')->get('notify', 14);

require ModuleHelper::getLayoutPath('mod_privacy_status', $params->get('layout', 'default'));
