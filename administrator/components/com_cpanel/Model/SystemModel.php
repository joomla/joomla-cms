<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
		$maintainSection = new SystemHeader('MOD_MENU_MAINTAIN', 'refresh');
		$infoSection     = new SystemHeader('MOD_MENU_INFORMATION', 'refresh');
		$installSection  = new SystemHeader('MOD_MENU_INSTALL', 'download');
		$manageSection   = new SystemHeader('MOD_MENU_MANAGE', 'refresh');
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
			/** @var \Joomla\Component\Checkin\Administrator\Model\CheckinModel $checkinModel */
			$checkinModel = $this->bootComponent('com_checkin')->getMVCFactory()->createModel('Checkin', 'Administrator', ['ignore_request' => true]);
			$checkins     = count($checkinModel->getItems());

			$maintainSection->addItem(
				new SystemItem('MOD_MENU_GLOBAL_CHECKIN', 'index.php?option=com_checkin', $checkins)
			);
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			/** @var \Joomla\Component\Installer\Administrator\Extension\InstallerComponent $installerComponent */
			$installerComponent = $this->bootComponent('com_installer');

			/** @var \Joomla\Component\Installer\Administrator\Model\WarningsModel $warningsModel */
			$warningsModel = $installerComponent->getMVCFactory()->createModel('Warnings', 'Administrator', ['ignore_request' => true]);
			$warningMessages = count($warningsModel->getItems());

			$infoSection->addItem(
				new SystemItem('MOD_MENU_INFORMATION_WARNINGS', 'index.php?option=com_installer&view=warnings', $warningMessages)
			);
		}

		if ($user->authorise('core.manage', 'com_postinstall'))
		{
			/** @var \Joomla\Component\Postinstall\Administrator\Model\MessagesModel $messagesModel */
			$messagesModel = $this->bootComponent('com_postinstall')->getMVCFactory()->createModel('Messages', 'Administrator', ['ignore_request' => true]);
			$messages      = count($messagesModel->getItems());

			$infoSection->addItem(
				new SystemItem('MOD_MENU_INFORMATION_POST_INSTALL_MESSAGES', 'index.php?option=com_postinstall', $messages)
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
			/** @var \Joomla\Component\Installer\Administrator\Model\DatabaseModel $warningsModel */
			$databaseModel  = $installerComponent->getMVCFactory()->createModel('Database', 'Administrator', ['ignore_request' => true]);
			$changeSet      = $databaseModel->getItems();
			$changeSetCount = 0;

			foreach ($changeSet as $item)
			{
				$changeSetCount += $item['errorsCount'];
			}

			$infoSection->addItem(
				new SystemItem(
					'MOD_MENU_SYSTEM_INFORMATION_DATABASE',
					'index.php?option=com_installer&view=database',
					$changeSetCount === 0 ? '' : $changeSetCount
				)
			);
		}

		// Install
		if ($user->authorise('core.manage', 'com_installer'))
		{
			/** @var \Joomla\Component\Installer\Administrator\Model\DiscoverModel $discoverModel */
			$discoverModel = $this->bootComponent('com_installer')->getMVCFactory()->createModel('Discover', 'Administrator', ['ignore_request' => true]);
			$discoverModel->discover();
			$discoveredExtensions = count($discoverModel->getItems());

			$installSection->addItem(
				new SystemItem('MOD_MENU_INSTALL_EXTENSIONS', 'index.php?option=com_installer&view=install')
			);
			$installSection->addItem(
				new SystemItem('MOD_MENU_INSTALL_DISCOVER', 'index.php?option=com_installer&view=discover', $discoveredExtensions)
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
			/** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $joomlaUpdateModel */
			$joomlaUpdateModel = $this->bootComponent('com_joomlaupdate')->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
			$joomlaUpdateModel->refreshUpdates(true);
			$joomlaUpdate      = $joomlaUpdateModel->getUpdateInformation();
			$hasUpdate         = $joomlaUpdate['hasUpdate'] ? $joomlaUpdate['latest'] : '';

			$updateSection->addItem(
				new SystemItem('MOD_MENU_UPDATE_JOOMLA', 'index.php?option=com_joomlaupdate', $hasUpdate)
			);
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			Updater::getInstance()->findUpdates();

			/** @var \Joomla\Component\Installer\Administrator\Model\UpdateModel $updateModel */
			$updateModel     = $installerComponent->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
			$extensionsCount = count($updateModel->getItems());

			$updateSection->addItem(
				new SystemItem('MOD_MENU_UPDATE_EXTENSIONS', 'index.php?option=com_installer&view=update', $extensionsCount)
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
