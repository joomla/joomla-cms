<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JViewCms extends JViewLegacy
{
	/**
	 * Configuration options
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * key of the default model in the models array
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
	
	protected function setPaths($config = array())
	{
		$class = get_class($this);
		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
		$directoryArray = preg_split('/(?=[A-Z])/', $class, null, $flags);
		$type = $directoryArray[(count($directoryArray) - 1)];
		$component = $this->clean(array_shift($directoryArray));
		
		$path_to_file = DIRECTORY_SEPARATOR;
		foreach ($directoryArray as $dir)
		{
			if ($dir != $type)
			{
				$path_to_file .= strtolower($dir).DIRECTORY_SEPARATOR;
			}
		}
		
		
		$app = JFactory::getApplication();
		$template_path = JPATH_THEMES .DIRECTORY_SEPARATOR. $app->getTemplate() .DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR;
		$template_path .= 'com_'.strtolower($component).substr($path_to_file, 0, -1);
		$this->paths['template'][] = $template_path;
		
		$this->paths['template'][] = JPATH_COMPONENT.$path_to_file.'tmpl';
	}
	
	/**
	 * Method to render a template script and return the output.
	 * @param   string  $tpl  The name of the template file to parse. Automatically searches through the template paths.
	 *
	 * @return  mixed $output A string or a ErrorException.
	 * @see     Babelu_libViewJoomlaBase::loadTemplate()
	 */
	public function render($tpl = null)
	{
		$output = $this->loadTemplate($tpl);
		
		return $output;
	}
	
	public function loadTemplate($tpl = null)
	{
		$output = null;
		
		$template = JFactory::getApplication()->getTemplate();
		$this->loadTplLanguageFiles($template);
		
		$layout = $this->layout;
		
		if (isset($tpl))
		{
			$file = $layout.'_'.$tpl;
			
			$defaultFile = 'default_'.$tpl;
			
			$tpl = $this->clean($tpl);
		}
		else 
		{
			$file = $layout;
			$defaultFile = 'default';
		}
		
		// Clean the file name
		$file = $this->clean($file).'.php';
		$defaultFile = $this->clean($defaultFile).'.php';
		
		jimport('joomla.filesystem.path');
		$templateLocation = JPath::find($this->paths['template'], $file);
		
		// if we couldn't find the layout_file, look for default_file
		if ($templateLocation == false)
		{
			$templateLocation = JPath::find($this->paths['template'], $defaultFile);
		}
		
		if ($templateLocation != false)
		{
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
		else //Panic
		{
				throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}	
	}
	
	/**
	 * Method to clean illegal characters from path variables
	 * @param mixed $subject
	 * @param string $pattern default is '/[^A-Z0-9_\.-]/i'
	 * @param mixed $replacement default is ''
	 * @return mixed
	 */
	protected function clean($subject, $pattern = '/[^A-Z0-9_\.-]/i', $replacement = '')
	{
		$subject = preg_replace('/[^A-Z0-9_\.-]/i', '', $subject);
		return $subject;
	}
	
	/**
	 * Method to load the lanauge files for the template
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
	 * @param mixed $var to escape
	 * @param booleen $htmlSpecialChars true for htmlspecialchars or false for htmlentities
	 * @param booleen $doubleEncode When doubleEncode is false PHP will not encode existing html entities, the default is to convert everything.
	 * @return mixed
	 */
	public function escape($var, $htmlSpecialChars = true, $doubleEncode = true)
	{
		if ($htmlSpecialChars)
		{
			$escaped = htmlspecialchars($var, ENT_COMPAT, 'UTF-8', $doubleEncode);
		}
		else
		{
			$escaped = htmlentities($var,ENT_COMPAT, 'UTF-8', $doubleEncode);
		}
	
		return $escaped;
	}
	
	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by JModel will be
	 * referenced by the name without JModel, eg. JModelCategory is just
	 * Category.
	 *
	 * @param   JModelLegacy  $model    The model to add to the view.
	 * @param   boolean       $default  Is this the default model?
	 *
	 * @return  object   The added model.
	 *
	 * @since   12.2
	 */
	public function setModel($model, $default = false)
	{
		$name = $this->config['subject'];
		$this->_models[$name] = $model;
	
		if ($default)
		{
			$this->_defaultModel = $name;
		}
		return $model;
	}
	
	/**
	 * Method to get the model object
	 *
	 * @param   string  $name  The name of the model (optional)
	 *
	 * @return  mixed  JModelLegacy object
	 *
	 * @since   12.2
	 */
	public function getModel($name = null)
	{
		if ($name === null)
		{
			$name = $this->_defaultModel;
		}
		return $this->_models[$name];
	}
	
	protected function canDo($action, $assetName = null)
	{
		$model = $this->getModel();
		
		return $model->allowAction($action, $assetName);
	}
	
}