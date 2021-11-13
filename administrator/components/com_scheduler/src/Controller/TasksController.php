<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Controller;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

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

	/**
	 * Unlocks a running task
	 *
	 * @return void
	 */
	public function unlock()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			$this->app->getLogger()->warning(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Unlock the items.
			try
			{
				$model->unlock($cid);
				$errors = $model->getErrors();
				$ntext = null;

				if ($errors)
				{
					Factory::getApplication()->enqueueMessage(Text::plural($this->text_prefix . '_N_ITEMS_FAILED_UNLOCKING', \count($cid)), 'error');
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNLOCKED';
				}

				if (\count($cid))
				{
					$this->setMessage(Text::plural($ntext, \count($cid)));
				}
			}
			catch (\Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);
	}
}
