<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a user id from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.2
 */
class JFormFieldContenthistory extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $type = 'ContentHistory';

	/**
	 * Method to get the content history field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		$typeId = JTable::getInstance('Contenttype')->getTypeId($this->element['data-typeAlias']);
		$itemId = $this->form->getValue('id');
		$label = JText::_('JTOOLBAR_VERSIONS');
		$html = array();
		$link = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;field='
			. $this->id . '&amp;item_id=' . $itemId . '&amp;type_id=' . $typeId . '&amp;type_alias='
			. $this->element['data-typeAlias'] . '&amp;' . JSession::getFormToken() . '=1';

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal_' . $this->id);

		$html[] = '		<a class="btn modal_' . $this->id . '" title="' . $label . '" href="' . $link . '"'
			. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = '<i class="icon-archive"></i>';
		$html[] = $label;
		$html[] = '</a>';
		$html[] = '</div>';
		return implode("\n", $html);
	}
}
