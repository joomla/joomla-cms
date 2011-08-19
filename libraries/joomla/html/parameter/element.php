<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Parameter base class
 *
 * The JElement is the base class for all JElement types
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  12.1    Use JFormField instead
 */
class JElement extends JObject
{
	/**
	 * Element name
	 *
	 * This has to be set in the final
	 * renderer classes.
	 *
	 * @var    string
	 */
	protected $_name = null;

	/**
	 * Reference to the object that instantiated the element
	 *
	 * @var    object
	 */
	protected $_parent = null;

	/**
	 * Constructor
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 */
	public function __construct($parent = null)
	{
		// Deprecation warning.
		JLog::add('JElement::__construct is deprecated.', JLog::WARNING, 'deprecated');
		
		$this->_parent = $parent;
	}

	/**
	 * Get the element name
	 *
	 * @return  string  type of the parameter
	 *
	 * @since   11.1
	 * @deprecated    12.1
	 */
	public function getName() {
		// Deprecation warning.
		JLog::add('Jelement::getName is deprecated.', JLog::WARNING, 'deprecated');

		return $this->_name;
	}

	/**
	 * Method to render an xml element
	 *
	 * @param  string   $xmlElement    Name of the element
	 * @param  string   $value         Value of the element
	 * @param  string   $control_name  Name of the control
	 *
	 * @return  array  Attributes of an element
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 */
	public function render(&$xmlElement, $value, $control_name = 'params')
	{
		// Deprecation warning.
		JLog::add('JElement::render is deprecated.', JLog::WARNING, 'deprecated');
		
		$name = $xmlElement->attributes('name');
		$label = $xmlElement->attributes('label');
		$descr = $xmlElement->attributes('description');

		//make sure we have a valid label
		$label = $label ? $label : $name;
		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
		$result[2] = $descr;
		$result[3] = $label;
		$result[4] = $value;
		$result[5] = $name;

		return $result;
	}

	/**
	 * Method to get a tool tip from an XML element
	 * 
	 * @param   string  $label         Label attribute for the element
	 * @param   string  $description   Description attribute for the element
	 * @param   object  $xmlElement    The element object
	 * @param   string  $control_name  Control name
	 * @param   string  $name          Name attribut
	 * 
	 * @return  string
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 */
	public function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
	{
		// Deprecation warning.
		JLog::add('JElement::fetchTooltip is deprecated.', JLog::WARNING, 'deprecated');
		
		$output = '<label id="' . $control_name . $name . '-lbl" for="' . $control_name . $name . '"';
		if ($description)
		{
			$output .= ' class="hasTip" title="' . JText::_($label) . '::' . JText::_($description) . '">';
		}
		else
		{
			$output .= '>';
		}
		$output .= JText::_($label) . '</label>';

		return $output;
	}

	/**
	 * Fetch an element
	 *
	 * @param   string  $name          Name attribute of the element
	 * @param   string  $value         Value attribute of the element
	 * @param   objecr  $xmlElement    Element object
	 * @param   strong  $control_name  Control name of the element
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @deprecated    12.1
	 */
	public function fetchElement($name, $value, &$xmlElement, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElement::fetchElement is deprecated.', JLog::WARNING, 'deprecated');
		
	}
}
