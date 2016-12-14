<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Prototype admin view.
 *
 * @since  3.2
 */
abstract class ConfigViewCmsHtml extends JViewHtml
{
	/**
	 * The output of the template script.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $_output = null;

	/**
	 * The name of the default template source file.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $_template = null;

	/**
	 * The set of search directories for resources (templates)
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $_path = array('template' => array(), 'helper' => array());

	/**
	 * Layout extension
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $_layoutExt = 'php';

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModel            $model  The model object.
	 * @param   SplPriorityQueue  $paths  The paths queue.
	 *
	 * @since   3.2
	 */
	public function __construct(JModel $model, SplPriorityQueue $paths = null)
	{
		$app = JFactory::getApplication();
		$component = JApplicationHelper::getComponentName();
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);

		if (isset($paths))
		{
			$paths->insert(JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $this->getName(), 2);
		}

		parent::__construct($model, $paths);
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param   string  $tpl  The name of the template source file; automatically searches the template paths and compiles as needed.
	 *
	 * @return  string  The output of the the template script.
	 *
	 * @since   3.2
	 * @throws  Exception
	 */
	public function loadTemplate($tpl = null)
	{
		// Clear prior output
		$this->_output = null;

		$template = JFactory::getApplication()->getTemplate();
		$layout = $this->getLayout();

		// Create the template file name based on the layout
		$file = isset($tpl) ? $layout . '_' . $tpl : $layout;

		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		// Load the language file for the template
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);

		// Prevents adding path twise
		if (empty($this->_path['template']))
		{
			// Adding template paths
			$this->paths->top();
			$defaultPath = $this->paths->current();
			$this->paths->next();
			$templatePath = $this->paths->current();
			$this->_path['template'] = array($defaultPath, $templatePath);
		}

		// Load the template script
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));
		$this->_template = JPath::find($this->_path['template'], $filetofind);

		// If alternate layout can't be found, fall back to default layout
		if ($this->_template == false)
		{
			$filetofind = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $filetofind);
		}

		if ($this->_template != false)
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
			include $this->_template;

			// Done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for
	 * @param   array   $parts  An associative array of filename information
	 *
	 * @return  string  The filename
	 *
	 * @since   3.2
	 */
	protected function _createFileName($type, $parts = array())
	{
		switch ($type)
		{
			case 'template':
				$filename = strtolower($parts['name']) . '.' . $this->_layoutExt;
				break;

			default:
				$filename = strtolower($parts['name']) . '.php';
				break;
		}

		return $filename;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.2
	 * @throws  Exception
	 */
	public function getName()
	{
		if (empty($this->_name))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}

			$lastPart = substr($classname, $viewpos + 4);
			$pathParts = explode(' ', JStringNormalise::fromCamelCase($lastPart));

			if (!empty($pathParts[1]))
			{
				$this->_name = strtolower($pathParts[0]);
			}
			else
			{
				$this->_name = strtolower($lastPart);
			}
		}

		return $this->_name;
	}
}
