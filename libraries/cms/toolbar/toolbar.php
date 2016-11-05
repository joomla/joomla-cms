<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * ToolBar handler
 *
 * @since  1.5
 */
class JToolbar
{
	/**
	 * Toolbar name
	 *
	 * @var    string
	 */
	protected $_name = array();

	/**
	 * Toolbar array
	 *
	 * @var    array
	 */
	protected $_bar = array();

	/**
	 * Loaded buttons
	 *
	 * @var    array
	 */
	protected $_buttons = array();

	/**
	 * Directories, where button types can be stored.
	 *
	 * @var    array
	 */
	protected $_buttonPath = array();

	/**
	 * Stores the singleton instances of various toolbar.
	 *
	 * @var    JToolbar
	 * @since  2.5
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   string  $name  The toolbar name.
	 *
	 * @since   1.5
	 */
	public function __construct($name = 'toolbar')
	{
		$this->_name = $name;

		// Set base path to find buttons.
		$this->_buttonPath[] = __DIR__ . '/button';
	}

	/**
	 * Returns the global JToolbar object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $name  The name of the toolbar.
	 *
	 * @return  JToolbar  The JToolbar object.
	 *
	 * @since   1.5
	 */
	public static function getInstance($name = 'toolbar')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = new JToolbar($name);
		}

		return self::$instances[$name];
	}

	/**
	 * Set a value
	 *
	 * @return  string  The set value.
	 *
	 * @since   1.5
	 */
	public function appendButton()
	{
		// Push button onto the end of the toolbar array.
		$btn = func_get_args();
		array_push($this->_bar, $btn);

		return true;
	}

	/**
	 * Get the list of toolbar links.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		return $this->_bar;
	}

	/**
	 * Get the name of the toolbar.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get a value.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function prependButton()
	{
		// Insert button into the front of the toolbar array.
		$btn = func_get_args();
		array_unshift($this->_bar, $btn);

		return true;
	}

	/**
	 * Render a toolbar.
	 *
	 * @return  string  HTML for the toolbar.
	 *
	 * @since   1.5
	 */
	public function render()
	{
		$html = array();

		// Start toolbar div.
		$layout = new JLayoutFile('joomla.toolbar.containeropen');

		$html[] = $layout->render(array('id' => $this->_name));

		// Render each button in the toolbar.
		foreach ($this->_bar as $button)
		{
			$html[] = $this->renderButton($button);
		}

		// End toolbar div.
		$layout = new JLayoutFile('joomla.toolbar.containerclose');

		$html[] = $layout->render(array());

		return implode('', $html);
	}

	/**
	 * Render a button.
	 *
	 * @param   object  &$node  A toolbar node.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function renderButton(&$node)
	{
		// Get the button type.
		$type = $node[0];

		$button = $this->loadButtonType($type);

		// Check for error.
		if ($button === false)
		{
			return JText::sprintf('JLIB_HTML_BUTTON_NOT_DEFINED', $type);
		}

		return $button->render($node);
	}

	/**
	 * Loads a button type.
	 *
	 * @param   string   $type  Button Type
	 * @param   boolean  $new   False by default
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function loadButtonType($type, $new = false)
	{
		$signature = md5($type);

		if (isset($this->_buttons[$signature]) && $new === false)
		{
			return $this->_buttons[$signature];
		}

		if (!class_exists('JToolbarButton'))
		{
			JLog::add(JText::_('JLIB_HTML_BUTTON_BASE_CLASS'), JLog::WARNING, 'jerror');

			return false;
		}

		$buttonClass = 'JToolbarButton' . ucfirst($type);

		// @deprecated 12.3 Remove the acceptance of legacy classes starting with JButton.
		$buttonClassOld = 'JButton' . ucfirst($type);

		if (!class_exists($buttonClass))
		{
			if (!class_exists($buttonClassOld))
			{
				if (isset($this->_buttonPath))
				{
					$dirs = $this->_buttonPath;
				}
				else
				{
					$dirs = array();
				}

				$file = JFilterInput::getInstance()->clean(str_replace('_', DIRECTORY_SEPARATOR, strtolower($type)) . '.php', 'path');

				jimport('joomla.filesystem.path');

				if ($buttonFile = JPath::find($dirs, $file))
				{
					include_once $buttonFile;
				}
				else
				{
					JLog::add(JText::sprintf('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile), JLog::WARNING, 'jerror');

					return false;
				}
			}
		}

		if (!class_exists($buttonClass) && !class_exists($buttonClassOld))
		{
			// @todo remove code: return	JError::raiseError('SOME_ERROR_CODE', "Module file $buttonFile does not contain class $buttonClass.");
			return false;
		}

		$this->_buttons[$signature] = new $buttonClass($this);

		return $this->_buttons[$signature];
	}

	/**
	 * Add a directory where JToolbar should search for button types in LIFO order.
	 *
	 * You may either pass a string or an array of directories.
	 *
	 * JToolbar will be searching for an element type in the same order you
	 * added them. If the parameter type cannot be found in the custom folders,
	 * it will look in libraries/joomla/html/toolbar/button.
	 *
	 * @param   mixed  $path  Directory or directories to search.
	 *
	 * @return  void
	 *
	 * @since   boolean
	 * @see     JToolbar
	 */
	public function addButtonPath($path)
	{
		// Just force path to array.
		settype($path, 'array');

		// Loop through the path directories.
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed.
			if (substr($dir, -1) != DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// Add to the top of the search dirs.
			array_unshift($this->_buttonPath, $dir);
		}
	}
}
