<?php
/**
 * @version
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.field');
/**
 * Form Field Type Class for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class JFormFieldModal_Users extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Modal_Users';

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
		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
			// Get the title of the linked chart
		$db->setQuery(
			'SELECT name' .
			' FROM #__users' .
			' WHERE id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

		if (empty($title)) {
			$title = JText::_('Contact_Select_a_User');
		}
		// Load the modal behavior.
		JHtml::_('behavior.modal', 'a.modal');

		$document = &JFactory::getDocument();
		$document->addScriptDeclaration($js);

		// Setup variables for display.
		$link = 'index.php?option=com_users&amp;view=users&layout=modal&amp;tmpl=component&amp;function=jSelectChart_'.$this->inputId;

		// Load the current username if available.
		$table = &JTable::getInstance('user');
		if ($this->value) {
			$table->load($this->value);
		} else {
			$table->username = JText::_('Contact_Select_User');
		}
		$title = htmlspecialchars($table->username, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$this->inputId.'_name" value="'.$title.'" disabled="disabled" />';
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '    <a class="modal" title="'.JText::_('Contact_Select_User').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('JSelect').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active user id field.
		$html[] = '<input type="hidden" id="'.$this->inputId.'_id" name="'.$this->inputName.'" value="'.(int)$this->value.'" />';
		$doc->addScriptDeclaration(
			"function jSelectChart_".$this->inputId."(id, name, object) {
				document.id('".$this->inputId."_id').value = id;
				document.id('".$this->inputId."_name').value = name;
				SqueezeBox.close();
			}"
		);
	return implode("\n", $html);
	}
}
