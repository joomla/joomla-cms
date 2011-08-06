<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a category element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  Use JFormFieldCategory instead.
 */
class JElementCategory extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Category';

	/**
	 *
	 *
	 * @since       11.1
	 * @deprecated    12.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementCategory::fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		$db = JFactory::getDbo();

		$extension = $node->attributes('extension');
		$class = $node->attributes('class');
		$filter = explode(',', $node->attributes('filter'));

		if (!isset($extension))
		{
			// Alias for extension
			$extension = $node->attributes('scope');
			if (!isset($extension))
			{
				$extension = 'com_content';
			}
		}

		if (!$class)
		{
			$class = "inputbox";
		}

		if (count($filter) < 1)
		{
			$filter = null;
		}

		return JHtml::_('list.category', $control_name . '[' . $name . ']', $extension, $extension . '.view', $filter, (int) $value, $class, null, 1,
			$control_name . $name);
	}
}
