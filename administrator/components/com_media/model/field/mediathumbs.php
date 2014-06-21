<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class JFormFieldMediathumbs extends JFormField {

	protected $type = 'Mediathumbs';

	/**
	 * Return Label for Mediathumbs field
	 * @see JFormField::getLabel()
	 */
	public function getLabel()
	{		 
		$label = '<label for="c" class="control-label hasTooltip" title="' ;
		$label .= JHtml::tooltipText('COM_MEDIA_EDITOR_THUMBS_CREATION_METHOD');
		$label .= '">';
		$label .= JText::_('COM_MEDIA_EDITOR_THUMBS_CREATION_METHOD');
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
				'<option value="' . JImage::SCALE_FILL . '" >Scale Fill</option>'.
				'<option value="' . JImage::SCALE_INSIDE . '" >Scale Inside</option>'.
				'<option value="' . JImage::SCALE_OUTSIDE . '" >Scale Outside</option>'.
				'<option value="' . JImage::CROP . '" >Crop</option>'.
				'<option value="' . JImage::CROP_RESIZE . '" >Crop Resize</option>'.
				'<option value="' . JImage::SCALE_FIT . '" >Scale Fit</option>'.
				'</select>';
	}
}