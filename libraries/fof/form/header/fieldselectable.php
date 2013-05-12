<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Generic field header, with drop down filters
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderFieldselectable extends FOFFormHeaderField
{

	/**
	 * Create objects for the options
	 *
	 * @return  array  The array of option objects
	 */
	protected function getOptions()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{

			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$options[] = JHtml::_(
					'select.option', (string) $option['value'], JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text', ((string) $option['disabled'] == 'true')
			);
		}

		reset($options);

		return $options;
	}

}
