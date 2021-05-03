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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\String\Inflector;

/**
 * Workflow base controller package.
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $default_view = 'workflows';

	/**
	 * The extension for which the workflow apply.
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
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException when no extension is set
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

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
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  BaseController|boolean  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view   = $this->input->get('view');
		$layout = $this->input->get('layout');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if (in_array($view, ['workflow', 'stage', 'transition']) && $layout == 'edit' && !$this->checkEditId('com_workflow.edit.' . $view, $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			if (!\count($this->app->getMessageQueue()))
			{
				$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			}

			$url = 'index.php?option=com_workflow&view=' . Inflector::pluralize($view) . '&extension=' . $this->input->getCmd('extension');

			$this->setRedirect(Route::_($url, false));

			return false;
		}

		return parent::display();
	}
}
