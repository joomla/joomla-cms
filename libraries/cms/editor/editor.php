<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\AbstractEvent;
use Joomla\Registry\Registry;

/**
 * JEditor class to handle WYSIWYG editors
 *
 * @since  1.5
 */
class JEditor implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * Editor Plugin object
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $_editor = null;

	/**
	 * Editor Plugin name
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_name = null;

	/**
	 * Object asset
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $asset = null;

	/**
	 * Object author
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $author = null;

	/**
	 * JEditor instances container.
	 *
	 * @var    JEditor[]
	 * @since  2.5
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   string               $editor      The editor name
	 * @param   DispatcherInterface  $dispatcher  The event dispatcher we're going to use
	 */
	public function __construct($editor = 'none', DispatcherInterface $dispatcher = null)
	{
		$this->_name = $editor;

		// Set the dispatcher
		if (!is_object($dispatcher))
		{
			$dispatcher = JFactory::getContainer()->get('dispatcher');
		}

		$this->setDispatcher($dispatcher);

		// Register the getButtons event
		$this->getDispatcher()->addListener(
			'getButtons',
			function(AbstractEvent $event) {
				$editor = $event->getArgument('editor', null);
				$buttons = $event->getArgument('buttons', null);
				$result = $event->getArgument('result', []);
				$newResult = $this->getButtons($editor, $buttons);
				$newResult = (array) $newResult;
				$event['result'] = array_merge($result, $newResult);
			}
		);
	}

	/**
	 * Returns the global Editor object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $editor  The editor to use.
	 *
	 * @return  JEditor The Editor object.
	 *
	 * @since   1.5
	 */
	public static function getInstance($editor = 'none')
	{
		$signature = serialize($editor);

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = new static($editor);
		}

		return self::$instances[$signature];
	}

	/**
	 * Initialise the editor
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function initialise()
	{
		// Check if editor is already loaded
		if (is_null(($this->_editor)))
		{
			return;
		}

		$event = new Event('onInit');

		$return    = '';
		$results   = $this->getDispatcher()->dispatch('onInit', $event);

		foreach ($results['result'] as $result)
		{
			if (trim($result))
			{
				$return = $result;
			}
		}

		$document = JFactory::getDocument();

		if (method_exists($document, 'addCustomTag') && !empty($return))
		{
			$document->addCustomTag($return);
		}
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
	 * @since   1.5
	 */
	public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$this->asset = $asset;
		$this->author = $author;
		$this->_loadEditor($params);

		// Check whether editor is already loaded
		if (is_null(($this->_editor)))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JLIB_NO_EDITOR_PLUGIN_PUBLISHED'), 'danger');

			return;
		}

		// Backwards compatibility. Width and height should be passed without a semicolon from now on.
		// If editor plugins need a unit like "px" for CSS styling, they need to take care of that
		$width = str_replace(';', '', $width);
		$height = str_replace(';', '', $height);

		$return = null;

		$args['name'] = $name;
		$args['content'] = $html;
		$args['width'] = $width;
		$args['height'] = $height;
		$args['col'] = $col;
		$args['row'] = $row;
		$args['buttons'] = $buttons;
		$args['id'] = $id ? $id : $name;

		$editorId = (object) array('id' => $args['id']);

		// Register the getContent event
		$this->getDispatcher()->addListener(
			'getContent',
			function(AbstractEvent $event) use ($editorId) {
				$editor = $editorId->id;
				$result = $event->getArgument('result', []);
				$result[] = $this->getContent($editor);
				$event['result'] = $result;
			}
		);

		// Register the setContent event
		$this->getDispatcher()->addListener(
			'setContent',
			function(AbstractEvent $event) use ($editorId) {
				$editor = $editorId->id;
				$html = $event->getArgument('html', null);
				$result = $event->getArgument('result', []);
				$result[] = $this->setContent($editor, $html);
				$event['result'] = $result;
			}
		);

		// Register the save event
		$this->getDispatcher()->addListener(
			'save',
			function(AbstractEvent $event) use ($editorId) {
				$editor = $editorId->id;
				$result = $event->getArgument('result', []);
				$result[] = $this->save($editor);
				$event['result'] = $result;
			}
		);

		$event = new Event('onDisplay', $args);

		$results = $this->getDispatcher()->dispatch('onDisplay', $event);

		foreach ($results['result'] as $result)
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
	 * @since   1.5
	 */
	public function save($editor)
	{
		$this->_loadEditor();

		// Check whether editor is already loaded
		if (is_null(($this->_editor)))
		{
			return '';
		}

		$args[] = $editor;

		$event = new Event('onSave', $args);

		$return = '';
		$results = $this->getDispatcher()->dispatch('onSave', $event);

		foreach ($results['result'] as $result)
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
	 * @since   1.5
	 */
	public function getContent($editor)
	{
		$this->_loadEditor();

		$args['name'] = $editor;

		$event = new Event('onGetContent', $args);

		$return = '';
		$results = $this->getDispatcher()->dispatch('onGetContent', $event);

		foreach ($results['result'] as $result)
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
	 * @since   1.5
	 */
	public function setContent($editor, $html)
	{
		$this->_loadEditor();

		$args['name'] = $editor;
		$args['html'] = $html;

		$event = new Event('onSetContent', $args);

		$return = '';
		$results = $this->getDispatcher()->dispatch('onSetContent', $event);

		foreach ($results['result'] as $result)
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
	 * @since   1.5
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
			$className = 'PlgEditorsXtd' . $plugin->name;

			if (!class_exists($className))
			{
				$className = 'PlgButton' . $plugin->name;
			}

			if (class_exists($className))
			{
				$dispatcher = $this->getDispatcher();
				$plugin = new $className($dispatcher, (array) $plugin);
			}

			// Try to authenticate
			if (!method_exists($plugin, 'onDisplay'))
			{
				continue;
			}

			$button = $plugin->onDisplay($editor, $this->asset, $this->author);

			if (empty($button))
			{
				continue;
			}

			if (is_array($button))
			{
				$result = array_merge($result, $button);
				continue;
			}

			$result[] = $button;
		}

		return $result;
	}

	/**
	 * Load the editor
	 *
	 * @param   array  $config  Associative array of editor config parameters
	 *
	 * @return  mixed
	 *
	 * @since   1.5
	 */
	protected function _loadEditor($config = array())
	{
		// Check whether editor is already loaded
		if (!is_null(($this->_editor)))
		{
			return false;
		}

		// Build the path to the needed editor plugin
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';

		if (!is_file($path))
		{
			JLog::add(JText::_('JLIB_HTML_EDITOR_CANNOT_LOAD'), JLog::WARNING, 'jerror');

			return false;
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin = JPluginHelper::getPlugin('editors', $this->_name);

		// If no plugin is published we get an empty array and there not so much to do with it
		if (empty($plugin))
		{
			return false;
		}

		$params = new Registry($plugin->params);
		$params->loadArray($config);
		$plugin->params = $params;

		// Build editor plugin classname
		$name = 'PlgEditor' . $this->_name;

		$dispatcher = $this->getDispatcher();

		if ($this->_editor = new $name($dispatcher, (array) $plugin))
		{
			// Load plugin parameters
			$this->initialise();
			JPluginHelper::importPlugin('editors-xtd');
		}
	}
}
