<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * The workflow stages controller
 *
 * @since  4.0.0
 */
class StagesController extends AdminController
{
	/**
	 * The workflow in where the stage belongs to
	 *
	 * @var    integer
	 * @since  4.0.0
	 */
	protected $workflowId;

	/**
	 * The extension
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $extension;

	/**
	 * The section of the current extension
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $section;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $text_prefix = 'COM_WORKFLOW_STAGES';

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException when no extension or workflow id is set
	 */
	public function __construct(array $config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// If workflow id is not set try to get it from input or throw an exception
		if (empty($this->workflowId))
		{
			$this->workflowId = $this->input->getInt('workflow_id');

			if (empty($this->workflowId))
			{
				throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_ERROR_WORKFLOW_ID_NOT_SET'));
			}
		}

		// If extension is not set try to get it from input or throw an exception
		if (empty($this->extension))
		{
			$extension = $this->input->getCmd('extension');

			$parts = explode('.', $extension);

			$this->extension = array_shift($parts);

			if (!empty($parts))
			{
				$this->section = array_shift($parts);
			}

			if (empty($this->extension))
			{
				throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_ERROR_EXTENSION_NOT_SET'));
			}
		}

		$this->registerTask('unsetDefault',	'setDefault');
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since  4.0.0
	 */
	public function getModel($name = 'Stage', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to set the home property for a list of items
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function setDefault()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('setDefault' => 1, 'unsetDefault' => 0);
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (!$value)
		{
			$this->setMessage(Text::_('COM_WORKFLOW_DISABLE_DEFAULT'), 'warning');
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. '&extension=' . $this->extension, false
				)
			);

			return;
		}

		if (empty($cid) || !is_array($cid))
		{
			$this->setMessage(Text::_('COM_WORKFLOW_NO_ITEM_SELECTED'), 'warning');
		}
		elseif (count($cid) > 1)
		{
			$this->setMessage(Text::_('COM_WORKFLOW_TOO_MANY_STAGES'), 'error');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$id = (int) reset($cid);

			// Publish the items.
			if (!$model->setDefault($id, $value))
			{
				$this->setMessage($model->getError(), 'warning');
			}
			else
			{
				$this->setMessage(Text::_('COM_WORKFLOW_STAGE_SET_DEFAULT'));
			}
		}

		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. '&extension=' . $this->extension
				. '&workflow_id=' . $this->workflowId, false
			)
		);
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
		return '&extension=' . $this->extension . ($this->section ? '.' . $this->section : '') . '&workflow_id=' . $this->workflowId;
	}
}
