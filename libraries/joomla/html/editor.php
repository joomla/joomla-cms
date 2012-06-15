<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.event.dispatcher');

/**
 * JEditor class to handle WYSIWYG editors
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
class JEditor extends JObject
{
	/**
	 * An array of Observer objects to notify
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_observers = array();

	/**
	 * The state of the observable object
	 *
	 * @var    mixed
	 * @since  11.1
	 */
	protected $_state = null;

	/**
	 * A multi dimensional array of [function][] = key for observers
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_methods = array();

	/**
	 * Editor Plugin object
	 *
	 * @var  object
	 */
	protected $_editor = null;

	/**
	 * Editor Plugin name
	 *
	 * @var  string
	 */
	protected $_name = null;

	/**
	 * Object asset
	 *
	 * @var  string
	 */
	protected $asset = null;

	/**
	 * Object author
	 *
	 * @var  string
	 */
	protected $author = null;

	/**
	 * @var    array  JEditor instances container.
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   string  $editor  The editor name
	 */
	public function __construct($editor = 'none')
	{
		$this->_name = $editor;
	}

	/**
	 * Returns the global Editor object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $editor  The editor to use.
	 *
	 * @return  object  JEditor  The Editor object.
	 *
	 * @since   11.1
	 */
	public static function getInstance($editor = 'none')
	{
		$signature = serialize($editor);

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = new JEditor($editor);
		}

		return self::$instances[$signature];
	}

	/**
	 * Get the state of the JEditor object
	 *
	 * @return  mixed    The state of the object.
	 *
	 * @since   11.1
	 */
	public function getState()
	{
		return $this->_state;
	}

	/**
	 * Attach an observer object
	 *
	 * @param   object  $observer  An observer object to attach
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function attach($observer)
	{
		if (is_array($observer))
		{
			if (!isset($observer['handler']) || !isset($observer['event']) || !is_callable($observer['handler']))
			{
				return;
			}

			// Make sure we haven't already attached this array as an observer
			foreach ($this->_observers as $check)
			{
				if (is_array($check) && $check['event'] == $observer['event'] && $check['handler'] == $observer['handler'])
				{
					return;
				}
			}

			$this->_observers[] = $observer;
			end($this->_observers);
			$methods = array($observer['event']);
		}
		else
		{
			if (!($observer instanceof JEditor))
			{
				return;
			}

			// Make sure we haven't already attached this object as an observer
			$class = get_class($observer);

			foreach ($this->_observers as $check)
			{
				if ($check instanceof $class)
				{
					return;
				}
			}

			$this->_observers[] = $observer;
			$methods = array_diff(get_class_methods($observer), get_class_methods('JPlugin'));
		}

		$key = key($this->_observers);

		foreach ($methods as $method)
		{
			$method = strtolower($method);

			if (!isset($this->_methods[$method]))
			{
				$this->_methods[$method] = array();
			}

			$this->_methods[$method][] = $key;
		}
	}

	/**
	 * Detach an observer object
	 *
	 * @param   object  $observer  An observer object to detach.
	 *
	 * @return  boolean  True if the observer object was detached.
	 *
	 * @since   11.1
	 */
	public function detach($observer)
	{
		// Initialise variables.
		$retval = false;

		$key = array_search($observer, $this->_observers);

		if ($key !== false)
		{
			unset($this->_observers[$key]);
			$retval = true;

			foreach ($this->_methods as &$method)
			{
				$k = array_search($key, $method);

				if ($k !== false)
				{
					unset($method[$k]);
				}
			}
		}

		return $retval;
	}

	/**
	 * Initialise the editor
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function initialise()
	{
		//check if editor is already loaded
		if (is_null(($this->_editor)))
		{
			return;
		}

		$args['event'] = 'onInit';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				//$return .= $result;
				$return = $result;
			}
		}

		$document = JFactory::getDocument();
		$document->addCustomTag($return);
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $html     The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   integer  $col      The number of columns for the textarea.
	 * @param   integer  $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    The object asset
	 * @param   object   $author   The author.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$this->asset = $asset;
		$this->author = $author;
		$this->_loadEditor($params);

		// Check whether editor is already loaded
		if (is_null(($this->_editor)))
		{
			return;
		}

		// Backwards compatibility. Width and height should be passed without a semicolon from now on.
		// If editor plugins need a unit like "px" for CSS styling, they need to take care of that
		$width = str_replace(';', '', $width);
		$height = str_replace(';', '', $height);

		// Initialise variables.
		$return = null;

		$args['name'] = $name;
		$args['content'] = $html;
		$args['width'] = $width;
		$args['height'] = $height;
		$args['col'] = $col;
		$args['row'] = $row;
		$args['buttons'] = $buttons;
		$args['id'] = $id ? $id : $name;
		$args['event'] = 'onDisplay';

		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				$return .= $result;
			}
		}
		return $return;
	}

	/**
	 * Save the editor content
	 *
	 * @param   string  $editor  The name of the editor control
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function save($editor)
	{
		$this->_loadEditor();

		// Check whether editor is already loaded
		if (is_null(($this->_editor)))
		{
			return;
		}

		$args[] = $editor;
		$args['event'] = 'onSave';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				$return .= $result;
			}
		}

		return $return;
	}

	/**
	 * Get the editor contents
	 *
	 * @param   string  $editor  The name of the editor control
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getContent($editor)
	{
		$this->_loadEditor();

		$args['name'] = $editor;
		$args['event'] = 'onGetContent';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				$return .= $result;
			}
		}

		return $return;
	}

	/**
	 * Set the editor contents
	 *
	 * @param   string  $editor  The name of the editor control
	 * @param   string  $html    The contents of the text area
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function setContent($editor, $html)
	{
		$this->_loadEditor();

		$args['name'] = $editor;
		$args['html'] = $html;
		$args['event'] = 'onSetContent';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				$return .= $result;
			}
		}

		return $return;
	}

	/**
	 * Get the editor extended buttons (usually from plugins)
	 *
	 * @param   string  $editor   The name of the editor.
	 * @param   mixed   $buttons  Can be boolean or array, if boolean defines if the buttons are
	 *                            displayed, if array defines a list of buttons not to show.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function getButtons($editor, $buttons = true)
	{
		$result = array();

		if (is_bool($buttons) && !$buttons)
		{
			return $result;
		}

		// Get plugins
		$plugins = JPluginHelper::getPlugin('editors-xtd');

		foreach ($plugins as $plugin)
		{
			if (is_array($buttons) && in_array($plugin->name, $buttons))
			{
				continue;
			}

			JPluginHelper::importPlugin('editors-xtd', $plugin->name, false);
			$className = 'plgButton' . $plugin->name;

			if (class_exists($className))
			{
				$plugin = new $className($this, (array) $plugin);
			}

			// Try to authenticate
			if ($temp = $plugin->onDisplay($editor, $this->asset, $this->author))
			{
				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * Load the editor
	 *
	 * @param   array  $config  Associative array of editor config paramaters
	 *
	 * @return  mixed
	 *
	 * @since   11.1
	 */
	protected function _loadEditor($config = array())
	{
		// Check whether editor is already loaded
		if (!is_null(($this->_editor)))
		{
			return;
		}

		jimport('joomla.filesystem.file');

		// Build the path to the needed editor plugin
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/editors/' . $name . '.php';

		if (!JFile::exists($path))
		{
			$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';
			if (!JFile::exists($path))
			{
				$message = JText::_('JLIB_HTML_EDITOR_CANNOT_LOAD');
				JError::raiseWarning(500, $message);
				return false;
			}
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin = JPluginHelper::getPlugin('editors', $this->_name);
		$params = new JRegistry;
		$params->loadString($plugin->params);
		$params->loadArray($config);
		$plugin->params = $params;

		// Build editor plugin classname
		$name = 'plgEditor' . $this->_name;

		if ($this->_editor = new $name($this, (array) $plugin))
		{
			// Load plugin parameters
			$this->initialise();
			JPluginHelper::importPlugin('editors-xtd');
		}
	}
}
