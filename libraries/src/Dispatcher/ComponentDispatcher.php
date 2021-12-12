<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Base class for a Joomla Component Dispatcher
 *
 * Dispatchers are responsible for checking ACL of a component if appropriate and
 * choosing an appropriate controller (and if necessary, a task) and executing it.
 *
 * @since  4.0.0
 */
class ComponentDispatcher extends Dispatcher
{
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $option;

	/**
	 * The MVC factory
	 *
	 * @var  MVCFactoryInterface
	 *
	 * @since   4.0.0
	 */
	protected $mvcFactory;

	/**
	 * Constructor for ComponentDispatcher
	 *
	 * @param   CMSApplicationInterface  $app         The application instance
	 * @param   Input                    $input       The input instance
	 * @param   MVCFactoryInterface      $mvcFactory  The MVC factory instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplicationInterface $app, Input $input, MVCFactoryInterface $mvcFactory)
	{
		parent::__construct($app, $input);

		$this->mvcFactory = $mvcFactory;

		// If option is not provided, detect it from dispatcher class name, ie ContentDispatcher
		if (empty($this->option))
		{
			$this->option = ComponentHelper::getComponentName(
				$this,
				str_replace('com_', '', $input->get('option'))
			);
		}

		$this->loadLanguage();
	}

	/**
	 * Load the language
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->option, JPATH_BASE) ||
		$this->app->getLanguage()->load($this->option, JPATH_COMPONENT);
	}

	/**
	 * Method to check component access permission
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function checkAccess()
	{
		// Check the user has permission to access this component if in the backend
		if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.manage', $this->option))
		{
			throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		// Check component access permission
		$this->checkAccess();

		$command = $this->input->getCmd('task', 'display');

		// Check for a controller.task command.
		if (strpos($command, '.') !== false)
		{
			// Explode the controller.task command.
			list ($controller, $task) = explode('.', $command);

			$this->input->set('controller', $controller);
			$this->input->set('task', $task);
		}
		else
		{
			// Do we have a controller?
			$controller = $this->input->get('controller', 'display');
			$task       = $command;
		}

		// Build controller config data
		$config['option'] = $this->option;

		// Set name of controller if it is passed in the request
		if ($this->input->exists('controller'))
		{
			$config['name'] = strtolower($this->input->get('controller'));
		}

		// Execute the task for this component
		$controller = $this->getController($controller, ucfirst($this->app->getName()), $config);
		$controller->execute($task);
		$controller->redirect();
	}

	/**
	 * Get a controller from the component
	 *
	 * @param   string  $name    Controller name
	 * @param   string  $client  Optional client (like Administrator, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  BaseController
	 *
	 * @since   4.0.0
	 */
	public function getController(string $name, string $client = '', array $config = array()): BaseController
	{
		// Set up the client
		$client = $client ?: ucfirst($this->app->getName());

		// Get the controller instance
		$controller = $this->mvcFactory->createController(
			$name,
			$client,
			$config,
			$this->app,
			$this->input
		);

		// Check if the controller could be created
		if (!$controller)
		{
			throw new \InvalidArgumentException(Text::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $name));
		}

		return $controller;
	}
}
