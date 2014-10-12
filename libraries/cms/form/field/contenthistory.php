<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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

		JFactory::getDocument()->addStyleDeclaration('
		@media only screen and (min-width : 768px) {
			#versionsModal {
			width: 80% !important;
			margin-left:-40% !important;
			height:auto;
			}
			#versionsModal #versionsModal-container .modal-body iframe {
			margin:0;
			padding:0;
			display:block;
			width:100%;
			height:400px !important;
			border:none;
			}
		}');

		// Include jQuery
		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.modal');
		$html[] = '<button href="#versionsModal" role="button" class="btn btn-small" data-toggle="modal" title="' . $label . '"><span class="icon-archive"></span>' . $label . '</button>';
		$html[] = JHtmlBootstrap::renderModal('versionsModal', array( 'url' => $link, 'title' => $label ,'height' => '800px', 'width' => '600px'), '');

		return implode("\n", $html);
	}
}
