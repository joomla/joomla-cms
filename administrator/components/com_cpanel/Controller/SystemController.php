<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Updater\Updater;

/**
 * Cpanel Controller
 *
 * @since  1.5
 */
class SystemController extends BaseController
{
	public function loadSystemInfo()
	{
		$type = $this->input->get('type');

		$count = 0;

		switch ($type)
		{
			case 'postinstall':
				$count = $this->countPostInstallMessages();
				break;

			case 'installationwarnings':
				$count = $this->countInstallWarnings();
				break;

			case 'checkins':
				$count = $this->countCheckins();
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

	protected function countPostInstallMessages()
	{
		return $this->countItems('com_postinstall', 'Messages');
	}

	protected function countInstallWarnings()
	{
		return $this->countItems('com_installer', 'Warnings');
	}

	protected function countCheckins()
	{
		return $this->countItems('com_checkin', 'Checkin');
	}

	protected function countDatabaseUpdates()
	{
		if (!Factory::getUser()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$boot      = Factory::getApplication()->bootComponent('com_installer');
		$model     = $boot->getMVCFactory()->createModel('Database', 'Administrator', ['ignore_request' => true]);

		$changeSet = $model->getItems();

		$changeSetCount = 0;

		foreach ($changeSet as $item)
		{
			$changeSetCount += $item['errorsCount'];
		}

		return $changeSetCount;
	}

	protected function countSystemUpdates()
	{
		if (!Factory::getUser()->authorise('core.manage', 'com_joomlaupdate'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$boot    = Factory::getApplication()->bootComponent('com_joomlaupdate');
		$model   = $boot->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

		$model->refreshUpdates(true);

		$joomlaUpdate = $model->getUpdateInformation();

		$hasUpdate = $joomlaUpdate['hasUpdate'] ? $joomlaUpdate['latest'] : '';

		return $hasUpdate;
	}

	protected function countExtensionUpdates()
	{
		if (!Factory::getUser()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$boot    = Factory::getApplication()->bootComponent('com_installer');
		$model   = $boot->getMVCFactory()->createModel('Discover', 'Administrator', ['ignore_request' => true]);

		$model->discover();

		$items     = count($model->getItems());

		return $items;
	}

	protected function countExtensionDiscover()
	{
		return $this->countItems('com_installer', 'Discover');
	}

	protected function countItems($extension, $model)
	{
		if (!Factory::getUser()->authorise('core.manage', $extension))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$boot    = Factory::getApplication()->bootComponent($extension);
		$model   = $boot->getMVCFactory()->createModel($model, 'Administrator', ['ignore_request' => true]);

		$items     = count($model->getItems());

		return $items;
	}
}
