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
 * @abstract
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		11.1
 * @deprecated	JParameter is deprecated and will be removed in a future version. Use JForm instead.
 */
class JElement extends JObject
{
	/**
	* element name
	*
	* This has to be set in the final
	* renderer classes.
	*
	* @var		string
	*/
	protected $_name = null;

	/**
	* Reference to the object that instantiated the element
	*
	* @var		object
	*/
	protected $_parent = null;

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	public function __construct($parent = null)
	{
		$this->_parent = $parent;
	}

	/**
	* Get the element name
	*
	* @return	string	type of the parameter
	*/
	public function getName() {
		return $this->_name;
	}

	public function render(&$xmlElement, $value, $control_name = 'params')
	{
		$name	= $xmlElement->attributes('name');
		$label	= $xmlElement->attributes('label');
		$descr	= $xmlElement->attributes('description');
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

	public function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		$output = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
		if ($description) {
			$output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
		} else {
			$output .= '>';
		}
		$output .= JText::_($label).'</label>';

		return $output;
	}

	public function fetchElement($name, $value, &$xmlElement, $control_name)
	{

	}
}