<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;

/**
 * Installer Database Controller
 *
 * @since  2.5
 */
class DatabaseController extends BaseController
{
	/**
	 * Tries to fix missing database updates
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	public function fix()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Get items to fix the database.
		$cid = $this->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			$this->app->getLogger()->warning(
				Text::_(
					'COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'
				), array('category' => 'jerror')
			);
		}
		else
		{
			/** @var DatabaseModel $model */
			$model = $this->getModel('Database');
			$model->fix($cid);

			/** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $updateModel */
			$updateModel = $this->app->bootComponent('com_joomlaupdate')
				->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
			$updateModel->purge();

			// Refresh versionable assets cache
			$this->app->flushAssets();
		}

		$this->setRedirect(Route::_('index.php?option=com_installer&view=database', false));
	}

	/**
	 * Export all the database via XML
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function export()
	{
		/** @var DatabaseModel $model */
		$model = $this->getModel('Database');

		if ($model->export())
		{
			$this->setMessage(Text::_('COM_INSTALLER_MSG_DATABASE_EXPORT_OK'));
		}
		else
		{
			$this->setMessage(Text::_('COM_INSTALLER_MSG_DATABASE_EXPORT_ERROR'));
		}

		$this->setRedirect(Route::_('index.php?option=com_installer&view=database', false));
	}

	/**
	 * Import all the database via XML
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function import()
	{
		// Get file to import in the database.
		$input = Factory::getApplication()->input;
		$file = $input->files->get('zip_file', null, 'raw');

		if ($file['name'] == '')
		{
			$this->app->getLogger()->warning(
				Text::_(
					'COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'
				), array('category' => 'jerror')
			);
		}
		else
		{
			/** @var DatabaseModel $model */
			$model = $this->getModel('Database');

			if ($model->import($file))
			{
				$this->setMessage(Text::_('COM_INSTALLER_MSG_DATABASE_IMPORT_OK'));
			}
			else
			{
				$this->setMessage(Text::_('COM_INSTALLER_MSG_DATABASE_IMPORT_ERROR'));
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_installer&view=database', false));
	}

	/**
	 * Provide the data for a badge in a menu item via JSON
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMenuBadgeData()
	{
		if (!Factory::getUser()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$model = $this->getModel('Database');

		$changeSet = $model->getItems();

		$changeSetCount = 0;

		foreach ($changeSet as $item)
		{
			$changeSetCount += $item['errorsCount'];
		}

		echo new JsonResponse($changeSetCount);
	}
}
