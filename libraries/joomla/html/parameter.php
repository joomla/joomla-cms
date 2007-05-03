<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
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

jimport( 'joomla.registry.registry' );

/**
 * Parameter handler
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */
class JParameter extends JRegistry
{
	/**
	 * The raw params string
	 *
	 * @access	private
	 * @var		string
	 * @since	1.5
	 */
	var $_raw = null;

	/**
	 * The xml params element
	 *
	 * @access	private
	 * @var		object
	 * @since	1.5
	 */
	var $_xml = null;

	/**
	* loaded elements
	*
	* @access	private
	* @var		array
	* @since	1.5
	*/
	var $_elements = array();

	/**
	* directories, where element types can be stored
	*
	* @access	private
	* @var		array
	* @since	1.5
	*/
	var $_elementDirs = array();

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string The raw parms text
	 * @param	string Path to the xml setup file
	 * @since	1.5
	 */
	function __construct($data, $path = '')
	{
		if( !defined( 'JPARAMETER_INCLUDE_PATH' ) ) {
			define( 'JPARAMETER_INCLUDE_PATH', dirname( __FILE__ ).DS.'parameter'.DS.'element' );
		}

		parent::__construct('_default');

		if (trim( $data )) {
			$this->loadINI($data);
		}

		if ($path) {
			$this->loadSetupFile($path);
		}

		$this->_raw = $data;
	}

	/**
	 * Set a value
	 *
	 * @access	public
	 * @param	string The name of the param
	 * @param	string The value of the parameter
	 * @return	string The previous value
	 * @since	1.5
	 */
	function set($key, $value = '', $group = '_default')
	{
		$oldValue	= $this->getValue($group.'.'.$key);
		$this->setValue($group.'.'.$key, (string) $value);
		return $oldValue;
	}

	/**
	 * Get a value
	 *
	 * @access	public
	 * @param	string The name of the param
	 * @param	mixed The default value if not found
	 * @return	string
	 * @since	1.5
	 */
	function get($key, $default = '', $group = '_default')
	{
		$value = $this->getValue($group.'.'.$key);
		$result = (empty($value) && ($value !== 0) && ($value !== '0')) ? $default : $value;
		return $result;
	}

	/**
	 * Sets a default value if not alreay assigned
	 *
	 * @access	public
	 * @param	string The name of the param
	 * @param	string The value of the parameter
	 * @param	string The parameter group to modify
	 * @return	string The set value
	 * @since	1.5
	 */
	function def($key, $value = '', $group = '_default') {
		return $this->set($key, $this->get($key, (string) $value, $group));
	}


	/**
	 * Sets the XML object from custom xml files
	 *
	 * @access	public
	 * @param	object An XML object
	 * @since	1.5
	 */
	function setXML( &$xml )
	{
		if (is_object( $xml ))
		{
			if ($group = $xml->attributes( 'group' )) {
				$this->_xml[$group] = $xml;
			} else {
				$this->_xml['_default'] = $xml;
			}
			if ($dir = $xml->attributes( 'addparameterdir' )) {
				$this->addParameterDir( JPATH_ROOT . str_replace('/', DS, $dir) );
			}
		}
	}

	/**
	 * Bind data to the parameter
	 *
	 * @param	mixed $data Array or Object
	 * @return	boolean True if the data was successfully bound
	 * @access	public
	 * @since	1.5
	 */
	function bind($data, $group = '_default')
	{
		if ( is_array($data) ) {
			return $this->loadArray($data, $group);
		} elseif ( is_object($data) ) {
			return $this->loadObject($data, $group);
		} else {
			return $this->loadINI($data, $group);
		}
	}

	/**
	 * Render
	 *
	 * @access	public
	 * @param	string The name of the control, or the default text area if a setup file is not found
	 * @return	string HTML
	 * @since	1.5
	 */
	function render($name = 'params', $group = '_default')
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}

		$params = $this->getParams($name, $group);

		$html = array ();
		$html[] = '<table width="100%" class="paramlist" cellspacing="1">';

		if ($description = $this->_xml[$group]->attributes('description')) {
			// add the params description to the display
			$desc	= JText::_($description);
			$html[]	= '<tr><td class="paramlist_description" colspan="2">'.$desc.'</td></tr>';
		}

		foreach ($params as $param)
		{
			$html[] = '<tr>';

			$html[] = '<td width="40%" class="paramlist_key"><span class="editlinktip">'.$param[0].'</span></td>';
			$html[] = '<td class="paramlist_value">'.$param[1].'</td>';

			$html[] = '</tr>';
		}

		if (count($params) < 1) {
			$html[] = "<tr><td colspan=\"2\"><i>".JText::_('There are no Parameters for this item')."</i></td></tr>";
		}

		$html[] = '</table>';

		return implode("\n", $html);
	}

	/**
	 * Render all parameters to an array
	 *
	 * @access	public
	 * @param	string The name of the control, or the default text area if a setup file is not found
	 * @return	array of all parameters, each as array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	function renderToArray($name = 'params', $group = '_default')
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}
		$results = array();
		foreach ($this->_xml[$group]->children() as $param)  {
			$result = $this->getParam($param, $name);
			$results[$result[5]] = $result;
		}
		return $results;
	}

	/**
	 * Return number of params to render
	 *
	 * @access	public
	 * @return	mixed	Boolean falst if no params exist or integer number of params that exist
	 * @since	1.5
	 */
	function getNumParams($group = '_default')
	{
		if (!isset($this->_xml[$group]) || !count($this->_xml[$group]->children())) {
			return false;
		} else {
			return count($this->_xml[$group]->children());
		}
	}

	/**
	 * Get the number of params in each group
	 *
	 * @access	public
	 * @return	array of all group names as key and param count as value
	 * @since	1.5
	 */
	function getGroups()
	{
		if (!is_array($this->_xml)) {
			return false;
		}
		$results = array();
		foreach ($this->_xml as $name => $group)  {
			$results[$name] = $this->getNumParams($name);
		}
		return $results;
	}

	/**
	 * Render all parameters
	 *
	 * @access	public
	 * @param	string The name of the control, or the default text area if a setup file is not found
	 * @return	array of all parameters, each as array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	function getParams($name = 'params', $group = '_default')
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}
		$results = array();
		foreach ($this->_xml[$group]->children() as $param)  {
			$results[] = $this->getParam($param, $name, $group);
		}
		return $results;
	}

	/**
	 * Render a parameter type
	 *
	 * @param	object A param tag node
	 * @param	string The control name
	 * @return	array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	function getParam(&$node, $control_name = 'params', $group = '_default')
	{
		//get the type of the parameter
		$type = $node->attributes('type');

		//remove any occurance of a mos_ prefix
		$type = str_replace('mos_', '', $type);

		$element =& $this->loadElement($type);

		/**
		 * error happened
		 */
		if ($element === false)
		{
			$result = array();
			$result[0] = $node->attributes('name');
			$result[1] = JText::_('Element not defined for type').' = '.$type;
			$result[5] = $result[0];
			return $result;
		}

		//get value
		$value = $this->get($node->attributes('name'), $node->attributes('default'));

		return $element->render($node, $value, $control_name);
	}

	/**
	* Loads an xml setup file and parses it
	*
	* @access	public
	* @param	string	path to xml setup file
	* @return	object
	* @since	1.5
	*/
	function loadSetupFile($path)
	{
		$result = false;

		if ($path)
		{
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path))
			{
				if ($params = & $xml->document->params) {
					foreach ($params as $param)
					{
						$this->setXML( $param );
						$result = true;
					}
				}
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	/**
	* Loads a element type
	*
	* @access	public
	* @param	string	elementType
	* @return	object
	* @since	1.5
	*/
	function &loadElement( $type, $new = false )
	{
		$false = false;
		$signature = md5( $type  );

		if( (isset( $this->_elements[$signature] ) && !is_a($this->_elements[$signature], '__PHP_Incomplete_Class'))  && $new === false ) {
			return	$this->_elements[$signature];
		}

		if( !class_exists( 'JElement' ) ) {
			jimport('joomla.html.parameter.element');
		}

		$elementClass	=	'JElement'.$type;
		if( !class_exists( $elementClass ) )
		{
			if( isset( $this->_elementDirs ) ) {
				$dirs = $this->_elementDirs;
			} else {
				$dirs = array();
			}

			array_push( $dirs, $this->getIncludePath());

			$found = false;
			foreach( $dirs as $dir )
			{
				$elementFile	= sprintf( '%s'.DS.'%s.php', $dir, str_replace( '_', DS, $type ) );

				if (@include_once $elementFile)
				{
					$found = true;
					break;
				}
			}

			if( !$found ) {
				return $false;
			}
		}

		if( !class_exists( $elementClass ) ) {
			return $false;
		}

		$this->_elements[$signature] = new $elementClass($this);

		return $this->_elements[$signature];
	}

	/**
	* Add a directory where JParameter should search for element types
	*
	* You may either pass a string or an array of directories.
	*
	* JParameter will be searching for a element type in the same
	* order you added them. If the parameter type cannot be found in
	* the custom folders, it will look in
	* JParameter/types.
	*
	* @access	public
	* @param	string|array	directory or directories to search.
	* @since	1.5
	*/
	function addParameterDir( $dir )
	{
		if( is_array( $dir ) ) {
			$this->_elementDirs = array_merge( $this->_elementDirs, $dir );
		} else {
			array_push( $this->_elementDirs, $dir );
		}
	}

   /**
	* Get the include path
	*
	* @access	public
	* @return	 string
	* @since	1.5
	*/
	function getIncludePath() {
		return	JPARAMETER_INCLUDE_PATH;
	}
}