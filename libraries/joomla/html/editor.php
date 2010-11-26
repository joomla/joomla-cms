<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.event.dispatcher');

/**
 * JEditor class to handle WYSIWYG editors
 *
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JEditor extends JObservable
{
	/**
	 * Editor Plugin object
	 *
	 * @var	object
	 */
	protected $_editor = null;

	/**
	 * Editor Plugin name
	 *
	 * @var string
	 */
	protected $_name = null;

	/**
	 * Object asset
	 *
	 * @var string
	 */
	protected $asset = null;

	/**
	 * Object author
	 *
	 * @var string
	 */
	protected $author = null;

	/**
	 * constructor
	 *
	 * @param	string	The editor name
	 */
	public function __construct($editor = 'none')
	{
		$this->_name = $editor;
	}

	/**
	 * Returns the global Editor object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string	$editor  The editor to use.
	 * @return	JEditor	The Editor object.
	 */
	public static function getInstance($editor = 'none')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$signature = serialize($editor);

		if (empty ($instances[$signature])) {
			$instances[$signature] = new JEditor($editor);
		}

		return $instances[$signature];
	}

	/**
	 * Initialise the editor
	 */
	public function initialise()
	{
		//check if editor is already loaded
		if (is_null(($this->_editor))) {
			return;
		}

		$args['event'] = 'onInit';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result)) {
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
	 * @param	string	$name		The control name.
	 * @param	string	$html		The contents of the text area.
	 * @param	string	$width		The width of the text area (px or %).
	 * @param	string	$height		The height of the text area (px or %).
	 * @param	int		$col		The number of columns for the textarea.
	 * @param	int		$row		The number of rows for the textarea.
	 * @param	boolean	$buttons	True and the editor buttons will be displayed.
	 * @param	string	$id			An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param	string	$asset
	 * @param	object	$author
	 * @param	array	$params		Associative array of editor parameters.
	 */
	public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$this->asset	= $asset;
		$this->author	= $author;
		$this->_loadEditor($params);

		//check if editor is already loaded
		if (is_null(($this->_editor))) {
			return;
		}

		// Backwards compatibility. Width and height should be passed without a semicolon from now on.
		// If editor plugins need a unit like "px" for CSS styling, they need to take care of that
		$width	= str_replace(';', '', $width);
		$height	= str_replace(';', '', $height);

		// Initialise variables.
		$return = null;

		$args['name']		= $name;
		$args['content']	= $html;
		$args['width']		= $width;
		$args['height']		= $height;
		$args['col']		= $col;
		$args['row']		= $row;
		$args['buttons']	= $buttons;
		$args['id']			= $id ? $id : $name;
		$args['event']		= 'onDisplay';

		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result)) {
				$return .= $result;
			}
		}
		return $return;
	}

	/**
	 * Save the editor content
	 *
	 * @param	string	The name of the editor control
	 */
	public function save($editor)
	{
		$this->_loadEditor();

		//check if editor is already loaded
		if (is_null(($this->_editor))) {
			return;
		}

		$args[] = $editor;
		$args['event'] = 'onSave';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result)) {
				$return .= $result;
			}
		}

		return $return;
	}

	/**
	 * Get the editor contents
	 *
	 * @param	string	$editor	The name of the editor control
	 *
	 * @return	string
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
			if (trim($result)) {
				$return .= $result;
			}
		}

		return $return;
	}

	/**
	 * Set the editor contents
	 *
	 * @param	string	$editor	The name of the editor control
	 * @param	string	$html	The contents of the text area
	 *
	 * @return	string
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
			if (trim($result)) {
				$return .= $result;
			}
		}

		return $return;
	}

	/**
	 * Get the editor buttons
	 *
	 * @param	string	$editor		The name of the editor.
	 * @param	mixed	$buttons	Can be boolean or array, if boolean defines if the buttons are displayed, if array defines a list of buttons not to show.
	 *
	 * @since 1.5
	 */
	public function getButtons($editor, $buttons = true)
	{
		$result = array();

		if (is_bool($buttons) && !$buttons) {
			return $result;
		}

		// Get plugins
		$plugins = JPluginHelper::getPlugin('editors-xtd');

		foreach($plugins as $plugin)
		{
			if (is_array($buttons) &&  in_array($plugin->name, $buttons)) {
				continue;
			}

			$isLoaded = JPluginHelper::importPlugin('editors-xtd', $plugin->name, false);
			$className = 'plgButton'.$plugin->name;

			if (class_exists($className)) {
				$plugin = new $className($this, (array)$plugin);
				$plugin->loadLanguage();
			}

			// Try to authenticate
			if ($temp = $plugin->onDisplay($editor, $this->asset, $this->author)) {
				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * Load the editor
	 *
	 * @param	array	$config	Associative array of editor config paramaters
	 *
	 * @return	mixed
	 * @since	1.5
	 */
	protected function _loadEditor($config = array())
	{
		//check if editor is already loaded
		if (!is_null(($this->_editor))) {
			return;
		}

		jimport('joomla.filesystem.file');

		// Build the path to the needed editor plugin
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS.'/editors/'.$name.'.php';

		if (!JFile::exists($path)) {
			$path = JPATH_PLUGINS.'/editors/'.$name.'/'.$name.'.php';
			if (!JFile::exists($path)) {
				$message = JText::_('Cannot load the editor');
				JError::raiseWarning(500, $message);
				return false;
			}
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin		= JPluginHelper::getPlugin('editors', $this->_name);
		$className	= 'plgEditor'.$plugin->name;

		if (class_exists($className)) {
			$plugin = new $className($this, (array)$plugin);
			$plugin->loadLanguage();
		}

		$params = new JRegistry;
		$params->loadJSON($plugin->params);
		$params->loadArray($config);
		$plugin->params = $params;

		// Build editor plugin classname
		$name = 'plgEditor'.$this->_name;

		if ($this->_editor = new $name ($this, (array)$plugin)) {
			// load plugin parameters
			$this->initialise();
			JPluginHelper::importPlugin('editors-xtd');
		}
	}
}