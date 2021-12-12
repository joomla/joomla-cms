<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Module\PrivacyDashboard\Administrator\Helper\PrivacyDashboardHelper;

// Only super user can view this data
if (!$app->getIdentity()->authorise('core.admin'))
{
	return;
}

// Boot component to ensure HTML helpers are loaded
$app->bootComponent('com_privacy');

// Load the privacy component language file.
$lang = $app->getLanguage();
$lang->load('com_privacy', JPATH_ADMINISTRATOR)
	|| $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy');

$list = PrivacyDashboardHelper::getData();

if (count($list))
{
	require ModuleHelper::getLayoutPath('mod_privacy_dashboard', $params->get('layout', 'default'));
}
else
{
	echo LayoutHelper::render('joomla.content.emptystate_module', [
			'textPrefix' => 'COM_PRIVACY_REQUESTS',
			'icon'       => 'icon-lock',
		]
	);
}
