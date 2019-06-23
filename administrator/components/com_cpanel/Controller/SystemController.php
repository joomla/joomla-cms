<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Cpanel System Controller
 *
 * @since  4.0.0
 */
class SystemController extends BaseController
{
	/**
	 * Ajax target point for reading the specific information
	 *
	 * @throws \Exception
	 *
	 * @return void
	 * 
	 * @since  4.0.0
	 */
	public function loadSystemInfo()
	{
		$type = $this->input->get('type');

		switch ($type)
		{
			case 'postinstall':
				$count = $this->countItems('com_postinstall', 'Messages');
				break;

			case 'installationwarnings':
				$count = $this->countItems('com_installer', 'Warnings');
				break;

			case 'checkins':
				$count = $this->countItems('com_checkin', 'Checkin');
				break;

			case 'databaseupdate':
				$count = $this->countDatabaseUpdates();
				break;

			case 'systemupdate':
				$count = $this->countSystemUpdates();
				break;

			case 'extensionupdate':
				$count = $this->countExtensionUpdates();
				break;

			case 'extensiondiscover':
				$count = $this->countExtensionDiscover();
				break;

			default:
				/**
				 * @TODO: Plugin event to allow custom sections to be added (see SystemModel)
				 */
				throw new \Exception(Text::_('COM_CPANEL_ERROR_DASHBOARD_TYPE_NOT_SUPPORTED'));
		}

		echo new JsonResponse($count);
	}

	/**
	 * Returns the existing database errors of the table structur
	 *
	 * @return integer  Number of database table errors
	 *
	 * @throws \Exception
	 * @since  4.0.0
	 */
	protected function countDatabaseUpdates()
	{
		if (!$this->app->getIdentity()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		/** @var \Joomla\Component\Installer\Administrator\Extension\InstallerComponent $boot */
		$boot      = $this->app->bootComponent('com_installer');

		/** @var \Joomla\Component\Installer\Administrator\Model\DatabaseModel $model */
		$model     = $boot->getMVCFactory()->createModel('Database', 'Administrator', ['ignore_request' => true]);
		$changeSet = $model->getItems();

		$changeSetCount = 0;

		foreach ($changeSet as $item)
		{
			$changeSetCount += $item['errorsCount'];
		}

		return $changeSetCount;
	}

	/**
	 * Returns the version number of the latest update or empty string if system is uptodate
	 *
	 * @return string  The version number or empty string
	 *
	 * @throws \Exception
	 * @since  4.0.0
	 */
	protected function countSystemUpdates()
	{
		if (!$this->app->getIdentity()->authorise('core.manage', 'com_joomlaupdate'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		/** @var \Joomla\CMS\Extension\LegacyComponent $boot */
		$boot    = $this->app->bootComponent('com_joomlaupdate');

		/** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
		$model   = $boot->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

		$model->refreshUpdates(true);

		$joomlaUpdate = $model->getUpdateInformation();

		$hasUpdate = $joomlaUpdate['hasUpdate'] ? '&#x200E;' . $joomlaUpdate['latest'] : '';

		return $hasUpdate;
	}

	/**
	 * Returns the number of outdates extensions installed in the system
	 *
	 * @return integer  Number of available updates
	 *
	 * @throws \Exception
	 * @since  4.0.0
	 */
	protected function countExtensionUpdates()
	{
		if (!$this->app->getIdentity()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		/** @var \Joomla\Component\Installer\Administrator\Extension\InstallerComponent $boot */
		$boot    = $this->app->bootComponent('com_installer');

		/** @var \Joomla\Component\Installer\Administrator\Model\UpdateModel $model */
		$model   = $boot->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

		$model->findUpdates();

		$items   = count($model->getItems());

		return $items;
	}

	/**
	 * Returns the number of available extensions for installation
	 *
	 * @return integer  Number of available updates
	 *
	 * @throws \Exception
	 */
	protected function countExtensionDiscover()
	{
		if (!$this->app->getIdentity()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		/** @var \Joomla\Component\Installer\Administrator\Extension\InstallerComponent $boot */
		$boot    = $this->app->bootComponent('com_installer');

		/** @var \Joomla\Component\Installer\Administrator\Model\DiscoverModel $model */
		$model   = $boot->getMVCFactory()->createModel('Discover', 'Administrator', ['ignore_request' => true]);

		$model->discover();

		$items   = count($model->getItems());

		return $items;
	}

	/**
	 * Generic getItems counter for different calls
	 *
	 * @param   string  $extension  The extension to check and authorise for
	 * @param   string  $modelName  The Model to load
	 *
	 * @return integer The number of items
	 *
	 * @throws \Exception
	 * @since  4.0.0
	 */
	protected function countItems($extension, $modelName)
	{
		if (!$this->app->getIdentity()->authorise('core.manage', $extension))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$boot    = $this->app->bootComponent($extension);
		$model   = $boot->getMVCFactory()->createModel($modelName, 'Administrator', ['ignore_request' => true]);

		$items   = count($model->getItems());

		return $items;
	}
}
