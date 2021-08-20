<?php
/**
 * Declares the MVC controller for CronjobModel.
 *
 * * : Model implicitly defaults to CronjobModel through a call to parent::getModel()
 *
 * @todo : Check if the controller needs more overrides
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cronjobs\Administrator\Controller;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Cronjobs\Administrator\Helper\CronjobsHelper;
use function defined;

/**
 * MVC Controller for CronjobView.
 *
 * @since  __DEPLOY_VERSION__
 */
class CronjobController extends FormController
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
		$app = $this->app;
		$input = $app->getInput();
		$validJobOptions = CronjobsHelper::getCronOptions();

		$canAdd = parent::add();

		if ($canAdd !== true)
		{
			return false;
		}

		$jobType = $input->get('type');
		$jobOption = $validJobOptions->findOption($jobType) ?: null;

		if (!$jobOption)
		{
			// ? : Is this the right redirect [review]
			$redirectUrl = 'index.php?option=' . $this->option . '&view=select&layout=edit';
			$this->setRedirect(Route::_($redirectUrl, false));
			$app->enqueueMessage(Text::_('COM_SCHEDULER_ERROR_INVALID_TASK_TYPE'), 'warning');
			$canAdd = false;
		}

		$app->setUserState('com_scheduler.add.cronjob.cronjob_type', $jobType);
		$app->setUserState('com_scheduler.add.cronjob.cronjob_option', $jobOption);

		// @todo : Parameter array handling below?

		return $canAdd;
	}

	/**
	 * Override parent cancel method to reset the add cronjob state
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

		$this->app->setUserState('com_scheduler.add.cronjob.cronjob_type', null);
		$this->app->setUserState('com_scheduler.add.cronjob.cronjob_option', null);

		// ? Do we need to redirect based on URL's 'return' param? {@see ModuleController}

		return $result;
	}

	/**
	 * Edit an existing record
	 *
	 * ! Just acting as a proxy to the parent method at the moment
	 *   Due removal if no additional handling needed here ⚠
	 *
	 * @param   string  $key     Name of primary key from urlVar
	 * @param   string  $urlVar  Name of urlVar if different from primary key [?]
	 *
	 * @return boolean  True if access user has sufficient privileges
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function edit($key = null, $urlVar = null): bool
	{
		// @todo: Change or remove
		return parent::edit($key, $urlVar);
	}

	/**
	 * Check if user has permissions to add an entry
	 *
	 * ! Does nothing at the moment
	 *   Remove if no special handling required ⚠
	 *
	 * @param   array  $data  Array of input data
	 *
	 * @return boolean  True if user can add an entry
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function allowAdd($data = array()): bool
	{
		// @todo: Change or remove
		return parent::allowAdd($data);
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

		/*
		 * Zero record (id:0), return component edit permission by calling parent controller method
		 * ? : Is this the right way to do this?
		*/
		if (!$recordId)
		{
			return parent::allowEdit($data, $key);
		}

		// @todo : Check if this works as expected
		return $this->app->getIdentity()->authorise('core.edit', 'com_scheduler.cronjob.' . $recordId);

	}
}
