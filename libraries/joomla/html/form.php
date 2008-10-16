<?php
/**
 * @version		$Id: form.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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

//Register the element class with the loader
JLoader::register('JElement', dirname(__FILE__).DS.'parameter'.DS.'element.php');
JLoader::register('JSimpleXMLElement', JPATH_BASE.DS.'libraries'.DS.'joomla'.DS.'utilities'.DS.'simplexml.php');

/**
 * Form handler
 *
 * @author 		Hannes Papenberg <hannes.papenberg@community.joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JForm extends JRegistry
{
	/**
	 * The raw key-value string
	 *
	 * @access	private
	 * @var	string
	 * @since	1.6
	 */
	protected $_raw = null;

	/**
	 * The xml elements
	 *
	 * @access	private
	 * @var	object
	 * @since	1.6
	 */
	protected $_xml = null;

	/**
	 * Additional Attributes to the group of XML Data
	 *
	 * @access 	private
	 * @var	array
	 * @since	1.6
	 */
	protected $_xmlAttributes = null;

	/**
	* loaded elements
	*
	* @access	private
	* @var	array
	* @since	1.6
	*/
	protected $_elements = array();

	/**
	* directories, where element types can be stored
	*
	* @access	private
	* @var	array
	* @since	1.6
	*/
	protected $_elementPath = array();

	/**
	* XML tag-name for the elements
	*
	* @access	private
	* @var	array
	* @since	1.6
	*/
	protected $_elementTagName = '';

	/**
	 * The HTML elements to render
	 *
	 * @access	private
	 * @var		object
	 * @since	1.6
	 */
	protected $_html = array();

	protected $_hiddenElements = array();

	/**
	 * The conditional informations for the params elements
	 *
	 * @access	private
	 * @var		object
	 * @since	1.5
	 */
	protected $_cond = array();

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string The raw parms text
	 * @param	string Path to the xml setup file or XML data
	 * @param	string Tagname used in the XML file
	 * @since	1.5
	 */
	public function __construct($data, $xmldata = '', $xmlelement = 'element', $addDefault = false)
	{
		parent::__construct('_default');

		// Set base path
		$this->_elementPath[] = dirname( __FILE__ ).DS.'parameter'.DS.'element';

		$this->_elementTagName = $xmlelement;

		if (trim( $data )) {
			$this->loadINI($data);
		}

		if(strpos($xmldata, '<')) {
			$this->loadXML($xmldata, $addDefault);
		} elseif ($xmldata != '') {
			$this->loadSetupFile($xmldata, $addDefault);
		}

		$this->_raw = $data;
	}

	/**
	 * Function to overload the class with another rendering engine
	 *
	 * @access	public
	 * @param	string The raw params text
	 * @param	string Path to the XML setup file or XML data
	 * @param	string Tagname used in the XML file
	 * @param	string Name of the class file to overload the JForm class with
	 * @since	1.6
	 */
	public static function getInstance($data, $xmldata = '', $xmlelement = 'element', $engine = null)
	{
		if($engine != null)
		{
			$engine = 'JForm'.ucfirst(strtolower($engine));
			return new $engine($data, $xmldata, $xmlelement);
		}
		return new JForm();
	}

	/**
	 * Set a value
	 *
	 * @access	public
	 * @param	string The name of the param
	 * @param	string The value of the parameter
	 * @return	string The set value
	 * @since	1.5
	 */
	public function set($key, $value = '', $group = '_default')
	{
		return $this->setValue($group.'.'.$key, (string) $value);
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
	public function get($key, $default = '', $group = '_default')
	{
		$value = $this->getValue($group.'.'.$key);
		$result = (empty($value) && ($value !== 0) && ($value !== '0')) ? $default : $value;
		return $result;
	}

	/**
	 * Sets a default value if not alreay assigned
	 *
	 * @access	public
	 * @param	string	The name of the param
	 * @param	string	The value of the parameter
	 * @param	string	The parameter group to modify
	 * @return	string	The set value
	 * @since	1.5
	 */
	public function def($key, $default = '', $group = '_default') {
		$value = $this->get($key, (string) $default, $group);
		return $this->set($key, $value);
	}

	/**
	 * Sets the XML object from custom xml files
	 *
	 * @access	public
	 * @param	object	An XML object
	 * @since	1.5
	 */
	public function setXML( &$xml )
	{
		if (is_object( $xml ))
		{
			if (!$group = $xml->attributes( 'group' )) {
				$group = '_default';
			}
			$this->_xmlAttributes[$group] = $xml->attributes();
			if(isset($this->_xml[$group])) {
				foreach($xml->children() as $child) {
					$this->_xml[$group]->_children[] = $child;
				}
			} else {
				$this->_xml[$group] = $xml;
			}
			if ($dir = $xml->attributes( 'addpath' )) {
				$this->addElementPath( JPATH_ROOT . str_replace('/', DS, $dir) );
			}
		}
	}

	/**
	 * Bind data to the parameter
	 *
	 * @param	mixed	$data Array or Object
	 * @param	string Name of the XML group
	 * @return	boolean	True if the data was successfully bound
	 * @access	public
	 * @since	1.5
	 */
	public function bind($data, $group = '_default')
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
	 * Function to render the HTML for the parameter set/form set
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @param	string	Name of the Group to render
	 * @param	boolean	Render as data view when false, render as form when set to true
	 * @return	string	HTML
	 * @since	1.6
	 */
	public function render($name = 'elements', $group = '_default', $form = true)
	{
		$this->_html = array();
		$this->_html[] = '<table width="100%" class="paramlist admintable" cellspacing="1">';

		if ($description = $this->_xml[$group]->attributes('description')) {
			// add the params description to the display
			$desc	= JText::_($description);
			$this->_html[]	= '<tr><td class="paramlist_description" colspan="2">'.$desc.'</td></tr>';
		}

		$this->_render($this->_xml[$group]->children(), $group, $name, '', '', $form);

		if (count($this->_xml[$group]->children()) < 1) {
			$this->_html[] = "<tr><td colspan=\"2\"><i>".JText::_('There are no Parameters for this item')."</i></td></tr>";
		}

		$this->_html[] = '</table>';
		return implode("\n", $this->_html).implode("\n", $this->_hiddenElements);
	}

	/**
	 * Render all parameters to an array
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @return	array	Array of all parameters, each as array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	public function renderToArray($name = 'element', $group = '_default')
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}
		$results = array();
		foreach ($this->_xml[$group]->children() as $element)  {
			$result = $this->getElement($element, $name);
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
	public function getNumElements($group = '_default')
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
	 * @return	array	Array of all group names as key and param count as value
	 * @since	1.5
	 */
	public function getGroups()
	{
		if (!is_array($this->_xml)) {
			return false;
		}
		$results = array();
		foreach ($this->_xml as $name => $group)  {
			$results[$name] = $this->getNumElements($name);
		}
		return $results;
	}

	/**
	 * Get additional Attributes for a Group
	 *
	 * @access	public
	 * @param	string Name of the group
	 * @return	array
	 * @since	1.6
	 */
	public function getGroupAttributes($group = '')
	{
		if(isset($this->_xmlAttributes[$group]))
		{
			return $this->_xmlAttributes[$group];
		}
		return $this->_xmlAttributes;
	}

	/**
	 * Render all parameters
	 * Notice: This function does not support the conditional parameters introduced in 1.6.
	 * This functionality is implemented in the JParameter::render() function directly.
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @return	array	Aarray of all parameters, each as array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	public function getElements($name = 'elements', $group = '_default')
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}
		$results = array();
		foreach ($this->_xml[$group]->children() as $element)  {
			$results[] = $this->getElement($element, $name);
		}
		return $results;
	}

	/**
	 * Render a parameter type
	 *
	 * @param	object	A param tag node
	 * @param	string	The control name
	 * @return	array	Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */
	public function getElement(&$node, $control_name = 'element', $group = '_default', $form = true)
	{
		//get the type of the parameter
		$type = $node->attributes('type');

		//remove any occurance of a mos_ prefix
		$type = str_replace('mos_', '', $type);

		$element =& $this->loadElement($type);

		// error happened
		if ($element === false)
		{
			$result = array();
			$result[0] = $node->attributes('name');
			$result[1] = JText::_('Element not defined for type').' = '.$type;
			$result[5] = $result[0];
			return $result;
		}

		//get value
		$value = $this->get($node->attributes('name'), $node->attributes('default'), $group);
		if($form) {
			return $element->render($node, $value, $control_name);
		} else {
			return $element->renderElement($node, $value, $control_name);
		}
	}

	/**
	 * Loads an xml setup file and parses it
	 *
	 * @access	public
	 * @param	string	path to xml setup file
	 * @return	object
	 * @since	1.5
	 */
	public function loadXML($xml, $addDefault = false)
	{
		$elementTagName = $this->_elementTagName.'s';
		$result = false;

		$xml = & JFactory::getXMLParser('Simple');

		if ($xml->loadString($xml))
		{
			if ($elements = & $xml->document->$elementTagName) {
				foreach ($elements as $element)
				{
					if ($addDefault)
					{
						foreach($element->_children as &$child)
						{
							if($child->attributes('type') == 'radio' || $child->attributes('type') == 'list') {
								$child->addAttribute('default', '');
								$child->addAttribute('type', 'list');
								$subchild = &$child->addChild('option', array('value' => ''));
								$subchild->setData('Use Global');
							}
						}
					}
					$this->setXML( $element );
					$result = true;
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
	 * Loads an xml setup file and parses it
	 *
	 * @access	public
	 * @param	string	path to xml setup file
	 * @return	object
	 * @since	1.5
	 */
	public function loadSetupFile($path, $addDefault = false)
	{
		static $file;

		$result = false;

		if(!is_array($file))
		{
			$file = array();
		}
		if ($path && !in_array($path, $file))
		{
			$file[] = $path;
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path))
			{
				$elementtagname = $this->_elementTagName.'s';
				if ($elements = & $xml->document->$elementtagname) {
					foreach ($elements as $element)
					{
						if ($addDefault)
						{
							foreach($element->_children as &$child)
							{
								if($child->attributes('type') == 'radio' || $child->attributes('type') == 'list') {
									$child->addAttribute('default', '');
									$child->addAttribute('type', 'list');
									$subchild = &$child->addChild('option', array('value' => ''));
									$subchild->setData('Use Global');
								}
							}
						}
						$this->setXML( $element );
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
	 * Loads all xml setup files from a directory and parses it
	 *
	 * @access	public
	 * @param	string	directory to xml setup file
	 * @return	object
	 * @since	1.6
	 */
	public function loadSetupDirectory($path, $filter = '.xml', $addDefault = false)
	{
		$result = false;

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($path)) {
			return $result;
		}
		$files = JFolder::files($path, $filter, false, true);
		if (count($files))
		{
			foreach($files as $file)
			{
				$result = $this->loadSetupFile($file, $addDefault);
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
	public function &loadElement( $type, $new = false )
	{
		$false = false;
		$signature = md5( $type  );

		if( (isset( $this->_elements[$signature] ) && !($this->_elements[$signature] INSTANCEOF __PHP_Incomplete_Class))  && $new === false ) {
			return	$this->_elements[$signature];
		}

		$elementClass	=	'JElement'.$type;
		if( !class_exists( $elementClass ) )
		{
			if( isset( $this->_elementPath ) ) {
				$dirs = $this->_elementPath;
			} else {
				$dirs = array();
			}

			$file = JFilterInput::clean(str_replace('_', DS, $type).'.php', 'path');

			jimport('joomla.filesystem.path');
			if ($elementFile = JPath::find($dirs, $file)) {
				include_once $elementFile;
			} else {
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
	public function addElementPath( $path )
	{
		// just force path to array
		settype( $path, 'array' );

		// loop through the path directories
		foreach ( $path as $dir )
		{
			// no surrounding spaces allowed!
			$dir = trim( $dir );

			// add trailing separators as needed
			if ( substr( $dir, -1 ) != DIRECTORY_SEPARATOR ) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// add to the top of the search dirs
			array_unshift( $this->_elementPath, $dir );
		}


	}


/**	public function getConditionalScript() {
		if(count($this->_cond) < 1) {
			return false;
		}
		$script = '';
		foreach($this->_cond as $group => $conditionals)
		{
			foreach($conditionals as $name => $values
			$script .= '$(\''.$group.$name.'\').addEvent(\'onChange\', function() {\n';
			foreach($values as $value =>
				$(\'


$this->_cond[$name][$param->attribute('name')][$cond_group->attribute('value')]

$name.$cond.$cond_value.'-cond'
		return implode("\n", $script);
	}
**/

	protected function _render($params, $group, $name = 'params', $cond = '', $cond_value = '', $form = true)
	{
		if($cond != '' && $cond_value != '') {
			$id_prefix = $name.$cond.$cond_value.'-cond';
		} else {
			$id_prefix = '';
		}

		foreach ($params as $param) {
			if($param->name() == 'conditionalParametersGroup') {
				$cond_groups = $param->children();
				foreach($cond_groups as $cond_group) {
					$this->_cond[$name][$param->attribute('name')][$cond_group->attribute('value')] = 0;
					$this->_render($cond_group->children(), $param->attribute('name'), $cond_group->attribute('value'));
				}
			} else {
				$result = $this->getElement($param, $name, '_default', $form);
				if($param->attributes('type') == 'hidden')
				{
					$this->_hiddenElements[] = $result[1];

				} else {
					if($id_prefix != '') {
						$id = ' id="'.$id_prefix.$this->_cond[$name][$cond][$cond_value].'"';
						$this->_cond[$name][$cond][$cond_value]++;
					} else {
						$id = '';
					}
					$this->_html[] = '<tr'.$id.'>';

					if ($result[0]) {
						$this->_html[] = '<td width="40%" class="paramlist_key"><span class="editlinktip">'.$result[0].'</span></td>';
						$this->_html[] = '<td class="paramlist_value">'.$result[1].'</td>';
					} else {
						$this->_html[] = '<td class="paramlist_value" colspan="2">'.$result[1].'</td>';
					}

					$this->_html[] = '</tr>';
				}
			}
		}
	}
}
