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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Default controller for com_scheduler.
 *
 * @since  4.1.0
 */
class DisplayController extends BaseController
{
	/**
	 * @var   string
	 * @since  4.1.0
	 */
	protected $default_view = 'tasks';

	/**
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see
	 *                               {@link InputFilter::clean()}.
	 *
	 * @return BaseController|boolean  Returns either a BaseController object to support chaining, or false on failure
	 *
	 * @since  4.1.0
	 * @throws \Exception
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$layout = $this->input->get('layout', 'default');

		// Check for edit form.
		if ($layout === 'edit')
		{
			if (!$this->validateEntry())
			{
				$tasksViewUrl = Route::_('index.php?option=com_scheduler&view=tasks', false);
				$this->setRedirect($tasksViewUrl);

				return false;
			}
		}

		// Let the parent method take over
		return parent::display($cachable, $urlparams);
	}

	/**
	 * Validates entry to the view
	 *
	 * @param   string  $layout  The layout to validate entry for (defaults to 'edit')
	 *
	 * @return boolean  True is entry is valid
	 *
	 * @since  4.1.0
	 */
	private function validateEntry(string $layout = 'edit'): bool
	{
		$context = 'com_scheduler';
		$id      = $this->input->getInt('id');
		$isValid = true;

		switch ($layout)
		{
			case 'edit':

				// True if controller was called and verified permissions
				$inEditList = $this->checkEditId("$context.edit.task", $id);
				$isNew      = ($id == 0);

				// For new item, entry is invalid if task type was not selected through SelectView
				if ($isNew && !$this->app->getUserState("$context.add.task.task_type"))
				{
					$this->setMessage((Text::_('COM_SCHEDULER_ERROR_FORBIDDEN_JUMP_TO_ADD_VIEW')), 'error');
					$isValid = false;
				}
				// For existing item, entry is invalid if TaskController has not granted access
				elseif (!$inEditList)
				{
					if (!\count($this->app->getMessageQueue()))
					{
						$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
					}

					$isValid = false;
				}
				break;
			default:
				break;
		}

		return $isValid;
	}
}
