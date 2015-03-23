<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * MediafLIP field
 *
 * @since  3.5
 */
class JFormFieldMediaflip extends JFormField
{
	protected $type = 'Mediaflip';

	/**
	 * Return Label for Mediafflip field
	 *
	 * @return string  Label for Mediaflip field
	 *
	 * @see JFormField::getLabel()
	 */
	public function getLabel()
	{
		$label = '<label for="mode" class="control-label hasTooltip" title="';
		$label .= JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_FLIP_MODE');
		$label .= '">';
		$label .= JText::_('COM_MEDIA_EDITOR_IMAGE_FLIP_MODE');
		$label .= '</label>';

		return $label;
	}

	/**
	 * Return Input for Mediaflip field
	 *
	 * @return string  Input for Mediaflip field
	 *
	 * @see JFormField::getInput()
	 */
	public function getInput()
	{
		return '<select name="mode" class="input-xlarge">' .
				'<option value="' . IMG_FLIP_HORIZONTAL . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FLIP_HORIZONTAL') . '</option>' .
				'<option value="' . IMG_FLIP_VERTICAL . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FLIP_VERTICAL') . '</option>' .
				'<option value="' . IMG_FLIP_BOTH . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FLIP_BOTH') . '</option>' .
				'</select>';
	}
}
