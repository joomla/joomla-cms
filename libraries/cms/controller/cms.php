<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerCms extends JControllerBase
{
	/**
	 * Configuration variables
	 * @var array
	 */
	protected $config;

	/**
	 * Associative array of models
	 * stored as $models[$prefix][$name] used by get models
	 * @var array
	 */
	protected $models = array();

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _redirect.
	 */
	protected $redirect;

	/**
	 * Redirect message.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _message.
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _messageType.
	 */
	protected $messageType;

	/**
	 * Instantiate the controller.
	 *
	 * @param   JInput           $input  The input object.
	 * @param   JApplicationWeb  $app    The application object.
	 * @param   array            $config Configuration
	 *
	 * @since  12.1
	 */
	public function __construct(JInput $input, $app = null, $config = array())
	{
		parent::__construct($input, $app);

		if (!array_key_exists('option', $config))
		{
			$config['option'] = $input->get('option');
		}

		$this->config = $config;
	}

	/**
	 * Method to check the session token
	 * @return void
	 */
	protected function validateSession()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	}

	/**
	 * Method to refresh the session token to prevent the back button
	 * @return void
	 */
	protected function refreshToken()
	{
		$session = JFactory::getSession();
		$session->getToken(true);
	}

	/**
	 * Method to translate a string using JText::_() method
	 *
	 * @param string $string
	 *
	 * @return string translation result
	 */
	protected function translate($string)
	{
		return JText::_($string);
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
	 * Method to get a model, creating it if it does not already exist.
	 * Uses the prefix and $name to create the class name. Format $prefix.'Model'.$name
	 * If null default values are taken from $config array
	 *
	 * @param string $prefix
	 * @param string $name
	 * @param array  $config
	 *
	 * @return JModelCms
	 * @throws ErrorException
	 */
	public function getModel($prefix = null, $name = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		if (is_null($prefix))
		{
			$prefix = $this->getPrefix();
		}

		if (is_null($name))
		{
			$name = $config['subject'];
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
	 * that can be passed a config array.
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
	 * Redirects the browser or returns false if no redirect is set.
	 * @return  boolean  False if no redirect exists.
	 *
	 * @since   12.2
	 */
	public function redirect()
	{
		if ($this->hasRedirect())
		{
			$app = $this->app;

			// Enqueue the redirect message
			$app->enqueueMessage($this->message, $this->messageType);

			// Execute the redirect
			$app->redirect($this->redirect);
		}

		return false;
	}

	/**
	 * Method to check if the controller has a redirect
	 * @return boolean
	 */
	public function hasRedirect()
	{
		if (!empty($this->redirect))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to set the default abort redirect
	 *
	 * @param string $msg  translated abort message
	 * @param string $type type of message I.E. 'error' or 'warning
	 */
	protected function abort($msg, $type)
	{
		$config   = $this->config;
		$abortUrl = 'index.php?option=' . $config['option'] . '&task=display.' . $config['subject'];
		$this->setRedirect($abortUrl, $msg, $type, true);
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string $url       URL to redirect to.
	 * @param   string $msg       Message to display on redirect. Optional.
	 * @param   string $type      Message type. Optional, defaults to 'message'.
	 * @param   bool   $useJRoute should we phrase the url with JRoute?
	 *
	 * @return  $this  Object to support chaining.
	 *
	 * @since   12.2
	 */
	public function setRedirect($url, $msg = null, $type = 'message', $useJRoute = true)
	{
		if ($useJRoute)
		{
			$this->redirect = JRoute::_($url, false);
		}
		else
		{
			$this->redirect = $url;
		}

		if ($msg !== null)
		{
			$this->message = $msg;
		}

		$this->messageType = $type;

		return $this;
	}

	/**
	 * Method to cast all values in a cid array to integer values
	 *
	 * @param array $cid
	 *
	 * @return array $cleanCid
	 */
	protected function cleanCid($cid)
	{
		$cleanCid = array();
		foreach ((array) $cid AS $id)
		{
			$cleanCid[] = (int) $id;
		}

		return $cleanCid;
	}

	/**
	 * Method to execute a task controller internally without rebuilding the stack.
	 * This is intended to be used for task chaining
	 *
	 * @param JInput       $input
	 * @param JApplication $app
	 * @param array        $config
	 *
	 * @throws InvalidArgumentException
	 * @return boolean
	 */
	protected function executeInline(JInput $input, $app = null, $config = array())
	{
		if(is_null($app))
		{
			$app = $this->app;
		}

		$config = $this->normalizeConfig($config);

		$prefix = $this->getPrefix($config['option']);

		$dispatchControllerName = $prefix.'Controller';
		if(!class_exists($dispatchControllerName))
		{
			$format = $input->getWord('format', 'html');
			throw new InvalidArgumentException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $dispatchControllerName, $format));
		}


		/** @var $dispatchController JControllerDispatcher */
		$dispatchController = new $dispatchControllerName($input, $app, $config);
		$dispatchController->mergeModels($this->models);

		return $dispatchController->execute();
	}

	/**
	 * Method to merge an array of models to $this->models array
	 * @param array $models Associative array of models that follow the $models[prefix][$name] format
	 * @param bool  $overwrite True to overwrite existing models with $models value
	 * @return void
	 */
	public function mergeModels($models = array(), $overwrite = false)
	{
		if($overwrite)
		{
			$models += $this->models;
			$this->models = $models;
		}
		else
		{
			$this->models += $models;
		}
	}
}