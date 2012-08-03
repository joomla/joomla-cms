<?php
/**
 * Element: Custom Field Value
 * Displays a custom key field (use in combination with customfieldkey)
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Custom Field Value Element
 */
class nnFieldCustomFieldValue
{
	function getLabel($name, $id, $label, $description, $params)
	{
		$this->params = $params;

		$html = '<span id="span_'.$id.'"></span>';
		return $html;
	}

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$size = ($this->def('size') ? 'size="'.$this->def('size').'"' : '');
		$class = ($this->def('class') ? 'class="'.$this->def('class').'"' : 'class="text_area"');
		$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$class.' '.$size.' />';
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_CustomFieldValue extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'CustomFieldValue';

	protected function getLabel()
	{
		$this->_nnfield = new nnFieldCustomFieldValue();
		return $this->_nnfield->getLabel($this->name, $this->id, $this->__get('title'), $this->description, $this->element->attributes());
	}

	protected function getInput()
	{
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}