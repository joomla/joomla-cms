<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.event.dispatcher');

/**
 * JEditor class to handle WYSIWYG editors
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JEditor extends JObservable
{
	/**
	 * Editor Plugin object
	 *
	 *
	 */
	var $_editor = null;


	/**
	 * Editor Plugin name
	 *
	 *
	 */
	var $_name = null;

	/**
	* constructor
	*
	* @access protected
	* @param string The editor name
	*/

	function __construct($editor = 'none') {
		$this->_name = $editor;
	}

	/**
	 * Returns a reference to a global Editor object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $editor = &JEditor::getInstance([$editor);</pre>
	 *
	 * @access public
	 * @param string $editor  The editor to use.
	 * @return JEditor  The Editor object.
	 */
	function &getInstance($editor = 'none')
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
	 * Initialize the editor
	 *
	 */
	function initialise()
	{
		//check if editor is already loaded
		if(is_null(($this->_editor))) {
			return;
		}

		$args['event'] = 'onInit';

		$return = '';
		$results[] = $this->_editor->update($args);
		foreach ($results as $result) {
			if (trim($result)) {
				//$return .= $result;
				$return = $result;
			}
		}

		$document =& JFactory::getDocument();
		$document->addCustomTag($return);
	}

	/**
	 * Present a text area
	 *
	 *
	 */
	function display($name, $html, $width, $height, $col, $row, $buttons = true)
	{
		$this->_loadEditor();

		//check if editor is already loaded
		if(is_null(($this->_editor))) {
			return;
		}

		/*
		 * Backwards compatibility. Width and height should be passed without a semicolon from now on.
		 * If editor plugins need a unit like "px" for CSS styling, they need to take care of that
		 */
		$width	= str_replace( ';', '', $width );
		$height	= str_replace( ';', '', $height );

		/*
		 * Initialize variables
		 */
		$return = null;

		$args['name'] 		 = $name;
		$args['content']	 = $html;
		$args['width'] 		 = $width;
		$args['height'] 	 = $height;
		$args['col'] 		 = $col;
		$args['row'] 		 = $row;
		$args['buttons']	 = $buttons;
		$args['event'] 		 = 'onDisplay';

		$results[] = $this->_editor->update($args);

		foreach ($results as $result) {
			if (trim($result)) {
				$return .= $result;
			}
		}
		return $return;
	}

	/**
	 * Save the editor content
	 *
	 *
	 */
	function save( $editor )
	{
		$this->_loadEditor();

		//check if editor is already loaded
		if(is_null(($this->_editor))) {
			return;
		}

		$args[] = $editor;
		$args['event'] = 'onSave';

		$return = '';
		$results[] = $this->_editor->update($args);
		foreach ($results as $result) {
			if (trim($result)) {
				$return .= $result;
			}
		}
		return $return;
	}

	/**
	 * Get the editor contents
	 *
	 *
	 */
	function getContent( $editor )
	{
		$this->_loadEditor();

		$args['name'] = $editor;
		$args['event'] = 'onGetContent';

		$return = '';
		$results[] = $this->_editor->update($args);
		foreach ($results as $result) {
			if (trim($result)) {
				$return .= $result;
			}
		}
		return $return;
	}

	/**
	 * Set the editor contents
	 *
	 *
	 */
	function setContent( $editor, $html )
	{
		$this->_loadEditor();

		$args['name'] = $editor;
		$args['html'] = $html;
		$args['event'] = 'onSetContent';

		$return = '';
		$results[] = $this->_editor->update($args);
		foreach ($results as $result) {
			if (trim($result)) {
				$return .= $result;
			}
		}
		return $return;
	}

	/**
	 * Load the editor
	 *
	 * @access private
	 * @since 1.5
	 */
	function _loadEditor()
	{
		//check if editor is already loaded
		if(!is_null(($this->_editor))) {
			return;
		}

		jimport('joomla.filesystem.file');

		// Build the path to the needed editor plugin
		$name = JInputFilter::clean($this->_name, 'cmd');
		$path = JPATH_SITE.DS.'plugins'.DS.'editors'.DS.$name.'.php';

		if ( ! JFile::exists($path) )
		{
			$message = JText::_('Cannot load the editor');
			JError::raiseWarning( 500, $message );
			return false;
		}

		// Require plugin file
		require_once $path;

		// Build editor plugin classname
		$name = 'plgEditor'.$this->_name;
		if($this->_editor = new $name ($this))
		{
			// load plugin parameters
			$plugin =& JPluginHelper::getPlugin('editors', $this->_name);
			$params = new JParameter($plugin->params);

			// push the parameters in the plugin
			$this->_editor->set('params', $params);

			$this->initialise();
			JPluginHelper::importPlugin('editors-xtd');
		}
	}
}