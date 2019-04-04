<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Menu Type field.
 *
 * @since  1.6
 */
class JFormFieldMenutype extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $type = 'menutype';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$html     = array();
		$recordId = (int) $this->form->getValue('id');
		$size     = (string) ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    = (string) ($v = $this->element['class']) ? ' class="' . $v . '"' : 'class="text_area"';
		$required = (string) $this->element['required'] ? ' required="required"' : '';
		$clientId = (int) $this->element['clientid'] ?: 0;

		// Get a reverse lookup of the base link URL to Title
		switch ($this->value)
		{
			case 'url':
				$value = JText::_('COM_MENUS_TYPE_EXTERNAL_URL');
				break;

			case 'alias':
				$value = JText::_('COM_MENUS_TYPE_ALIAS');
				break;

			case 'separator':
				$value = JText::_('COM_MENUS_TYPE_SEPARATOR');
				break;

			case 'heading':
				$value = JText::_('COM_MENUS_TYPE_HEADING');
				break;

			case 'container':
				$value = JText::_('COM_MENUS_TYPE_CONTAINER');
				break;

			default:
				$link = $this->form->getValue('link');

				/** @var  MenusModelMenutypes $model */
				$model = JModelLegacy::getInstance('Menutypes', 'MenusModel', array('ignore_request' => true));
				$model->setState('client_id', $clientId);

				$rlu   = $model->getReverseLookup();

				// Clean the link back to the option, view and layout
				$value = JText::_(ArrayHelper::getValue($rlu, MenusHelper::getLinkKey($link)));
				break;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration('
			function jSelectPosition_' . $this->id . '(name) {
				document.getElementById("' . $this->id . '").value = name;
			}
		'
		);

		$link = JRoute::_('index.php?option=com_menus&view=menutypes&tmpl=component&client_id=' . $clientId . '&recordId=' . $recordId);
		$html[] = '<span class="input-append"><input type="text" ' . $required . ' readonly="readonly" id="' . $this->id
			. '" value="' . $value . '" ' . $size . $class . ' />';
		$html[] = '<button type="button" data-target="#menuTypeModal" class="btn btn-primary" data-toggle="modal" title="' . JText::_('JSELECT') . '">'
			. '<span class="icon-list icon-white" aria-hidden="true"></span> '
			. JText::_('JSELECT') . '</button></span>';
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'menuTypeModal',
			array(
				'url'        => $link,
				'title'      => JText::_('COM_MENUS_ITEM_FIELD_TYPE_LABEL'),
				'width'      => '800px',
				'height'     => '300px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer'     => '<button type="button" class="btn" data-dismiss="modal">'
						. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
			)
		);
		$html[] = '<input class="input-small" type="hidden" name="' . $this->name . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';

		return implode("\n", $html);
	}
}
