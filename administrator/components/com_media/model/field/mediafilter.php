<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class JFormFieldMediafilter extends JFormField {

	protected $type = 'Mediafilter';

	/**
	 * Return Label for Mediafilter field
	 * @see JFormField::getLabel()
	 */
	public function getLabel()
	{		 
		$label = '<label for="filter" class="control-label hasTooltip" title="' ;
		$label .= JHtml::tooltipText('COM_MEDIA_EDITOR_FILTER_NAME');
		$label .= '">';
		$label .= JText::_('COM_MEDIA_EDITOR_FILTER_NAME');
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
				'<option value="smooth" >Smooth</option>'.
				'<option value="contrast" >Contrast</option>'.
				'<option value="edgedetect" >Edge Detect</option>'.
				'<option value="grayscale" >Grayscale</option>'.
				'<option value="sketchy" >Sketchy</option>'.
				'<option value="emboss" >Emboss</option>'.
				'<option value="brightness" >Brightness</option>'.
				'<option value="negate" >Negate</option>'.
				'</select>';
	}
}