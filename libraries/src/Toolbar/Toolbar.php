<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout;

/**
 * ToolBar handler
 *
 * @since  1.5
 */
class Toolbar
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
	 * @var    Toolbar
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
	 * Returns the global Toolbar object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $name  The name of the toolbar.
	 *
	 * @return  \JToolbar  The JToolbar object.
	 *
	 * @since   1.5
	 */
	public static function getInstance($name = 'toolbar')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = new Toolbar($name);
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
		$btn          = func_get_args();
		$this->_bar[] = $btn;

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
		$layout = new FileLayout('joomla.toolbar.containeropen');

		$html[] = $layout->render(array('id' => $this->_name));

		// Render each button in the toolbar.
		foreach ($this->_bar as $button)
		{
			$html[] = $this->renderButton($button);
		}

		// End toolbar div.
		$layout = new FileLayout('joomla.toolbar.containerclose');

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
			return \JText::sprintf('JLIB_HTML_BUTTON_NOT_DEFINED', $type);
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

		if ($new === false && isset($this->_buttons[$signature]))
		{
			return $this->_buttons[$signature];
		}

		if (!class_exists('Joomla\\CMS\\Toolbar\\ToolbarButton'))
		{
			\JLog::add(\JText::_('JLIB_HTML_BUTTON_BASE_CLASS'), \JLog::WARNING, 'jerror');

			return false;
		}

		$buttonClass = $this->loadButtonClass($type);

		if (!$buttonClass)
		{
			if (isset($this->_buttonPath))
			{
				$dirs = $this->_buttonPath;
			}
			else
			{
				$dirs = array();
			}

			$file = \JFilterInput::getInstance()->clean(str_replace('_', DIRECTORY_SEPARATOR, strtolower($type)) . '.php', 'path');

			\JLoader::import('joomla.filesystem.path');

			if ($buttonFile = \JPath::find($dirs, $file))
			{
				include_once $buttonFile;
			}
			else
			{
				\JLog::add(\JText::sprintf('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile), \JLog::WARNING, 'jerror');

				return false;
			}

			$buttonClass = $this->loadButtonClass($type);

			if (!$buttonClass)
			{
				return false;
			}
		}

		$this->_buttons[$signature] = new $buttonClass($this);

		return $this->_buttons[$signature];
	}

	/**
	 * Load the button class including the deprecated ones.
	 *
	 * @param   string  $type  Button Type
	 *
	 * @return  string|null
	 *
	 * @since   3.8.0
	 */
	private function loadButtonClass($type)
	{
		$buttonClasses = array(
			'Joomla\\CMS\\Toolbar\\Button\\' . ucfirst($type) . 'Button',
			// @deprecated 3.8.0
			'JToolbarButton' . ucfirst($type),
			// @deprecated 3.1.4 Remove the acceptance of legacy classes starting with JButton.
			'JButton' . ucfirst($type)
		);

		foreach ($buttonClasses as $buttonClass)
		{
			if (!class_exists($buttonClass))
			{
				continue;
			}

			return $buttonClass;
		}

		return null;
	}

	/**
	 * Add a directory where Toolbar should search for button types in LIFO order.
	 *
	 * You may either pass a string or an array of directories.
	 *
	 * Toolbar will be searching for an element type in the same order you
	 * added them. If the parameter type cannot be found in the custom folders,
	 * it will look in libraries/joomla/html/toolbar/button.
	 *
	 * @param   mixed  $path  Directory or directories to search.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function addButtonPath($path)
	{
		// Loop through the path directories.
		foreach ((array) $path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed.
			if (substr($dir, -1) !== DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// Add to the top of the search dirs.
			array_unshift($this->_buttonPath, $dir);
		}
	}
}
