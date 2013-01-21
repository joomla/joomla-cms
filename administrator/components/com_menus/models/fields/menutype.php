<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.6
 */
class JFormFieldMenutype extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'menutype';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$html 		= array();
		$recordId	= (int) $this->form->getValue('id');
		$size		= ($v = $this->element['size']) ? ' size="'.$v.'"' : '';
		$class		= ($v = $this->element['class']) ? ' class="'.$v.'"' : 'class="text_area"';

		// Get a reverse lookup of the base link URL to Title
		$model 	= JModelLegacy::getInstance('menutypes', 'menusModel');
		$rlu 	= $model->getReverseLookup();

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

			default:
				$link	= $this->form->getValue('link');
				// Clean the link back to the option, view and layout
				$value	= JText::_(JArrayHelper::getValue($rlu, MenusHelper::getLinkKey($link)));
				break;
		}
		// Load the javascript and css
		JHtml::_('behavior.framework');
		JHtml::_('behavior.modal');

		$html[] = '<span class="input-append"><input type="text" readonly="readonly" disabled="disabled" value="'.$value.'"'.$size.$class.' /><a class="btn btn-primary" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\''.JRoute::_('index.php?option=com_menus&view=menutypes&tmpl=component&recordId='.$recordId).'\'})"><i class="icon-list icon-white"></i> '.JText::_('JSELECT').'</a></span>';
		$html[] = '<input class="input-small" type="hidden" name="' . $this->name . '" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';

		return implode("\n", $html);
	}
}
