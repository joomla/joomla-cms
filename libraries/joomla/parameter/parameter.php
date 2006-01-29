<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.registry.registry' );

/**
 * Parameter handler
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since 1.0
 */
class JParameter extends JRegistry
{
	/** 
	 * The raw params string
	 * 
	 * @access	private
	 * @var string 
	 */
	var $_raw = null;
	
	/** 
	 * The xml params element 
	 * 
	 * @access	private
	 * @var object 
	 */
	var $_xml = null;
	
	/**
	* loaded elements
	*
	* @access	private
	* @var		array
	*/
	var $_elements = array();
	
	/**
	* directories, where element types can be stored
	* 
	* @access	private
	* @var		array
	*/
	var $_elementDirs  = array();

	/**
	 * Constructor
	 * 
	 * @access protected
	 * @param string The raw parms text
	 * @param string Path to the xml setup file
	 * @var string The type of setup file
	 */
	function __construct($data, $path = '') 
	{
		if( !defined( 'JPARAMETER_INCLUDE_PATH' ) ) {
			define( 'JPARAMETER_INCLUDE_PATH', dirname( __FILE__ ) . '/element' );
		}
		
		parent::__construct('parameter');
			
		$this->loadINI($data);
		$this->loadSetupFile($path);
		
		$this->_raw = $data;
	}

	/**
	 * Set a value
	 * 
	 * @access public
	 * @param string The name of the param
	 * @param string The value of the parameter
	 * @return string The set value
	 */
	function set($key, $value = '') {
		return $this->setValue('parameter.'.$key, $value);
	}
	
	/**
	 * Get a value
	 * 
	 * @access public
	 * @param string The name of the param
	 * @param mixed The default value if not found
	 * @return string
	 */
	function get($key, $default = '') 
	{
		$value = $this->getValue('parameter.'.$key);
		$result = isset($value) ? $value : $default; 
		return $result;
	}

	/**
	 * Sets a default value if not alreay assigned
	 * 
	 * @access public
	 * @param string The name of the param
	 * @param string The value of the parameter
	 * @return string The set value
	 */
	function def($key, $value = '') {
		return $this->set($key, $this->get($key, $value));
	}

	/**
	 * Render
	 * 
	 * @access public
	 * @param string The name of the control, or the default text area if a setup file is not found
	 * @return string HTML
	 */
	function render($name = 'params') 
	{
		if (!is_object($this->_xml)) {
			return false;
		}
		
		$element = & $this->_xml;
		
		$html = array ();
		$html[] = '<table width="100%" class="paramlist">';

		if ($description = $element->getAttribute('description')) {
			// add the params description to the display
			$html[] = '<tr><td colspan="3">'.$description.'</td></tr>';
		}

		foreach ($element->childNodes as $param) 
		{
			$result = $this->renderParam($param, $name);
			
			$html[] = '<tr>';

			$html[] = '<td width="40%" align="right" valign="top"><span class="editlinktip">'.$result[0].'</span></td>';
			$html[] = '<td>'.$result[1].'</td>';

			$html[] = '</tr>';
		}

		if (count($element->childNodes) < 1) {
			$html[] = "<tr><td colspan=\"2\"><i>".JText::_('There are no Parameters for this item')."</i></td></tr>";
		}
		
		$html[] = '</table>';
		
		return implode("\n", $html);
	}

	/**
	 * render a parameter type
	 * 
	 * @param object A param tag node
	 * @param string The control name
	 * @return array Any array of the label, the form element and the tooltip
	 */
	function renderParam(&$node, $control_name = 'params') 
	{
		//get the type of the parameter
		$type = $node->getAttribute('type');
		
		//remove any occurance of a mos_ prefix
		$type = str_replace('mos_', '', $type);
		
		$element =& $this->loadElement($type);
		
		/**
		 * error happened
		 */
		if ($element === false) {
			
			$result = array();
			$result[0] = $node->getAttribute('name');
			$result[1] = JText::_('Element not defined for type').' = '.$type;
			return $result;
		}
		
		return $element->render($node, $control_name);
	}
	
	/**
	* Loads an xml setup file and parses it
	*
	* @access	public
	* @param	string	path to xml setup file
	* @return	object
	* @since 1.1
	*/
	function loadSetupFile($path) 
	{
		$xmlDoc = & JFactory::getXMLParser();
		$xmlDoc->resolveErrors(true);
		
		$result = false;
		if ($xmlDoc->loadXML($path, false, true)) {
			$root = & $xmlDoc->documentElement;

			if ($params = & $root->getElementsByPath('params', 1)) {
				$this->_xml = & $params;
				$result = true;
			}
		}
		
		return $result;
	}
	
	/**
	* Loads a element type
	*
	* @access	public
	* @param	string	elementType
	* @return	object
	* @since 1.1
	*/
	function &loadElement( $type, $new = false )
	{	
		$signature = md5( $type  );

		if( isset( $this->_elements[$signature] ) && $new === false ) {
			return	$this->_elements[$signature];
		}

		if( !class_exists( 'JElement' ) )
		{
			if( !jimport('joomla.parameter.element') ) {
				//return	JError::raiseError( 'SOME_ERROR_CODE', 'Could not load parameter base class.' );
				return false;
			}
		}

		$elementClass	=	'JElement_' . $type;
		if( !class_exists( $elementClass ) )
		{
			if( isset( $this->_elementDirs ) )
				$dirs = $this->_elementDirs;
			else
				$dirs = array();
			
			array_push( $dirs, $this->getIncludePath());
				
			$found = false;
			foreach( $dirs as $dir )
			{
				$elementFile	= sprintf( "%s/%s.php", $dir, str_replace( '_', '/', $type ) );
				
				if (@include_once $elementFile) {
					$found = true;
					break;
				}
			}

			if( !$found ) {
				//return	JError::raiseError( 'SOME_ERROR_CODE', "Could not load module $parameterClass ($parameterFile)." );
				return false;
			}
		}

		if( !class_exists( $elementClass ) )
		{
			//return	JError::raiseError( 'SOME_ERROR_CODE', "Module file $parameterFile does not contain class $paramaterClass." );
			return false;
		}

		$this->_elementTypes[$signature]	=& new $elementClass($this);
		
		return $this->_elementTypes[$signature];
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
	* @since 1.1
	*/
	function addParameterDir( $dir )
	{
		if( is_array( $dir ) )
			$this->_elementDirs = array_merge( $this->_elementDirs, $dir );
		else
			array_push( $this->_parameterDirs, $dir );
	}
	
   /**
	* Get the include path
	*
	* @access	public
	* @return   string
	* @since 1.1
	*/
	function getIncludePath() {
		return	JPARAMETER_INCLUDE_PATH;
	}
}
?>