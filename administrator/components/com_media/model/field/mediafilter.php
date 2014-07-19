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
 * Mediafilter field
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class JFormFieldMediafilter extends JFormField {

	protected $type = 'Mediafilter';

	/**
	 * Return Label for Mediafilter field
	 * @see JFormField::getLabel()
	 */
	public function getLabel()
	{		 
		$label = '<label for="filter" class="control-label hasTooltip" title="' ;
		$label .= JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_FILTER_NAME');
		$label .= '">';
		$label .= JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_NAME');
		$label .= '</label>';

		return $label;

	}

	/**
	 * Return Input for Mediafilter field
	 * @see JFormField::getInput()
	 */
	public function getInput()
	{
		return '<select name="filter" class="input-xlarge">'.
				'<option value="smooth" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_SMOOTH') . '</option>'.
				'<option value="contrast" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_CONTRAST') . '</option>'.
				'<option value="edgedetect" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_EDGE_DETECT') . '</option>'.
				'<option value="grayscale" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_GRAYSCALE') . '</option>'.
				'<option value="sketchy" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_SKETCHY') . '</option>'.
				'<option value="emboss" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_EMBOSS') . '</option>'.
				'<option value="brightness" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_BRIGHTNESS') . '</option>'.
				'<option value="negate" >' . JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_NEGATE') . '</option>'.
				'</select>';
	}
}