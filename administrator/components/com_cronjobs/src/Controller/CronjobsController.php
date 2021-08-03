<?php
/**
 * Declares the MVC controller for CronJobsModel.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cronjobs
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 namespace Joomla\Component\Cronjobs\Administrator\Controller;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Cronjobs\Administrator\Model\CronjobModel;
use function defined;

/**
 * The CronjobsModel controller.
 *
 * @since __DEPLOY_VERSION__
 */
class CronjobsController extends AdminController
{
	/**
	 * Proxy for the parent method, set
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  Array of configuration parameters.
	 *
	 * @return  BaseDatabaseModel
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Cronjob', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
	{
		return parent::getModel($name, $prefix, $config);
	}
}
