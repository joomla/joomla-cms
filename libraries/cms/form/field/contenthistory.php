<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a user id from a modal list.
 *
 * @since  3.2
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
		$footer = '<button class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JTOOLBAR_CLOSE') . '</a>';
		$typeId = JTable::getInstance('Contenttype')->getTypeId($this->element['data-typeAlias']);
		$itemId = $this->form->getValue('id');
		$label = JText::_('JTOOLBAR_VERSIONS');
		$link = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;field='
			. $this->id . '&amp;item_id=' . $itemId . '&amp;type_id=' . $typeId . '&amp;type_alias='
			. $this->element['data-typeAlias'] . '&amp;' . JSession::getFormToken() . '=1';

		// Create Bootstrap modal.
		$html = JHtml::_(
			'bootstrap.renderModal',
			'contenthistoryModal_' . $this->id,
			array(
				'title' 	  => JText::_('COM_CONTENTHISTORY_MODAL_TITLE'),
				'backdrop' 	  => 'static',
				'keyboard' 	  => true,
				'closeButton' => true,
				'footer' 	  => $footer,
				'url'		  => $link,
				'height' 	  => '300px',
				'width' 	  => '500px'
			)
		);

		$html .= '<a href="#contenthistoryModal_' . $this->id . '" role="button" class="btn" data-toggle="modal" title="' . $label . '">
			<span class="icon-archive"></span> ' . $label . '</a>';

		return $html;
	}
}
