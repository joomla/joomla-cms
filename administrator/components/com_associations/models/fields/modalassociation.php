<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal item picker.
 *
 * @since  3.7.0
 */
class JFormFieldModalAssociation extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.7.0
	 */
	protected $type = 'Modal_Association';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		// The active item id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Build the script.
		$script = array();

		// Select button script
		$script[] = 'function jSelectAssociation_' . $this->id . '(id) {';
		$script[] = '   target = document.getElementById("target-association");';
		$script[] = '   document.getElementById("target-association").src = target.getAttribute("data-editurl") + '
						. '"&task=" + target.getAttribute("data-item") + ".edit" + "&id=" + id';
		$script[] = '	jQuery("#associationSelect' . $this->id . 'Modal").modal("hide");';
		$script[] = '}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();

		$linkAssociations = 'index.php?option=com_associations&amp;view=associations&amp;layout=modal&amp;tmpl=component'
			. '&amp;forcedItemType=' . JFactory::getApplication()->input->get('itemtype', '', 'string') . '&amp;function=jSelectAssociation_' . $this->id;

		$linkAssociations .= "&amp;forcedLanguage=' + document.getElementById('target-association').getAttribute('data-language') + '";

		$urlSelect = $linkAssociations . '&amp;' . JSession::getFormToken() . '=1';

		// Select custom association button
		$html[] = '<a'
			. ' id="select-change"'
			. ' class="btn' . ($value ? '' : ' hidden') . '"'
			. ' data-toggle="modal"'
			. ' data-select="' . JText::_('COM_ASSOCIATIONS_SELECT_TARGET') . '"'
			. ' data-change="' . JText::_('COM_ASSOCIATIONS_CHANGE_TARGET') . '"'
			. ' role="button"'
			. ' href="#associationSelect' . $this->id . 'Modal">'
			. '<span class="icon-file" aria-hidden="true"></span>'
			. '<span id="select-change-text"></span>'
			. '</a>';

		// Clear association button
		$html[] = '<button'
 				. ' class="btn' . ($value ? '' : ' hidden') . '"'
 				. ' onclick="return Joomla.submitbutton(\'undo-association\');"'
 				. ' id="remove-assoc">'
 				. '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('JCLEAR')
 				. '</button>';

		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $value . '" />';

		// Select custom association modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'associationSelect' . $this->id . 'Modal',
			array(
				'title'       => JText::_('COM_ASSOCIATIONS_SELECT_TARGET'),
				'backdrop'    => 'static',
				'url'         => $urlSelect,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>',
			)
		);

		return implode("\n", $html);
	}
}
