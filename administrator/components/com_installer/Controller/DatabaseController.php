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

use Joomla\CMS\MVC\Controller\BaseController;
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
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	public function fix()
	{
		/* @var \Joomla\Component\Installer\Administrator\Model\DatabaseModel $model */
		$model = $this->getModel('database');
		$model->fix();

		$updateModel = new UpdateModel;
		$updateModel->purge();

		// Refresh versionable assets cache
		$this->app->flushAssets();

		$this->setRedirect(\JRoute::_('index.php?option=com_installer&view=database', false));
	}
}
