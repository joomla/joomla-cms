<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

abstract class JViewCms
{
	/**
	 * Configuration options
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * key of the default model in the models array
	 *
	 * @var string
	 */
	protected $defaultModel;

	/**
	 * Associative array of model objects $models[$name]
	 * @var array
	 */
	protected $models = array();

	/**
	 * Associative array of paths to search for template files in
	 *
	 * @var array
	 */
	protected $paths = array();

	/**
	 * Layout name
	 *
	 * @var    string
	 */
	protected $layout = 'default';

	public function __construct($config = array())
	{
		$this->config = $config;

		$this->setPaths($config);

		// Set the layout
		if (array_key_exists('layout', $config))
		{
			$this->layout = $config['layout'];
		}

	}

	/**
	 * Method to set the template paths.
	 * You can set your own by adding an array of paths to $config['templates']
	 * @param array $config
	 */
	protected function setPaths($config = array())
	{
		$class          = get_class($this);
		$flags          = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
		$directoryArray = preg_split('/(?=[A-Z])/', $class, null, $flags);
		$type           = $directoryArray[(count($directoryArray) - 1)];
		$component      = $this->clean(array_shift($directoryArray));

		$path_to_file = '/';
		foreach ($directoryArray as $dir)
		{
			if ($dir != $type)
			{
				$path_to_file .= strtolower($dir) . '/';
			}
		}


		$app           = JFactory::getApplication();
		$template_path = JPATH_THEMES . '/' . $app->getTemplate() . '/html/';
		$template_path .= 'com_' . strtolower($component) . substr($path_to_file, 0, -1);
		$this->paths['template'][] = $template_path;

		$this->paths['template'][] = JPATH_COMPONENT . $path_to_file . 'tmpl';

		//add paths from the config
		if(array_key_exists('templates', $config))
		{
			foreach((array) $config['templates'] AS $tmpl_path)
			{
				$this->paths['template'][] = $tmpl_path;
			}
		}
	}

	/**
	 * Method to render a template script and return the output.
	 *
	 * @param   string $tpl The name of the template file to parse. Automatically searches through the template paths.
	 *
	 * @return  mixed $output A string
	 * @see     JViewCms::loadTemplate()
	 */
	public function render($tpl = null)
	{
		$output = $this->loadTemplate($tpl);

		return $output;
	}

	/**
	 * Method to render a layout template file
	 *
	 * @param string $tpl
	 *
	 * @return string
	 * @throws ErrorException
	 */
	public function loadTemplate($tpl = null)
	{
		$template = JFactory::getApplication()->getTemplate();
		$this->loadTplLanguageFiles($template);

		$layout = $this->layout;

		if (isset($tpl))
		{
			$file = $layout . '_' . $tpl;

			$defaultFile = 'default_' . $tpl;

			$tpl = $this->clean($tpl);
		}
		else
		{
			$file        = $layout;
			$defaultFile = 'default';
		}

		// Clean the file name
		$file        = $this->clean($file) . '.php';
		$defaultFile = $this->clean($defaultFile) . '.php';

		jimport('joomla.filesystem.path');
		$templateLocation = JPath::find($this->paths['template'], $file);

		// if we couldn't find the layout_file, look for default_file
		if ($templateLocation == false)
		{
			$templateLocation = JPath::find($this->paths['template'], $defaultFile);
		}

		if ($templateLocation == false) // panic
		{
			throw new ErrorException(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}

		// Unset so as not to introduce into template scope
		unset($tpl);
		unset($file);

		// Never allow a 'this' property
		if (isset($this->this))
		{
			unset($this->this);
		}

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		// (this will execute the view logic).
		include $templateLocation;

		// Done with the requested template; get the buffer and
		// clear it.
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Method to clean illegal characters from path variables
	 *
	 * @param mixed  $subject
	 * @param string $pattern     regular expression used to clean a string default is '/[^A-Z0-9_\.-]/i'
	 * @param mixed  $replacement default is ''
	 *
	 * @return mixed
	 */
	protected function clean($subject, $pattern = '/[^A-Z0-9_\.-]/i', $replacement = '')
	{
		$subject = preg_replace($pattern, $replacement, $subject);

		return $subject;
	}

	/**
	 * Method to load the language files for the template
	 *
	 * @param string $template name
	 */
	protected function loadTplLanguageFiles($template)
	{
		// Load the language file for the template
		$lang = JFactory::getLanguage();

		if (!$lang->load('tpl_' . $template, JPATH_BASE, null, false, true))
		{
			$lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);
		}
	}

	/**
	 * Method to escape variables
	 *
	 * @param mixed $var              to escape
	 * @param bool  $htmlSpecialChars true for htmlspecialchars or false for htmlentities
	 * @param bool  $doubleEncode     When doubleEncode is false PHP will not encode existing html entities, the default is to convert everything.
	 *
	 * @return string
	 */
	public function escape($var, $htmlSpecialChars = true, $doubleEncode = true)
	{
		if ($htmlSpecialChars)
		{
			$escaped = htmlspecialchars($var, ENT_COMPAT, 'UTF-8', $doubleEncode);
		}
		else
		{
			$escaped = htmlentities($var, ENT_COMPAT, 'UTF-8', $doubleEncode);
		}

		return $escaped;
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string $name The name of the model (optional)
	 *
	 * @return  JModelCms
	 *
	 */
	public function getModel($name = null)
	{
		if ($name === null)
		{
			$name = $this->defaultModel;
		}

		return $this->models[$name];
	}


	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by class name.
	 *
	 * @param    string   $name
	 * @param   JModelCms $model   The model to add to the view.
	 * @param   boolean   $default Is this the default model?
	 *
	 * @return  JModelCms   The input parameters $model.
	 */
	public function setModel($name, $model, $default = false)
	{
		$this->models[$name] = $model;

		if ($default)
		{
			$this->defaultModel = $name;
		}

		return $model;
	}
}