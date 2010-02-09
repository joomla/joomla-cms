<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldMedia extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Media';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		static $init = false;
		$html = '';

		$onchange = (string)$this->_element->attributes()->onchange ? $this->_replacePrefix((string)$this->_element->attributes()->onchange) : '';
		$readonly = (string)$this->_element->attributes()->readonly == 'true';
		if (!$init) {
			JHtml::_('behavior.modal');
			$js = "
			function jInsertFieldValue(value,id) {
				var old_id = document.getElementById(id).value;
				if (old_id != id)
				{
					document.getElementById(id).value = value;
					".$onchange."
				}
			}";
			$doc = &JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			$init = true;
		}

		$link	= (string)$this->_element->attributes()->link.$this->inputId;
		$size	= (string)$this->_element->attributes()->size ? ' size="'.$this->_element->attributes()->size.'"' : '';
		$class	= (string)$this->_element->attributes()->class ? ' class="'.$this->_element->attributes()->class.'"' : '';

		$html .= '<div style="float: left;">';
		$html .= '<input type="text" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" disabled="disabled"'.$class.$size.' />';
		$html .= '</div>';
		$html .= '<div class="button2-left">';
		$html .= '<div class="blank">';
		$html .= '<a class="modal" title="'.JText::_('SELECT').'" href="'.($readonly?'':$link).'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">';
		$html .= JText::_('SELECT');
		$html .= '</a>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}
