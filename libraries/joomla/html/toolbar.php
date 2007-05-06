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

/**
 * ToolBar handler
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JToolBar extends JObject
{
	/**
	 * Toolbar name
	 *
	 * @access	private
	 * @var		string
	 */
	var $_name = array ();

	/**
	 * Toolbar array
	 *
	 * @access	private
	 * @var		array
	 */
	var $_bar = array ();

	/**
	 * Loaded buttons
	 *
	 * @access	private
	 * @var		array
	 */
	var $_buttons = array ();

	/**
	 * Directories, where button types can be stored
	 *
	 * @access	private
	 * @var		array
	 */
	var $_buttonPath = array ();

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param string The toolbar name
	 * @var string The type of setup file
	 */
	function __construct($name = 'toolbar')
	{
		$this->_name = $name;

		jimport('joomla.html.toolbar.button');

		// Set base path to find buttons
		$this->_buttonPath[] = dirname(__FILE__).DS.'toolbar'.DS.'button';

	}

	/**
	 * Returns a reference to a global JToolBar object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $toolbar = & JToolBar::getInstance( $name );</pre>
	 *
	 * @access	public
	 * @param	string		$name  The name of the toolbar.
	 * @return	JToolBar	The JToolBar object.
	 */
	function & getInstance($name = 'toolbar')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$name])) {
			$instances[$name] = new JToolBar($name);
		}

		return $instances[$name];
	}

	/**
	 * Set a value
	 *
	 * @access public
	 * @param string The name of the param
	 * @param string The value of the parameter
	 * @return string The set value
	 */
	function appendButton()
	{
		// Push button onto the end of the toolbar array
		$btn = func_get_args();
		array_push($this->_bar, $btn);
		return true;
	}

	/**
	 * Get a value
	 *
	 * @access public
	 * @param string The name of the param
	 * @param mixed The default value if not found
	 * @return string
	 */
	function prependButton()
	{
		// Insert button into the front of the toolbar array
		$btn = func_get_args();
		array_unshift($this->_bar, $btn);
		return true;
	}

	/**
	 * Render
	 *
	 * @access public
	 * @param string The name of the control, or the default text area if a setup file is not found
	 * @return string HTML
	 */
	function render()
	{
		$html = array ();

		// Start toolbar div
		$html[] = '<div class="toolbar" id="'.$this->_name.'">';
		$html[] = '<table class="toolbar"><tr>';

		// Render each button in the toolbar
		foreach ($this->_bar as $button)
		{
			$html[] = $this->renderButton($button);
		}

		// End toolbar div
		$html[] = '</tr></table>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	/**
	 * Render a parameter type
	 *
	 * @param object A param tag node
	 * @param string The control name
	 * @return array Any array of the label, the form element and the tooltip
	 */
	function renderButton( &$node )
	{
		// Get the button type
		$type = $node[0];

		$button = & $this->loadButtonType($type);

		/**
		 * Error Occurred
		 */
		if ($button === false)
		{
			return JText::_('Button not defined for type').' = '.$type;
		}
		return $button->render($node);
	}

	/**
	 * Loads a button type
	 *
	 * @access	public
	 * @param	string	buttonType
	 * @return	object
	 * @since 1.5
	 */
	function & loadButtonType($type, $new = false)
	{
		$signature = md5($type);
		if (isset ($this->_buttons[$signature]) && $new === false) {
			return $this->_buttons[$signature];
		}

		if (!class_exists('JButton'))
		{
			JError::raiseWarning( 'SOME_ERROR_CODE', 'Could not load button base class.' );
			return false;
		}

		$buttonClass = 'JButton'.$type;
		if (!class_exists($buttonClass))
		{
			if (isset ($this->_buttonPath))
				$dirs = $this->_buttonPath;
			else
				$dirs = array ();

			$found = false;
			foreach ($dirs as $dir)
			{
				$buttonFile = sprintf('%s'.DS.'%s.php', $dir, str_replace('_', DS, strtolower($type)));
				if (@ include_once $buttonFile)
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				JError::raiseWarning( 'SOME_ERROR_CODE', "Could not load module $buttonClass ($buttonFile)." );
				return false;
			}
		}

		if (!class_exists($buttonClass))
		{
			//return	JError::raiseError( 'SOME_ERROR_CODE', "Module file $buttonFile does not contain class $buttonClass." );
			return false;
		}
		$this->_buttons[$signature] = new $buttonClass($this);

		return $this->_buttons[$signature];
	}

	/**
	 * Add a directory where JToolBar should search for button types
	 *
	 * You may either pass a string or an array of directories.
	 *
	 * {@link JParameter} will be searching for an element type in the same order you
	 * added them. If the parameter type cannot be found in the custom folders,
	 * it will look in JParameter/types.
	 *
	 * @access	public
	 * @param	string|array	directory or directories to search.
	 * @since	1.5
	 */
	function addButtonPath($path)
	{
		if (is_array($path)) {
			$this->_buttonPath = array_merge($this->_buttonPath, $path);
		} else {
			array_push($this->_buttonPath, $path);
		}
	}
}