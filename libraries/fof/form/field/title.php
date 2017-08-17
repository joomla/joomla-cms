<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the FOF framework
 * Supports a title field with an optional slug display below it.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldTitle extends FOFFormFieldText implements FOFFormField
{
	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		// Initialise
		$slug_format	= '(%s)';
		$slug_class		= 'small';

		// Get field parameters
		if ($this->element['slug_field'])
		{
			$slug_field = (string) $this->element['slug_field'];
		}
		else
		{
			$slug_field = $this->item->getColumnAlias('slug');
		}

		if ($this->element['slug_format'])
		{
			$slug_format = (string) $this->element['slug_format'];
		}

		if ($this->element['slug_class'])
		{
			$slug_class = (string) $this->element['slug_class'];
		}

		// Get the regular display
		$html = parent::getRepeatable();

		$slug = $this->item->$slug_field;

		$html .= '<br>' . '<span class="' . $slug_class . '">';
		$html .= JText::sprintf($slug_format, $slug);
		$html .= '</span>';

		return $html;
	}
}
