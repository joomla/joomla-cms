<?php
/**
 * @version
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports a modal contact picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @since		1.6
 */
class JFormFieldModal_Contacts extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Modal_Contacts';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getInput()
	{
		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		// Load the javascript and css
		JHtml::_('behavior.framework');
		JHtml::script('modal.js');
		JHtml::stylesheet('modal.css');

		// Attach modal behavior to document
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			var div = new Element('div').setStyle('display', 'none').injectBefore(document.id('menu-types'));
			document.id('menu-types').injectInside(div);
			SqueezeBox.initialize();
			SqueezeBox.assign($$('input.modal'), {
				parse: 'rel'
			});
		});");

		// Get the title of the linked chart
		$db->setQuery(
			'SELECT name' .
			' FROM #__contact_details' .
			' WHERE id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

		if (empty($title)) {
			$title = JText::_('Contact_Select_a_Contact');
		}

		$doc->addScriptDeclaration(
		"function jSelectChart_".$this->inputId."(id, name, object) {
			document.id('".$this->inputId."_id').value = id;
			document.id('".$this->inputId."_name').value = name;
			SqueezeBox.close();
		}"
		);

		$link = 'index.php?option=com_contact&amp;view=contacts&amp;layout=modal&amp;tmpl=component&amp;function=jSelectChart_'.$this->inputId;

		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div class="fltlft"><input type="text" id="'.$this->inputId.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('Contact_Change_Contact').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('Contact_Change_Contact_button').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$this->inputId.'_id" name="'.$this->inputName.'" value="'.(int) $this->value.'" />';

		return $html;
	}
}
