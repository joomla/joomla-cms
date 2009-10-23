<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Form Field Type Class for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class JFormFieldUser extends JFormField
{
	/**
	 * Method to generate the form field markup.
	 *
	 * @param	string	The form field name.
	 * @param	string	The form field value.
	 * @param	object	The JFormField object.
	 * @param	string	The form field control name.
	 * @return	string	Form field markup.
	 */
	protected function _getInput()
	{
		// Load the modal behavior.
		JHtml::_('behavior.modal', 'a.modal');

		// Add the JavaScript select function to the document head.
		$js = '
		function jxSelectUser(id, title, el) {
			console.log(el);
			document.id(el + \'_id\').value = id;
			document.id(el + \'_name\').value = title;
			document.id(\'sbox-window\').close();
		}';
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);

		// Setup variables for display.
		$link = 'index.php?option=com_users&amp;view=users&layout=modal&amp;tmpl=component&amp;field='.$this->inputId;

		// Load the current username if available.
		$table = &JTable::getInstance('user');
		if ($this->value) {
			$table->load($this->value);
		} else {
			$table->username = JText::_('Select a User');
		}
		$title = htmlspecialchars($table->username, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$this->inputId.'_name" value="'.$title.'" disabled="disabled" />';
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '    <a class="modal" title="'.JText::_('Select a User').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Select').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active user id field.
		$html[] = '<input type="hidden" id="'.$this->inputId.'_id" name="'.$this->inputName.'" value="'.(int)$this->value.'" />';


		return implode("\n", $html);
	}
}