<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Field to select a user id from a modal list.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class JFormFieldUser extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'User';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getInput()
	{
		// Initialise variables.
		$document	= JFactory::getDocument();

		// Load the modal behavior.
		JHtml::_('behavior.modal', 'a.modal');

		// Add the JavaScript select function to the document head.
		$document->addScriptDeclaration(
		"function jSelectUser_".$this->inputId."(id, title, el) {
			document.id('".$this->inputId."_id').value = id;
			document.id('".$this->inputId."_name').value = title;
			SqueezeBox.close();
		}"
		);

		// Setup variables for display.
		$html	= array();
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
		$html[] = '    <a class="modal" title="'.JText::_('Users_Change_User').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Users_Change_User_button').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active user id field.
		$html[] = '<input type="hidden" id="'.$this->inputId.'_id" name="'.$this->inputName.'" value="'.(int)$this->value.'" />';

		return implode("\n", $html);
	}
}