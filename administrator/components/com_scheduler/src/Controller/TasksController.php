<?php
/**
 * @package         Joomla.Administrator
 * @subpackage      com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Implements the MVC controller for TasksView. */

namespace Joomla\Component\Scheduler\Administrator\Controller;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * MVC Controller for TasksView.
 *
 * @since  __DEPLOY_VERSION__
 */
class TasksController extends AdminController
{
	/**
	 * Proxy for the parent method.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  Array of configuration parameters.
	 *
	 * @return  BaseDatabaseModel
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Task', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
	{
		return parent::getModel($name, $prefix, $config);
	}
}
