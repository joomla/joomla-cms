<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Mediathumbs field
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class JFormFieldMediathumbs extends JFormField {

	protected $type = 'Mediathumbs';

	/**
	 * Return Label for Mediathumbs field
	 * @see JFormField::getLabel()
	 */
	public function getLabel()
	{		 
		$label = '<label for="c" class="control-label hasTooltip" title="' ;
		$label .= JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_THUMBS_CREATION_METHOD');
		$label .= '">';
		$label .= JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_CREATION_METHOD');
		$label .= '</label>';

		return $label;

	}

	/**
	 * Return Input for Mediathumbs field
	 * @see JFormField::getInput()
	 */
	public function getInput()
	{
		return '<select name="c" class="input-xlarge">'.
				'<option value="' . JImage::SCALE_FILL . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_SCALE_FILL') . '</option>'.
				'<option value="' . JImage::SCALE_INSIDE . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_SCALE_INSIDE') . '</option>'.
				'<option value="' . JImage::SCALE_OUTSIDE . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_SCALE_OUTSIDE') . '</option>'.
				'<option value="' . JImage::CROP . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_CROP') . '</option>'.
				'<option value="' . JImage::CROP_RESIZE . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_CROP_RESIZE') . '</option>'.
				'<option value="' . JImage::SCALE_FIT . '" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_SCALE_FIT') . '</option>'.
				'</select>';
	}
}
