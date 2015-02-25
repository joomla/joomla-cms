<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class JControllerCms
 * This is the base class for all MVSC controllers.
 */
abstract class JControllerCms extends JControllerBase
{
	/**
	 * Configuration variables
	 * @var array
	 */
	protected $config = array();

	/**
	 * Associative array of models
	 * stored as $models[$prefix][$name] used by get models
	 * @var array
	 */
	protected $models = array();

	/**
	 * URL to return the client to.
	 *
	 * @var    string
	 */
	protected $return;


	/**
	 * Redirect message.
	 *
	 * @var    string
	 */
	public $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 */
	public $messageType = 'message';


	/**
	 * Instantiate the controller.
	 *
	 * @param   JInput           $input  The input object.
	 * @param   JApplicationBase $app    The application object.
	 * @param   array            $config Configuration
	 */
	public function __construct(JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		parent::__construct($input, $app);

		if (!isset($config['option']))
		{
			$config['option'] = $this->input->get('option');
		}

		if (!isset($config['prefix']))
		{
			$config['prefix'] = $this->getPrefix($config['option']);
		}

		if (!isset($config['default_view']))
		{
			$config['default_view'] = 'default';
		}

		// get url variables every time
		if (!isset($config['view']))
		{
			$config['view'] = $this->input->get('view', $config['default_view']);
		}

		if (!isset($config['layout']))
		{
			$config['layout'] = $this->input->get('layout', 'default');
		}

		if (!isset($config['tmpl']))
		{
			$config['tmpl'] = $this->input->get('tmpl', null);
		}

		if (!isset($config['resource']))
		{
			$config['resource'] = $this->input->get('resource', $config['view'], 'CMD');
		}

		if (!isset($config['modal']))
		{
			$config['modal'] = $this->input->get('modal', false, 'BOOLEAN');
		}

		$this->config = $config;

	}

	/**
	 * Method to get the option prefix from the input
	 *
	 * @param string $option component option string 'com_{componentName}'
	 *
	 * @return string ucfirst(substr($this->config['option'], 4));
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
	 * Get the local configuration
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Method to get a model, creating it if it does not already exist.
	 * Uses the prefix and $name to create the class name. Format $prefix.'Model'.$name
	 * If null default values are taken from $config array
	 *
	 * @param string $prefix name of the component without 'com_', Defaults to $this->getPrefix();
	 * @param string $name   The name of the model.
	 * @param array  $config configuration array. This array is normalized. So you only need to send context specific configuration details.
	 *
	 * @return JModelCms
	 *
	 * @throws ErrorException
	 * @see JControllerCms::NormalizeConfig
	 */
	public function getModel($name, $prefix = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		if (is_null($prefix))
		{
			$prefix = $config['prefix'];
		}

		$prefix = ucfirst($prefix);
		$name   = ucfirst($name);

		if (isset($this->models[$prefix][$name]))
		{
			return $this->models[$prefix][$name];
		}

		$class = $prefix . 'Model' . $name;

		if (!class_exists($class))
		{
			throw new ErrorException(JText::sprintf('JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', $class));
		}

		$this->models[$prefix][$name] = new $class($config);

		return $this->models[$prefix][$name];
	}

	/**
	 * Method to insure all config variables are are included.
	 * Intended to be used in getModel, getView and other factory methods
	 * that can be passed a config array. Normalization is overwrite protected, so you only need to set context specific configuration details
	 *
	 * @param array $config to normalize
	 *
	 * @return array normalized config array
	 */
	protected function normalizeConfig($config)
	{
		//Safe merge. will not overwrite existing keys
		$config += $this->config;

		return $config;
	}

	/**
	 * Method to get a controller
	 *
	 * @param string           $name   of the controller to return
	 * @param string           $prefix using the format $prefix.'Controller'.$name
	 * @param JInput           $input  to use in the constructor method
	 * @param JApplicationBase $app    to use in the constructor method
	 * @param array            $config to use in the constructor method, this is normalized using the calling classes config array.
	 *
	 * @return mixed
	 */
	protected function getController($name, $prefix = null, JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		// Allows for secondary task chaining.
		//@todo experiment with multiple task chaining
		if (strpos($name, '.'))
		{
			list($name, $task) = explode('.', $name);
			$config['task'] = $task;
		}

		if (is_null($prefix))
		{
			$prefix = $config['prefix'];
		}

		$class = ucfirst($prefix) . 'Controller' . ucfirst($name);

		if (!class_exists($class))
		{
			$class = $this->getFallbackController($name, $input, $app, $config);
		}

		return new $class($input, $app, $config);
	}

	/**
	 * Method to get a the default task controller.
	 *
	 * Override this to use your own Fallback controller family.
	 *
	 * @param   string           $name   postfix name of the controller
	 * @param   JInput           $input  The input object.
	 * @param   JApplicationBase $app    The application object.
	 * @param   array            $config Configuration
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	protected function getFallbackController($name, JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		$fallbackClass = 'JController' . ucfirst($name);

		if (!class_exists($fallbackClass))
		{
			$format = $input->getWord('format', 'html');
			throw new InvalidArgumentException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $fallbackClass, $format));
		}

		return $fallbackClass;
	}

	/**
	 * Method to get the redirect url
	 * @return string
	 */
	public function getReturn()
	{
		return $this->return;
	}


	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string $url       URL to redirect to.
	 * @param   string $msg       Message to display on redirect. Optional.
	 * @param   string $msgType   Message type. Optional, defaults to 'message'.
	 * @param   bool   $useJRoute should we phrase the url with JRoute?
	 *
	 * @return  $this  Object to support chaining.
	 */
	public function setReturn($url, $msg = null, $msgType = 'message', $useJRoute = true)
	{
		$config = $this->config;

		//append modal callback if it exist
		if ($config['modal'])
		{
			$url .= '&modal=true';

			$giveTo = $this->input->get('giveTo', null, 'CMD');
			if (!is_null($giveTo))
			{
				$url .= '&giveTo=' . $giveTo;
			}
		}

		//append tmpl if it exists
		if (!is_null($config['tmpl']))
		{
			$url .= '&tmpl=' . $config['tmpl'];
		}


		if ($useJRoute)
		{
			$this->return = JRoute::_($url, false);
		}
		else
		{
			$this->return = $url;
		}

		if (!empty($msg))
		{
			$this->message     = $msg;
			$this->messageType = $msgType;
		}

		return $this;
	}


	/**
	 * Method to check if the controller has a redirect
	 *
	 * @return boolean
	 *
	 */
	public function hasReturn()
	{
		if (!empty($this->return))
		{
			return true;
		}

		return false;
	}

	/**
	 * Convenience method to check the session token.
	 *
	 * Tokens should be checked whenever a user submits data
	 * from a form that could compromise security.
	 *
	 * @throws ErrorException
	 */
	protected function validateSession()
	{
		$token = JSession::getFormToken();
		if (!$this->input->post->get($token, '', 'alnum'))
		{
			$this->setReturn('index.php');
			throw new ErrorException(JText::_('JINVALID_TOKEN'));
		}
	}

	/**
	 * Convenience method to refresh the session token to prevent the back button
	 *
	 * @return void
	 */
	protected function refreshToken()
	{
		$session = JFactory::getSession();
		$session->getToken(true);
	}

	/**
	 * Method to save the user input into state.
	 * This is intended to be used to preserve form data when server side validation fails
	 *
	 * @param string $key  dot delimited string format $context.$dataIdentifier
	 * @param mixed  $data the data to store
	 *
	 * @return void
	 */
	protected function setUserState($key = null, $data = null)
	{
		if (!is_null($key))
		{
			$session  = JFactory::getSession();
			$registry = $session->get('registry');

			if (!is_null($registry))
			{
				$registry->set($key, $data);
			}
		}
	}

	/**
	 * Method to get the users session state
	 *
	 * @param string $key     the name of the state variable
	 * @param mixed  $default return value if the state isn't set
	 *
	 * @return mixed
	 */
	protected function getUserState($key, $default = null)
	{
		$session  = JFactory::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->get($key, $default);
		}

		return null;
	}
}