<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Updater\Updater;
use Joomla\Component\Cpanel\Administrator\Entities\SystemHeader;
use Joomla\Component\Cpanel\Administrator\Entities\SystemItem;

/**
 * Model to get a list of system icons.
 *
 * @since  4.0.0
 */
class SystemModel extends BaseDatabaseModel
{
	/**
	 * Method to get a list of system icons with plugin hooks
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getItems()
	{
		$user            = Factory::getUser();
		$systemSection   = new SystemHeader('COM_CPANEL_SYSTEM_SETUP', 'cog');
		$maintainSection = new SystemHeader('MOD_MENU_MAINTAIN', 'sync');
		$infoSection     = new SystemHeader('MOD_MENU_INFORMATION', 'sync');
		$installSection  = new SystemHeader('MOD_MENU_INSTALL', 'download');
		$manageSection   = new SystemHeader('MOD_MENU_MANAGE', 'sync');
		$updateSection   = new SystemHeader('MOD_MENU_UPDATE', 'upload');
		$templateSection = new SystemHeader('MOD_MENU_TEMPLATES', 'image');
		$accessSection   = new SystemHeader('MOD_MENU_ACCESS', 'lock');

		// System
		if ($user->authorise('core.admin'))
		{
			$systemSection->addItem(
				new SystemItem('MOD_MENU_CONFIGURATION', 'index.php?option=com_config')
			);
		}

		// Maintain
		if ($user->authorise('core.manage', 'com_cache'))
		{
			$maintainSection->addItem(
				new SystemItem('MOD_MENU_CLEAR_CACHE', 'index.php?option=com_cache')
			);
			$maintainSection->addItem(
				new SystemItem('MOD_MENU_PURGE_EXPIRED_CACHE', 'index.php?option=com_cache&view=purge')
			);
		}

		if ($user->authorise('core.manage', 'com_checkin'))
		{
			$maintainSection->addItem(
				new SystemItem('MOD_MENU_GLOBAL_CHECKIN', 'index.php?option=com_checkin', 'checkins')
			);
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			$infoSection->addItem(
				new SystemItem('MOD_MENU_INFORMATION_WARNINGS', 'index.php?option=com_installer&view=warnings', 'installationwarnings')
			);
		}

		if ($user->authorise('core.manage', 'com_postinstall'))
		{
			$infoSection->addItem(
				new SystemItem('MOD_MENU_INFORMATION_POST_INSTALL_MESSAGES', 'index.php?option=com_postinstall', 'postinstall')
			);
		}

		if ($user->authorise('core.admin'))
		{
			$infoSection->addItem(
				new SystemItem('MOD_MENU_SYSTEM_INFORMATION_SYSINFO', 'index.php?option=com_admin&view=sysinfo')
			);
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			$infoSection->addItem(
				new SystemItem('MOD_MENU_SYSTEM_INFORMATION_DATABASE', 'index.php?option=com_installer&view=database', 'databaseupdate')
			);
		}

		// Install
		if ($user->authorise('core.manage', 'com_installer'))
		{
			$installSection->addItem(
				new SystemItem('MOD_MENU_INSTALL_EXTENSIONS', 'index.php?option=com_installer&view=install')
			);
			$installSection->addItem(
				new SystemItem('MOD_MENU_INSTALL_DISCOVER', 'index.php?option=com_installer&view=discover', 'extensiondiscover')
			);
			$installSection->addItem(
				new SystemItem('MOD_MENU_INSTALL_LANGUAGES', 'index.php?option=com_installer&view=languages')
			);
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_EXTENSIONS', 'index.php?option=com_installer&view=manage')
			);
		}

		if ($user->authorise('core.manage', 'com_languages'))
		{
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_LANGUAGES', 'index.php?option=com_languages&view=installed')
			);
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_LANGUAGES_CONTENT', 'index.php?option=com_languages&view=languages')
			);
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_LANGUAGES_OVERRIDES', 'index.php?option=com_languages&view=overrides')
			);
		}

		if ($user->authorise('core.manage', 'com_csp'))
		{
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_CSP', 'index.php?option=com_csp')
			);
		}

		if ($user->authorise('core.manage', 'com_plugins'))
		{
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_PLUGINS', 'index.php?option=com_plugins')
			);
		}

		if ($user->authorise('core.manage', 'com_redirect'))
		{
			$manageSection->addItem(
				new SystemItem('MOD_MENU_MANAGE_REDIRECTS', 'index.php?option=com_redirect')
			);
		}

		if ($user->authorise('core.manage', 'com_modules'))
		{
			$manageSection->addItem(
				new SystemItem('MOD_MENU_EXTENSIONS_MODULE_MANAGER_SITE', 'index.php?option=com_modules&view=modules&client_id=0')
			);

			$manageSection->addItem(
				new SystemItem('MOD_MENU_EXTENSIONS_MODULE_MANAGER_ADMINISTRATOR', 'index.php?option=com_modules&view=modules&client_id=1')
			);
		}

		if ($user->authorise('core.manage', 'com_joomlaupdate'))
		{
			$updateSection->addItem(
				new SystemItem('MOD_MENU_UPDATE_JOOMLA', 'index.php?option=com_joomlaupdate', 'systemupdate')
			);
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			$updateSection->addItem(
				new SystemItem('MOD_MENU_UPDATE_EXTENSIONS', 'index.php?option=com_installer&view=update', 'extensionupdate')
			);

			$updateSection->addItem(
				new SystemItem('MOD_MENU_UPDATE_SOURCES', 'index.php?option=com_installer&view=updatesites')
			);
		}

		// Templates
		if ($user->authorise('core.manage', 'com_templates'))
		{
			$templateSection->addItem(
				new SystemItem('MOD_MENU_TEMPLATE_SITE_TEMPLATES', 'index.php?option=com_templates&view=templates&client_id=0')
			);

			$templateSection->addItem(
				new SystemItem('MOD_MENU_TEMPLATE_SITE_STYLES', 'index.php?option=com_templates&view=styles&client_id=0')
			);

			$templateSection->addItem(
				new SystemItem('MOD_MENU_TEMPLATE_ADMIN_TEMPLATES', 'index.php?option=com_templates&view=templates&client_id=1')
			);

			$templateSection->addItem(
				new SystemItem('MOD_MENU_TEMPLATE_ADMIN_STYLES', 'index.php?option=com_templates&view=styles&client_id=1')
			);
		}

		// Access
		if ($user->authorise('core.manage', 'com_users'))
		{
			$accessSection->addItem(
				new SystemItem('MOD_MENU_ACCESS_GROUPS', 'index.php?option=com_users&view=groups')
			);

			$accessSection->addItem(
				new SystemItem('MOD_MENU_ACCESS_LEVELS', 'index.php?option=com_users&view=levels')
			);
		}

		// Global Configuration - Permissions and Filters
		if ($user->authorise('core.admin'))
		{
			$accessSection->addItem(
				new SystemItem('MOD_MENU_ACCESS_SETTINGS', 'index.php?option=com_config#page-permissions')
			);

			$accessSection->addItem(
				new SystemItem('MOD_MENU_ACCESS_TEXT_FILTERS', 'index.php?option=com_config#page-filters')
			);
		}

		$links = [
			$systemSection,
			$maintainSection,
			$infoSection,
			$installSection,
			$manageSection,
			$updateSection,
			$templateSection,
			$accessSection
		];

		// TODO: Plugin event to allow custom sections to be added

		return $links;
	}
}
