<?php
/**
 * @version		$Id: formfield.php 11535 2009-01-28 07:48:25Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.utilities.simplexml');

/**
 * Abstract Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
abstract class JFormField extends JObject
{
	/**
	 * The field type.
	 *
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	public $type;

   /**
	* A reference to the form object that the field belongs to.
	*
	* @access	protected
	* @var		object
	* @since	1.6
	*/
	protected $_form;

	/**
	 * Method to instantiate the form field.
	 *
	 * @access	public
	 * @param	object		$form		A reference to the form that the field belongs to.
	 * @return	void
	 * @since	1.6
	 */
	public function __construct($form = null)
	{
		$this->_form = $form;
	}

   /**
	* Method to get the form field type.
	*
	* @access	public
	* @return	string		The field type.
	* @since	1.6
	*/
	public function getType() {
		return $this->type;
	}

	public function render(&$xml, $value, $formName, $groupName)
	{
		// Set the xml element object.
		$this->_element		= $xml;

		// Set the id, name, and value.
		$this->id			= $xml->attributes('id');
		$this->name			= $xml->attributes('name');
		$this->value		= $value;

		// Set the label and description text.
		$this->labelText	= $xml->attributes('label') ? $xml->attributes('label') : $this->name;
		$this->descText		= $xml->attributes('description');

		// Set the required and validate options.
		$this->required		= ($xml->attributes('required') == 'true' || $xml->attributes('required') == 'required');
		$this->validate		= $xml->attributes('validate');

		// Add the required class if the field is required.
		if ($this->required) {
			if (strpos($xml->attributes('class'), 'required') === false) {
				$xml->addAttribute('class', $xml->attributes('class').' required');
			}
		}

		// Set the field decorator.
		$this->decorator	= $xml->attributes('decorator');

		// Set the visibility.
		$this->hidden		= ($xml->attributes('type') == 'hidden' || $xml->attributes('hidden'));

		// Set the multiple values option.
		$this->multiple		= ($xml->attributes('multiple') == 'true' || $xml->attributes('multiple') == 'multiple');

		// Set the form and group names.
		$this->formName		= $formName;
		$this->groupName	= $groupName;

		// Set the input name and id.
		$this->inputName	= $this->_getInputName($this->name, $formName, $groupName, $this->multiple);
		$this->inputId		= $this->_getInputId($this->id, $this->name, $formName, $groupName);

		// Set the actual label and input.
		$this->label		= $this->_getLabel();
		$this->input		= $this->_getInput();

		return $this;
	}

	/**
	 * Method to get the field label.
	 *
	 * @access	protected
	 * @return	string		The field label.
	 * @since	1.6
	 */
	protected function _getLabel()
	{
		// Get the id of the input.
		$id	= $this->_getInputId($this->id, $this->name, $this->formName, $this->groupName);

		// Set the class for the label.
		$class = !empty($this->descText) ? 'hasTip' : '';
		$class = $this->required == true ? $class.' required' : $class;

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->descText)) {
			$label = '<label id="'.$id.'-lbl" for="'.$id.'" class="'.$class.'" title="'.trim(JText::_($this->labelText, true), ':').'::'.JText::_($this->descText, true).'">';
		} else {
			$label = '<label id="'.$id.'-lbl" for="'.$id.'" class="'.$class.'">';
		}

		$label .= JText::_($this->labelText);
		$label .= '</label>';

		return $label;
	}

	/**
	 * Method to get the field input.
	 *
	 * @access	protected
	 * @return	string		The field input.
	 * @since	1.6
	 */
	abstract protected function _getInput();

	/**
	 * Method to get the name of the input field.
	 *
	 * @access	protected
	 * @param	string		$fieldName		The field name.
	 * @param	string		$formName		The form name.
	 * @param	string		$groupName		The group name.
	 * @param	boolean		$multiple		Whether the input should support multiple values.
	 * @return	string		The input field id.
	 * @since	1.6
	 */
	protected function _getInputName($fieldName, $formName = false, $groupName = false, $multiple = false)
	{
		// No form or group, just use the field name.
		if ($formName === false && $groupName === false) {
			$return = $fieldName;
		}
		// No group, use the form and field name.
		elseif ($formName !== false && $groupName === false) {
			$return = $formName.'['.$fieldName.']';
		}
		// No form, use the group and field name.
		elseif ($formName === false && $groupName !== false) {
			$return = $groupName.'['.$fieldName.']';
		}
		// Use the form, group, and field name.
		else {
			$return = $formName.'['.$groupName.']['.$fieldName.']';
		}

		// Check if the field should support multiple values.
		if ($multiple) {
			$return .= '[]';
		}

		return $return;
	}

	/**
	 * Method to get the id of the input field.
	 *
	 * @access	protected
	 * @param	string		$fieldId		The field id.
	 * @param	string		$fieldName		The field name.
	 * @param	string		$formName		The form name.
	 * @param	string		$groupName		The group name.
	 * @return	string		The input field id.
	 * @since	1.6
	 */
	protected function _getInputId($fieldId, $fieldName, $formName = false, $groupName = false)
	{
		// Use the field name if no id is set.
		if (empty($fieldId)) {
			$fieldId = $fieldName;
		}

		// No form or group, just use the field name.
		if ($formName === false && $groupName === false) {
			$return = $fieldId;
		}
		// No group, use the form and field name.
		elseif ($formName !== false && $groupName === false) {
			$return = $formName.'_'.$fieldId;
		}
		// No form, use the group and field name.
		elseif ($formName === false && $groupName !== false) {
			$return = $groupName.'_'.$fieldId;
		}
		// Use the form, group, and field name.
		else {
			$return = $formName.'_'.$groupName.'_'.$fieldId;
		}

		return $return;
	}
}