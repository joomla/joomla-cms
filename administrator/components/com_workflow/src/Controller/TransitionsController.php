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
use Joomla\Input\Input;

/**
 * Workflow Transitions controller
 *
 * @since  4.0.0
 */
class TransitionsController extends AdminController
{
	/**
	 * The workflow where the transition takes place
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
	protected $text_prefix = 'COM_WORKFLOW_TRANSITIONS';

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException when no extension or workflow id is set
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
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
	public function getModel($name = 'Transition', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
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
		$append = parent::getRedirectToListAppend();

		$append .= '&extension=' . $this->extension . ($this->section ? '.' . $this->section : '')
			. '&workflow_id=' . $this->workflowId;

		return $append;
	}
}
