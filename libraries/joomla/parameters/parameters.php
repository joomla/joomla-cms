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

jimport( 'joomla.common.base.object' );

/**
 * Parameters handler
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Parameters
 * @since 1.0
 */
class JParameters extends JObject
{
	/** 
	 * Description
	 * 
	 * @access	private
	 * @var object 
	 */
	var $_params = null;
	
	/** 
	 * The raw params string
	 * 
	 * @access	private
	 * @var string 
	 */
	var $_raw = null;
	
	/** 
	 * Path to the xml setup file
	 * 
	 * @access	private
	 * @var string 
	 */
	var $_path = null;
	
	/** 
	 * The type of setup file
	 * 
	 * @access	private
	 * @var string  
	 */
	var $_type = null;
	
	/** 
	 * The xml params element 
	 * 
	 * @access	private
	 * @var object 
	 */
	var $_xmlElem = null;
	
	/**
	* loaded parameter types
	*
	* @access	private
	* @var		array
	*/
	var $_parameterTypes = array();
	
	/**
	* directories, where parameter types can be stored
	* 
	* @access	private
	* @var		array
	*/
	var $_parameterDirs  = array();

	/**
	 * Constructor
	 * 
	 * @access protected
	 * @param string The raw parms text
	 * @param string Path to the xml setup file
	 * @var string The type of setup file
	 */
	function __construct($text, $path = '', $type = 'component') 
	{
		if( !defined( 'JPARAMETER_INCLUDE_PATH' ) ) {
			define( 'JPARAMETER_INCLUDE_PATH', dirname( __FILE__ ) . '/types' );
		}
		
		$this->_params = $this->parse($text);
		$this->_raw    = $text;
		$this->_path   = $path;
		$this->_type   = $type;
	}

	/**
	 * Returns the params array
	 * 
	 * @return object
	 */
	function toObject() {
		return $this->_params;
	}

	/**
	 * Returns a named array of the parameters
	 * 
	 * @return object
	 */
	function toArray() {
		return mosObjectToArray($this->_params);
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
		$this->_params-> $key = $value;
		return $value;
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
	 * Get a value
	 * 
	 * @access public
	 * @param string The name of the param
	 * @param mixed The default value if not found
	 * @return string
	 */
	function get($key, $default = '') 
	{
		if (isset ($this->_params-> $key)) {
			return $this->_params-> $key === '' ? $default : $this->_params-> $key;
		} else {
			return $default;
		}
	}

	/**
	 * Parse an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 * 
	 * @access public
	 * @param mixed The ini string or array of lines
	 * @param boolean add an associative index for each section [in brackets]
	 * @return object
	 */
	function parse($txt, $process_sections = false, $asArray = false) 
	{
		if (is_string($txt)) {
			$lines = explode("\n", $txt);
		} else
			if (is_array($txt)) {
				$lines = $txt;
			} else {
				$lines = array ();
			}
		$obj = $asArray ? array () : new stdClass();

		$sec_name = '';
		$unparsed = 0;
		if (!$lines) {
			return $obj;
		}
		foreach ($lines as $line) {
			// ignore comments
			if ($line && $line[0] == ';') {
				continue;
			}
			$line = trim($line);

			if ($line == '') {
				continue;
			}
			if ($line && $line[0] == '[' && $line[JString::strlen($line) - 1] == ']') {
				$sec_name = JString::substr($line, 1, JString::strlen($line) - 2);
				if ($process_sections) {
					if ($asArray) {
						$obj[$sec_name] = array ();
					} else {
						$obj-> $sec_name = new stdClass();
					}
				}
			} else {
				if ($pos = JString::strpos($line, '=')) {
					$property = trim(JString::substr($line, 0, $pos));

					// property is assumed to be ascii
					if (substr($property, 0, 1) == '"' && substr($property, -1) == '"') {
						$property = stripcslashes(substr($property, 1, count($property) - 2));
					}
					$value = trim(JString::substr($line, $pos +1));
					if ($value == 'false') {
						$value = false;
					}
					if ($value == 'true') {
						$value = true;
					}
					if (JString::substr($value, 0, 1) == '"' && JString::substr($value, -1) == '"') {
						$value = stripcslashes(JString::substr($value, 1, JString::strlen($value) - 2));
					}

					if ($process_sections) {
						$value = str_replace('\n', "\n", $value);
						if ($sec_name != '') {
							if ($asArray) {
								$obj[$sec_name][$property] = $value;
							} else {
								$obj-> $sec_name-> $property = $value;
							}
						} else {
							if ($asArray) {
								$obj[$property] = $value;
							} else {
								$obj-> $property = $value;
							}
						}
					} else {
						$value = str_replace('\n', "\n", $value);
						if ($asArray) {
							$obj[$property] = $value;
						} else {
							$obj-> $property = $value;
						}
					}
				} else {
					if ($line && trim($line[0]) == ';') {
						continue;
					}
					if ($process_sections) {
						$property = '__invalid'.$unparsed ++.'__';
						if ($process_sections) {
							if ($sec_name != '') {
								if ($asArray) {
									$obj[$sec_name][$property] = trim($line);
								} else {
									$obj-> $sec_name-> $property = trim($line);
								}
							} else {
								if ($asArray) {
									$obj[$property] = trim($line);
								} else {
									$obj-> $property = trim($line);
								}
							}
						} else {
							if ($asArray) {
								$obj[$property] = trim($line);
							} else {
								$obj-> $property = trim($line);
							}
						}
					}
				}
			}
		}
		return $obj;
	}

	/**
	 * render all the parameters
	 * 
	 * @param string The name of the control, or the default text area if a setup file is not found
	 * @return string HTML
	 */
	function render($name = 'params') 
	{
		if ($this->_path) {
			if (!is_object($this->_xmlElem)) {

				$xmlDoc = & JFactory::getXMLParser();
				$xmlDoc->resolveErrors(true);
				if ($xmlDoc->loadXML($this->_path, false, true)) {
					$root = & $xmlDoc->documentElement;

					$tagName = $root->getTagName();
					$isParamsFile = ($tagName == 'install' || $tagName == 'params' || $tagName == 'mosinstall' || $tagName == 'mosparams');
					if ($isParamsFile && $root->getAttribute('type') == $this->_type) {
						if ($params = & $root->getElementsByPath('params', 1)) {
							$this->_xmlElem = & $params;
						}
					}
				}
			}
		}

		if (is_object($this->_xmlElem)) {
			$html = array ();
			$html[] = '<table width="100%" class="paramlist">';

			$element = & $this->_xmlElem;

			if ($description = $element->getAttribute('description')) {
				// add the params description to the display
				$html[] = '<tr><td colspan="3">'.$description.'</td></tr>';
			}

			$this->_methods = get_class_methods(get_class($this));

			foreach ($element->childNodes as $param) 
			{
				$result = $this->renderParam($param, $name);
				$html[] = '<tr>';

				$html[] = '<td width="40%" align="right" valign="top"><span class="editlinktip">'.$result[0].'</span></td>';
				$html[] = '<td>'.$result[1].'</td>';

				$html[] = '</tr>';
			}
			$html[] = '</table>';

			if (count($element->childNodes) < 1) {
				$html[] = "<tr><td colspan=\"2\"><i>".JText::_('There are no Parameters for this item')."</i></td></tr>";
			}
			return implode("\n", $html);
		} else {
			return "<textarea name=\"".$name."\" cols=\"40\" rows=\"10\" class=\"text_area\">".$this->_raw."</textarea>";
		}
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
		
		$parameter =& $this->loadParameter($type);
		
		/**
		 * error happened
		 */
		if ($parameter === false) {
			
			$result = array();
			$result[0] = $node->getAttribute('name');
			$result[1] = JText::_('Handler not defined for type').' = '.$type;
			return $result;
		}
		
		return $parameter->render($node, $control_name);
	}
	
	/**
	* loads a parameter type
	*
	* @access	public
	* @param	string	parameterType
	* @return	object
	* @since 1.1
	*/
	function &loadParameter( $type, $new = false )
	{	
		$signature = md5( $type  );

		if( isset( $this->_paramatersTypes[$signature] ) && $new === false ) {
			return	$this->_parameterTypes[$signature];
		}

		if( !class_exists( 'JParameter' ) )
		{
			if( !jimport('joomla.parameters.parameter') ) {
				//return	JError::raiseError( 'SOME_ERROR_CODE', 'Could not load parameter base class.' );
				return false;
			}
		}

		$parameterClass	=	'JParameter_' . $type;
		if( !class_exists( $parameterClass ) )
		{
			if( isset( $this->_parameterDirs ) )
				$dirs = $this->_parameterDirs;
			else
				$dirs = array();
			
			array_push( $dirs, $this->getIncludePath());
				
			$found = false;
			foreach( $dirs as $dir )
			{
				$parameterFile	= sprintf( "%s/%s.php", $dir, str_replace( '_', '/', $type ) );
				
				if (@include_once $parameterFile) {
					$found = true;
					break;
				}
			}

			if( !$found ) {
				//return	JError::raiseError( 'SOME_ERROR_CODE', "Could not load module $parameterClass ($parameterFile)." );
				return false;
			}
		}

		if( !class_exists( $parameterClass ) )
		{
			//return	JError::raiseError( 'SOME_ERROR_CODE', "Module file $parameterFile does not contain class $paramaterClass." );
			return false;
		}

		$this->_parameterTypes[$signature]	=& new $parameterClass($this);
		
		return $this->_parameterTypes[$signature];
	}
	
	/**
	* add a directory where JParameters should search for parameter types
	*
	* You may either pass a string or an array of directories.
	*
	* JParameter will be searching for a parameter type in the same
	* order you added them. If the parameter type cannot be found in
	* the custom folders, it will look in
	* JParameters/types.
	*
	* @access	public
	* @param	string|array	directory or directories to search.
	* @since 1.1
	*/
	function addParameterDir( $dir )
	{
		if( is_array( $dir ) )
			$this->_parametersDirs = array_merge( $this->_parameterDirs, $dir );
		else
			array_push( $this->_parameterDirs, $dir );
	}
	
   /**
	* get the include path
	*
	* @access	public
	* @return   string
	* @since 1.1
	*/
	function getIncludePath()
	{
		return	JPARAMETER_INCLUDE_PATH;
	}
}
?>
