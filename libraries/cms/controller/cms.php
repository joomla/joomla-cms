<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
*/
class JControllerCms extends JControllerBase implements JControllerCmsInterface
{
	// Constants that define the form of the controller passed in options params
	const CONTROLLER_PREFIX = 0;
	const CONTROLLER_ACTIVITY = 1;
	const CONTROLLER_VIEW_FOLDER = 2;
	const CONTROLLER_OPTION = 3;
	const CONTROLLER_CORE_OPTION = 2;

	/**
	 * Redeclaration of the application object as
	 * the CMS application for typehinting.
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	protected $app;

	/*
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.4
	 */
	public $prefix;

	/*
	 * An array of options
	 *
	 * @var    string
	 * @since  3.4
	 */
	public $options;

	/*
	 * The JDocument object
	 *
	 * @var    JDocument
	 * @since  3.4
	 */
	public $doc;

	/*
	 * Injected configuration array
	 *
	 * @var    array
	 * @since  3.4
	 */
	public $config;	

	/*
	 * Permission needed for the action. Defaults to most restrictive
	 *
	 * @var  string
	 * @since  3.4
	 */
	public $permission = '';

	/**
	 * Associative array of models
	 * stored as $models[$prefix][$name] used by get models
	 * @var array
	 */
	protected $models = array();

	/**
	 * Redirect message.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $messageType;

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $redirect;

	/**
	 * The view name.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $viewName;

	/**
	 * Constructor
	 *
	 * @param   array            $config  An array of configuration options. Must have option key.
	 * @param   JInput           $input   The input object.
	 * @param   JApplicationCms  $app     The application object.
	 * @param   JDocument        $doc     The JDocument object
	 *
	 * @since   3.4
	 */
	public function __construct(array $config, JInput $input = null, JApplicationCms $app = null, JDocument $doc = null)
	{
		$this->config = $config;
		$this->doc = $doc ? $doc : JFactory::getDocument();

		parent::__construct($input, $app);
	}

	/**
	 * Execute the controller.
	 * 
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.4
	 */
	public function execute()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$this->componentFolder = $this->input->getWord('option', 'com_content');
		$this->viewName     = $this->input->getWord('view', 'articles');

		return true;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return  JControllerCms  This object to support chaining.
	 *
	 * @since   3.4
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		$this->redirect = $url;

		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}

		// Set the message type.
		if (empty($type))
		{
			$this->messageType = 'message';
		}
		else
		{
			// If the type is explicitly set, set it.
			$this->messageType = $type;
		}

		return $this;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 * @since   3.4
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			// Enqueue the redirect message
			$this->app->enqueueMessage($this->message, $this->messageType);

			// Execute the redirect
			$this->app->redirect($this->redirect);
		}

		return false;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   3.4
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit', 'string');
		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		return $append;
	}

	/**
	 * Method to get a model, creating it if it does not already exist.
	 * Uses the prefix and $name to create the class name. Format $prefix.'Model'.$name
	 *
	 * @param   string  $prefix  The model prefix
	 * @param   string  $name    The model name
	 *
	 * @return  JModelCms
	 * @throws  RuntimeException
	 */
	public function getModel($prefix = null, $name = null)
	{
		if (is_null($prefix))
		{
			$prefix = $this->getPrefix();
		}

		if (is_null($name))
		{
			if (isset($this->config['model']))
			{
				$name = $this->config['model'];
			}
			else
			{
				$name = $this->config['view'];
			}
		}

		$this->config['model'] = $name;
		$prefix = ucfirst($prefix);
		$name   = ucfirst($name);

		if (isset($this->models[$prefix][$name]))
		{
			return $this->models[$prefix][$name];
		}

		$class = $prefix . 'Model' . $name;

		if (!class_exists($class))
		{
			throw new RuntimeException(JText::sprintf('JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', $class));
		}

		$this->models[$prefix][$name] = new $class($this->config);

		return $this->models[$prefix][$name];
	}

	/**
	 * Method to get the option prefix from the input
	 *
	 * @param   string  $option  Component option string 'com_{componentName}'
	 *
	 * @return  string  The prefix for models and views
	 */
	protected function getPrefix($option = null)
	{
		if (is_null($option))
		{
			$option = $this->config['option'];
		}

		$prefix = ucfirst(substr($option, 4));

		return $prefix;
	}

	/**
	 * Method to save the user input into state.
	 * This is intended to be used to preserve form data when server side validation fails
	 *
	 * @param string $key dot delimited string format $context.$dataIdentifier
	 * @param mixed $data the data to store
	 *
	 * @return void
	 */
	protected function setUserState($key = null, $data = null)
	{
		if(!is_null($key))
		{
			$this->app->setUserState($key, $data);
		}
	}
}
