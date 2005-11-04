<?php
/**
* @version $Id: joomla.xml.php 618 2005-10-24 00:21:08Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// ensure this file is being included by a parent file
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Parameters handler
* @package Joomla
*/
class mosParameters {
	/** @var object */
	var $_params 	= null;
	/** @var string The raw params string */
	var $_raw 		= null;
	/** @var string Path to the xml setup file */
	var $_path 		= null;
	/** @var string The type of setup file */
	var $_type 		= null;
	/** @var object The xml params element */
	var $_xmlElem 	= null;
/**
* Constructor
* @param string The raw parms text
* @param string Path to the xml setup file
* @var string The type of setup file
*/
	function mosParameters( $text, $path='', $type='component' ) {
		$this->_params 	= $this->parse( $text );
		$this->_raw 	= $text;
		$this->_path 	= $path;
		$this->_type 	= $type;
	}

	/**
	 * Returns the params array
	 * @return object
	 */
	function toObject() {
		return $this->_params;
	}

	/**
	 * Returns a named array of the parameters
	 * @return object
	 */
	function toArray() {
		return mosObjectToArray( $this->_params );
	}

/**
* @param string The name of the param
* @param string The value of the parameter
* @return string The set value
*/
	function set( $key, $value='' ) {
		$this->_params->$key = $value;
		return $value;
	}
/**
* Sets a default value if not alreay assigned
* @param string The name of the param
* @param string The value of the parameter
* @return string The set value
*/
	function def( $key, $value='' ) {
		return $this->set( $key, $this->get( $key, $value ) );
	}
/**
* @param string The name of the param
* @param mixed The default value if not found
* @return string
*/
	function get( $key, $default='' ) {
		if (isset( $this->_params->$key )) {
			return $this->_params->$key === '' ? $default : $this->_params->$key;
		} else {
			return $default;
		}
	}
/**
* Parse an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
* @param mixed The ini string or array of lines
* @param boolean add an associative index for each section [in brackets]
* @return object
*/
	function parse( $txt, $process_sections = false, $asArray = false ) {
		if (is_string( $txt )) {
			$lines = explode( "\n", $txt );
		} else if (is_array( $txt )) {
			$lines = $txt;
		} else {
			$lines = array();
		}
		$obj = $asArray ? array() : new stdClass();

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
			$line = trim( $line );

			if ($line == '') {
				continue;
			}
			if ($line && $line[0] == '[' && $line[strlen($line) - 1] == ']') {
				$sec_name = substr( $line, 1, strlen($line) - 2 );
				if ($process_sections) {
					if ($asArray) {
						$obj[$sec_name] = array();
					} else {
						$obj->$sec_name = new stdClass();
					}
				}
			} else {
				if ($pos = strpos( $line, '=' )) {
					$property = trim( substr( $line, 0, $pos ) );

					if (substr($property, 0, 1) == '"' && substr($property, -1) == '"') {
						$property = stripcslashes(substr($property,1,count($property) - 2));
					}
					$value = trim( substr( $line, $pos + 1 ) );
					if ($value == 'false') {
						$value = false;
					}
					if ($value == 'true') {
						$value = true;
					}
					if (substr( $value, 0, 1 ) == '"' && substr( $value, -1 ) == '"') {
						$value = stripcslashes( substr( $value, 1, count( $value ) - 2 ) );
					}

					if ($process_sections) {
						$value = str_replace( '\n', "\n", $value );
						if ($sec_name != '') {
							if ($asArray) {
								$obj[$sec_name][$property] = $value;
							} else {
								$obj->$sec_name->$property = $value;
							}
						} else {
							if ($asArray) {
								$obj[$property] = $value;
							} else {
								$obj->$property = $value;
							}
						}
					} else {
						$value = str_replace( '\n', "\n", $value );
						if ($asArray) {
							$obj[$property] = $value;
						} else {
							$obj->$property = $value;
						}
					}
				} else {
					if ($line && trim($line[0]) == ';') {
						continue;
					}
					if ($process_sections) {
						$property = '__invalid' . $unparsed++ . '__';
						if ($process_sections) {
							if ($sec_name != '') {
								if ($asArray) {
									$obj[$sec_name][$property] = trim($line);
								} else {
									$obj->$sec_name->$property = trim($line);
								}
							} else {
								if ($asArray) {
									$obj[$property] = trim($line);
								} else {
									$obj->$property = trim($line);
								}
							}
						} else {
							if ($asArray) {
								$obj[$property] = trim($line);
							} else {
								$obj->$property = trim($line);
							}
						}
					}
				}
			}
		}
		return $obj;
	}
/**
* @param string The name of the control, or the default text area if a setup file is not found
* @return string HTML
*/
	function render( $name='params' ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		if ($this->_path) {
			if (!is_object( $this->_xmlElem )) {
				require_once( $mosConfig_absolute_path . '/includes/domit/xml_domit_lite_include.php' );

				$xmlDoc = new DOMIT_Lite_Document();
				$xmlDoc->resolveErrors( true );
				if ($xmlDoc->loadXML( $this->_path, false, true )) {
					$root =& $xmlDoc->documentElement;

					$tagName = $root->getTagName();
					$isParamsFile = ($tagName == 'mosinstall' || $tagName == 'mosparams');
					if ($isParamsFile && $root->getAttribute( 'type' ) == $this->_type) {
						if ($params = &$root->getElementsByPath( 'params', 1 )) {
							$this->_xmlElem =& $params;
						}
					}
				}
			}
		}

		if (is_object( $this->_xmlElem )) {
			$html = array();
			$html[] = '<table width="100%" class="paramlist">';

			$element =& $this->_xmlElem;

			if ($description = $element->getAttribute( 'description' )) {
				// add the params description to the display
				$html[] = '<tr><td colspan="3">' . $description . '</td></tr>';
			}

			//$params = mosParseParams( $row->params );
			$this->_methods = get_class_methods( get_class( $this ) );

			foreach ($element->childNodes as $param) {
				$result = $this->renderParam( $param, $name );
				$html[] = '<tr>';

				$html[] = '<td width="40%" align="right" valign="top"><span class="editlinktip">'. $result[0] .'</span></td>';
				$html[] = '<td>'. $result[1] .'</td>';

				$html[] = '</tr>';
			}
			$html[] = '</table>';

			if (count( $element->childNodes ) < 1) {
				$html[] = "<tr><td colspan=\"2\"><i>". $_LANG->_( 'There are no Parameters for this item' ) ."</i></td></tr>";
			}
			return implode( "\n", $html );
		} else {
			return "<textarea name=\"". $name ."\" cols=\"40\" rows=\"10\" class=\"text_area\">". $this->_raw ."</textarea>";
		}
	}
/**
* @param object A param tag node
* @param string The control name
* @return array Any array of the label, the form element and the tooltip
*/
	function renderParam( &$param, $control_name='params' ) {
		global $_LANG;

		$result = array();

		$name = $param->getAttribute( 'name' );
		$label = $param->getAttribute( 'label' );

		$value = $this->get( $name, $param->getAttribute( 'default' ) );
		$description = $param->getAttribute( 'description' );

		$result[0] = $label ? $label : $name;

		if ($result[0] == '@spacer') {
			$result[0] = '';
		} else {
			$result[0] = mosToolTip( addslashes( $description ), $result[0], '', '', $result[0], '#', 0 );
		}
		$type = $param->getAttribute( 'type' );

		if (in_array( '_form_' . $type, $this->_methods )) {
			$result[1] = call_user_func( array( &$this, '_form_' . $type ), $name, $value, $param, $control_name );
		} else {
			$result[1] = $_LANG->_( 'Handler not defined for type' ) . ' = ' . $type;
		}

		if ( $description ) {
			$result[2] = mosToolTip( $description, $result[0] );
			$result[2] = '';
		} else {
			$result[2] = '';
		}

		return $result;
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_text( $name, $value, &$node, $control_name ) {
		$size = $node->getAttribute( 'size' );

		return '<input type="text" name="'. $control_name .'['. $name .']" value="'. $value .'" class="text_area" size="'. $size .'"/>';
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_list( $name, $value, &$node, $control_name ) {
		global $_LANG;

		$size = $node->getAttribute( 'size' );

		$options = array();
		foreach ($node->childNodes as $option) {
			$val = $option->getAttribute( 'value' );
			$text = $option->gettext();
			$options[] = mosHTML::makeOption( $val, $_LANG->_( $text ) );
		}

		return mosHTML::selectList( $options, ''. $control_name .'['. $name .']', 'class="inputbox"', 'value', 'text', $value );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_radio( $name, $value, &$node, $control_name ) {
		global $_LANG;

		$options = array();
		foreach ($node->childNodes as $option) {
			$val 	= $option->getAttribute( 'value' );
			$text 	= $option->gettext();
			$options[] = mosHTML::makeOption( $val, $_LANG->_( $text ) );
		}

		return mosHTML::radioList( $options, ''. $control_name .'['. $name .']', '', $value );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_mos_section( $name, $value, &$node, $control_name ) {
		global $database;
		global $_LANG;

		$query = "SELECT id, title"
		. "\n FROM #__sections"
		. "\n WHERE published = 1"
		. "\n AND scope = 'content'"
		. "\n ORDER BY title"
		;
		$database->setQuery( $query );
		$options = $database->loadObjectList();
		array_unshift( $options, mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select Content Section' ) .' -', 'id', 'title' ) );

		return mosHTML::selectList( $options, ''. $control_name .'['. $name .']', 'class="inputbox"', 'id', 'title', $value );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_mos_category( $name, $value, &$node, $control_name ) {
		global $database;
		global $_LANG;

		$scope = $node->getAttribute( 'scope' );
		if( !isset($scope) ) {
			$scope = 'content';
		}

		if( $scope== 'content' ) {
			// This might get a conflict with the dynamic translation - TODO: search for better solution
			$query 	= "SELECT c.id, CONCAT_WS( '/',s.title, c.title ) AS title"
			. "\n FROM #__categories AS c"
			. "\n LEFT JOIN #__sections AS s ON s.id=c.section"
			. "\n WHERE c.published = 1"
			. "\n AND s.scope = '$scope'"
			. "\n ORDER BY c.title"
			;
		} else {
			$query 	= "SELECT c.id, c.title"
				. "\n FROM #__categories AS c"
				. "\n WHERE c.published = 1"
				. "\n AND c.section = '$scope'"
				. "\n ORDER BY c.title"
				;
		}
		$database->setQuery( $query );
		$options = $database->loadObjectList();
		array_unshift( $options, mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select Content Category' ) .' -', 'id', 'title' ) );

		return mosHTML::selectList( $options, ''. $control_name .'['. $name .']', 'class="inputbox"', 'id', 'title', $value );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_mos_menu( $name, $value, &$node, $control_name ) {
		global $database;
		global $_LANG;

		$menuTypes = mosAdminMenus::menutypes();

		foreach($menuTypes as $menutype ) {
			$options[] = mosHTML::makeOption( $menutype, $menutype );
		}
		array_unshift( $options, mosHTML::makeOption( '', '- '. $_LANG->_( 'Select Menu' ) .' -' ) );

		return mosHTML::selectList( $options, ''. $control_name .'['. $name .']', 'class="inputbox"', 'value', 'text', $value );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_filelist( $name, $value, &$node, $control_name ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		// path to images directory
		$path 	= $mosConfig_absolute_path . $node->getAttribute( 'directory' );
		$filter = $node->getAttribute( 'filter' );
		$files 	= mosReadDirectory( $path, $filter );

		$options = array();
		foreach ($files as $file) {
			$options[] = mosHTML::makeOption( $file, $file );
		}
		if ( !$node->getAttribute( 'hide_none' ) ) {
			array_unshift( $options, mosHTML::makeOption( '-1', '- '. $_LANG->_( 'Do not use an image' ) .' -' ) );
		}
		if ( !$node->getAttribute( 'hide_default' ) ) {
			array_unshift( $options, mosHTML::makeOption( '', '- '. $_LANG->_( 'Use Default image' ) .' -' ) );
		}

		return mosHTML::selectList( $options, ''. $control_name .'['. $name .']', 'class="inputbox"', 'value', 'text', $value, "param$name" );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_imagelist( $name, $value, &$node, $control_name ) {
		$node->setAttribute( 'filter', '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$' );
		return $this->_form_filelist( $name, $value, $node, $control_name );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_textarea( $name, $value, &$node, $control_name ) {
 		$rows 	= $node->getAttribute( 'rows' );
 		$cols 	= $node->getAttribute( 'cols' );
 		// convert <br /> tags so they are not visible when editing
 		$value 	= str_replace( '<br />', "\n", $value );

 		return '<textarea name="params['. $name .']" cols="'. $cols .'" rows="'. $rows .'" class="text_area">'. $value .'</textarea>';
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_spacer( $name, $value, &$node, $control_name ) {
		if ( $value ) {
			return $value;
		} else {
			return '<hr />';
		}
	}

	/**
	* special handling for textarea param
	*/
	function textareaHandling( &$txt ) {
		$total = count( $txt );
		for( $i=0; $i < $total; $i++ ) {
			if ( strstr( $txt[$i], "\n" ) ) {
				$txt[$i] = str_replace( "\n", '<br />', $txt[$i] );
			}
		}
		$txt = implode( "\n", $txt );

		return $txt;
	}
	
	/**
	* Returns the list of existing frontend languages (independed if used or not!)
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_language_list( $name, $value, &$node, $control_name) {
		global $_LANG;
		
		$languages = JLanguageHelper::buildLanguageList( 0, $value );
		array_unshift( $languages, mosHTML::makeOption( '',  '- '. $_LANG->_( 'Select Language' ) .' -' ) );

		return mosHTML::selectList( $languages, ''. $control_name .'['. $name .']', 'class="inputbox"', 'value', 'text', $value, "param$name" );
	}
	/**
	* Returns the list of existing admin languages
	* @param string The name of the form element
	* @param string The value of the element
	* @param object The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_admin_language_list( $name, $value, &$node, $control_name) {
		global $_LANG;

		$languages = JLanguageHelper::buildLanguageList( 1, $value );
		array_unshift( $languages, mosHTML::makeOption( '0',  '- '. $_LANG->_( 'Select Language' ) .' -' ) );

		return mosHTML::selectList( $languages, ''. $control_name .'['. $name .']', 'class="inputbox"', 'value', 'text', $value, "param$name" );
	}
}

/**
* @param string
* @return string
*/
function mosParseParams( $txt ) {
	return mosParameters::parse( $txt );
}

function walkNodesAndReturnMosNodeList(&$nodeList, &$contextNode) {
	//STEP 1: DO SOME ERROR CHECKING (this can be omitted if you want to optimize, but isn't as safe)
	//ensure that node is not null
	if (!isset( $contextNode )) {
		return;
	}

	//ensure that node is a DOMIT element
	if (strtolower( get_class( $contextNode ) ) != 'domit_element') {
		//if contextNode is a DOMIT Document, grab the documentElement
		if (strtolower( get_class( $contextNode ) ) == 'domit_document') {
			$contextNode =& $contextNode->documentElement;
			if (!isset( $contextNode )) {
				return;
			}
		} else {
			return;
		}
	}

	//STEP 2: EVALUATE THE CONTEXT NODE BASED ON SOME CRITERIA
	//determine whether the context node should be added to the master nodeList
	if ((strlen( $contextNode->nodeName ) > 3) && (substr( $contextNode->nodeName, 0, 4 ) == "mos:")) {
		$nodeList->appendNode($contextNode);
	}

	//STEP 3: ITERATE THROUGH THE CONTEXT NODE CHILDREN AND
	//RECURSIVELY CALL THIS FUNCTION WITH THE CHILD AS THE CONTEXT NODE
	$total = $contextNode->childCount;

	for ($i = 0; $i < $total; $i++) {
		walkNodesAndReturnMosNodeList($nodeList, $contextNode->childNodes[$i]);
	}
} //walkNodesAndReturnMosNodeList

/*
	You'd call the function like this:

	$myNodeList = new DOMIT_NodeList();
	walkNodesAndReturnMosNodeList($myNodeList, $someXMLDoc);
*/

class mosEmpty {
	function def( $key, $value='' ) {
		return 1;
	}
	function get( $key, $default='' ) {
		return 1;
	}
}
?>
