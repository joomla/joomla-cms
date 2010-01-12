<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldRadio extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Radio';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		// Get the options for the radio list.
		$options = array();
		foreach ($this->_element->children() as $option) {
			$tmp = JHtml::_('select.option', $option->attributes('value'), trim($option->data()));
			$tmp->class = $option->attributes('class');
			$tmp->onclick = $option->attributes('onclick') ? ' onclick="'.$this->_replacePrefix($option->attributes('onclick')).'"' : '';
			$options[] = $tmp;
		}
		reset($options);

		// Get the fieldset class.
		$class = $this->_element->attributes('class') ? ' class="radio '.$this->_element->attributes('class').'"': ' class="radio"';

		$html = array();
		$html[] = '<fieldset id="'.$this->inputId.'"'.$class.'>';

		foreach ($options as $i => $option) {
			if (is_array($this->value)) {
				foreach ($this->value as $val) {
					$value = is_object($val) ? $val->value : $val;
					if ($option->value == $value) {
						$bool = ' selected="selected"';
						break;
					}
				}
			} else {
				$bool = ((string) $option->value == (string) $this->value ? ' checked="checked"' : null);
			}

			// Get the defined class for the option if set.
			$class = isset($option->class) ? ' class="'.$option->class.'"' : null;


			$html[] = '<input id="'.$this->inputId.$i.'" type="radio" name="'.$this->inputName.'" value="'.htmlspecialchars($option->value).'"'.$bool.$option->onclick.' />';
			$html[] = '<label for="'.$this->inputId.$i.'"'.$class.'>'.JText::_($option->text).'</label>';
		}

		$html[] = '</fieldset>';

		return implode($html);
	}
}
