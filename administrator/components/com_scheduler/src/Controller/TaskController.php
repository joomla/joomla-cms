<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright     (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Implements the MVC controller for TaskModel. */

namespace Joomla\Component\Scheduler\Administrator\Controller;

// Restrict direct access
\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;

/**
 * MVC Controller for TaskView.
 *
 * @since  __DEPLOY_VERSION__
 * @todo   check if we need more overrides
 *
 */
class TaskController extends FormController
{
	/**
	 * Add a new record
	 *
	 * @return boolean
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function add(): bool
	{
		/** @var AdministratorApplication $app */
		$app              = $this->app;
		$input            = $app->getInput();
		$validTaskOptions = SchedulerHelper::getTaskOptions();

		$canAdd = parent::add();

		if ($canAdd !== true)
		{
			return false;
		}

		$taskType   = $input->get('type');
		$taskOption = $validTaskOptions->findOption($taskType) ?: null;

		if (!$taskOption)
		{
			// ? : Is this the right redirect [review]
			$redirectUrl = 'index.php?option=' . $this->option . '&view=select&layout=edit';
			$this->setRedirect(Route::_($redirectUrl, false));
			$app->enqueueMessage(Text::_('COM_SCHEDULER_ERROR_INVALID_TASK_TYPE'), 'warning');
			$canAdd = false;
		}

		$app->setUserState('com_scheduler.add.task.task_type', $taskType);
		$app->setUserState('com_scheduler.add.task.task_option', $taskOption);

		// @todo : Parameter array handling below?

		return $canAdd;
	}

	/**
	 * Override parent cancel method to reset the add task state
	 *
	 * @param   ?string  $key  Primary key from the URL param
	 *
	 * @return boolean  True if access level checks pass
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function cancel($key = null): bool
	{
		$result = parent::cancel($key);

		$this->app->setUserState('com_scheduler.add.task.task_type', null);
		$this->app->setUserState('com_scheduler.add.task.task_option', null);

		// ? Do we need to redirect based on URL's 'return' param? {@see ModuleController}

		return $result;
	}

	/**
	 * Check if user has the authority to edit an asset
	 *
	 * @param   array   $data  Array of input data
	 * @param   string  $key   Name of key for primary key, defaults to 'id'
	 *
	 * @return boolean True if user is allowed to edit record
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function allowEdit($data = array(), $key = 'id'): bool
	{
		// Extract the recordId from $data, will come in handy
		$recordId = (int) $data[$key] ?? 0;

		/**
		 * Zero record (id:0), return component edit permission by calling parent controller method
		 * ? : Is this the right way to do this?
		 */
		if ($recordId === 0)
		{
			return parent::allowEdit($data, $key);
		}

		// @todo : Check if this works as expected
		return $this->app->getIdentity()->authorise('core.edit', 'com_scheduler.task.' . $recordId);

	}
}
