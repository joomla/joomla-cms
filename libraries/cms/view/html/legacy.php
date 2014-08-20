<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Prototype legacy view class. Allows the same views as before
 * to give people time to transition to JLayouts whilst using the new MVC
 * immediately.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JViewHtmlLegacy extends JViewLegacy implements JView
{
	/**
	 * Document Object.
	 *
	 * @var    JDocument
	 * @since  3.4
	 */
	protected $document;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModel            $model  The model object.
	 * @param   JDocument           $document  The document object.
	 * @param   SplPriorityQueue  $paths  The paths queue.
	 *
	 * @since   3.2
	 */
	public function __construct(JModel $model, JDocument $document, SplPriorityQueue $paths = null, $config = array())
	{
		$app = JFactory::getApplication();
		$component = JApplicationHelper::getComponentName();
		$this->setModel($model, true);
		$this->document = $document;

		if (isset($paths))
		{
			$paths->insert(JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $this->getName(), 2);
		}
		else
		{
			$paths = new SplPriorityQueue;
			$paths->insert(JPATH_BASE . '/components/' . $component . '/view/' . $viewName . '/tmpl', 1);
			$paths->insert(JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $this->getName(), 2);
		}

		$this->paths = $paths;

		// Set the view name if given (it should be)
		if (isset($config['view']))
		{
			$this->_name  = $config['view'];
		}

		parent::__construct($config);
	}

	/**
	 * Retrieves the data array from the default model.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	abstract public function getData();

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param   string  $tpl  The name of the template source file; automatically searches the template paths and compiles as needed.
	 *
	 * @return  string  The output of the the template script.
	 *
	 * @since   3.2
	 * @throws  RuntimeException
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

		// Change the template folder if alternative layout is in different template
		/* if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		} */

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
			throw new RuntimeException(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}
	}

	/**
	 * Method to render the view. Proxies to JViewLegacy.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.4
	 */
	public function render()
	{
		return $this->display(null);
	}

	/**
	 * Method to get the view name
	 *
	 * The view name by default parsed using the classname, or it can be set
	 * by passing a $config['view'] in the class constructor. Note that using $config['name']
	 * to set the name in the constructor is deprecated.
	 *
	 * @return  string  The name of the view
	 *
	 * @since   3.2
	 * @throws  RuntimeException
	 */
	public function getName()
	{
		if (empty($this->_name))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
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
