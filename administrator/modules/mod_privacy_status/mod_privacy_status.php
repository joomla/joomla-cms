<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_status
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\Privacy\Administrator\Helper\PrivacyHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Module\PrivacyStatus\Administrator\Helper\PrivacyStatusHelper;

// Only super user can view this data
if (!$app->getIdentity()->authorise('core.admin')) {
    return;
}

// Boot component to ensure HTML helpers are loaded
$app->bootComponent('com_privacy');

// Load the privacy component language file.
$lang = $app->getLanguage();
$lang->load('com_privacy', JPATH_ADMINISTRATOR)
    || $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy');

$privacyPolicyInfo            = PrivacyStatusHelper::getPrivacyPolicyInfo();
$requestFormPublished         = PrivacyStatusHelper::getRequestFormPublished();
$privacyConsentPluginId       = PrivacyHelper::getPrivacyConsentPluginId();
$sendMailEnabled              = (bool) $app->get('mailonline', 1);
$numberOfUrgentRequests       = PrivacyStatusHelper::getNumberUrgentRequests();
$urgentRequestDays            = (int) ComponentHelper::getParams('com_privacy')->get('notify', 14);
$databaseConnectionEncryption = Factory::getContainer()->get(DatabaseInterface::class)->getConnectionEncryption();

require ModuleHelper::getLayoutPath('mod_privacy_status', $params->get('layout', 'default'));
