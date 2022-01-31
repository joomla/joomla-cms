<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Controller;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\WorkflowModelInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Base class for a Joomla Administrator Controller
 *
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @since  1.6
 */
class AdminController extends BaseController
{
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $option;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix;

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $view_list;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 *                                         Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                         'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    The Input object for the request
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Define standard task mappings.

		// Value = 0
		$this->registerTask('unpublish', 'publish');

		// Value = 2
		$this->registerTask('archive', 'publish');

		// Value = -2
		$this->registerTask('trash', 'publish');

		// Value = -3
		$this->registerTask('report', 'publish');
		$this->registerTask('orderup', 'reorder');
		$this->registerTask('orderdown', 'reorder');

		// Guess the option as com_NameOfController.
		if (empty($this->option))
		{
			$this->option = ComponentHelper::getComponentName($this, $this->getName());
		}

		// Guess the \Text message prefix. Defaults to the option.
		if (empty($this->text_prefix))
		{
			$this->text_prefix = strtoupper($this->option);
		}

		// Guess the list view as the suffix, eg: OptionControllerSuffix.
		if (empty($this->view_list))
		{
			$reflect = new \ReflectionClass($this);

			$r = array(0 => '', 1 => '', 2 => $reflect->getShortName());

			if ($reflect->getNamespaceName())
			{
				$r[2] = str_replace('Controller', '', $r[2]);
			}
			elseif (!preg_match('/(.*)Controller(.*)/i', $reflect->getShortName(), $r))
			{
				throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
			}

			$this->view_list = strtolower($r[2]);
		}
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');

		if (!\is_array($cid) || \count($cid) < 1)
		{
			$this->app->getLogger()->warning(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', \count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}

			// Invoke the postDelete method to allow for the child class to access the model.
			$this->postDeleteHook($model, $cid);
		}

		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);
	}

	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   BaseDatabaseModel  $model  The data model object.
	 * @param   integer            $id     The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postDeleteHook(BaseDatabaseModel $model, $id = null)
	{
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function publish()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

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

			// Publish the items.
			try
			{
				$model->publish($cid, $value);
				$errors = $model->getErrors();
				$ntext = null;

				if ($value === 1)
				{
					if ($errors)
					{
						$this->app->enqueueMessage(Text::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', \count($cid)), 'error');
					}
					else
					{
						$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
					}
				}
				elseif ($value === 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value === 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
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

	/**
	 * Changes the order of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.6
	 */
	public function reorder()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids = $this->input->post->get('cid', array(), 'array');
		$inc = $this->getTask() === 'orderup' ? -1 : 1;

		$model = $this->getModel();
		$return = $model->reorder($ids, $inc);

		$redirect = Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false);

		if ($return === false)
		{
			// Reorder failed.
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
			$this->setRedirect($redirect, $message, 'error');

			return false;
		}
		else
		{
			// Reorder succeeded.
			$message = Text::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
			$this->setRedirect($redirect, $message);

			return true;
		}
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.6
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Get the input
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		$pks = ArrayHelper::toInteger($pks);
		$order = ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		$redirect = Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false);

		if ($return === false)
		{
			// Reorder failed
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
			$this->setRedirect($redirect, $message, 'error');

			return false;
		}
		else
		{
			// Reorder succeeded.
			$this->setMessage(Text::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
			$this->setRedirect($redirect);

			return true;
		}
	}

	/**
	 * Check in of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.6
	 */
	public function checkin()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids = $this->input->post->get('cid', array(), 'array');
		$cid = ArrayHelper::toInteger($ids);

		$model = $this->getModel();
		$return = $model->checkin($cid);

		if ($return === false)
		{
			// Checkin failed.
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false
				), $message, 'error'
			);

			return false;
		}
		else
		{
			// Checkin succeeded.
			$message = Text::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', \count($cid));
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false
				), $message
			);

			return true;
		}
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Get the input
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		$pks = ArrayHelper::toInteger($pks);
		$order = ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo '1';
		}

		// Close the application
		$this->app->close();
	}

	/**
	 * Method to run Transition by id of item.
	 *
	 * @return  boolean  Indicates whether the transition was successful.
	 *
	 * @since   4.0.0
	 */
	public function runTransition()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get the input
		$pks = $this->input->post->get('cid', array(), 'array');

		if (!\count($pks))
		{
			return false;
		}

		$transitionId = (int) $this->input->post->getInt('transition_id');

		// Get the model
		$model = $this->getModel();

		if (!$model instanceof WorkflowModelInterface)
		{
			return false;
		}

		$return = $model->executeTransition($pks, $transitionId);

		$redirect = Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false);

		if ($return === false)
		{
			// Transition change failed.
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_RUN_TRANSITION', $model->getError());
			$this->setRedirect($redirect, $message, 'error');

			return false;
		}

		// Transition change succeeded.
		$message = Text::_('JLIB_APPLICATION_SUCCESS_RUN_TRANSITION');
		$this->setRedirect($redirect, $message);

		return true;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   4.0.0
	 */
	protected function getRedirectToListAppend()
	{
		return '';
	}
}
