<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;

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
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

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
			// Get the model
			/** @var DatabaseModel $model */
			$model = $this->getModel('database');
			$model->fix($cid);

			$updateModel = new UpdateModel;
			$updateModel->purge();

			// Refresh versionable assets cache
			$this->app->flushAssets();
		}

		$this->setRedirect(Route::_('index.php?option=com_installer&view=database', false));
	}
}
