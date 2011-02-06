<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a category element
 *
 * @package		Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementCategory extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Category';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db = JFactory::getDbo();

		$extension	= $node->attributes('extension');
		$class		= $node->attributes('class');
		$filter		= explode(',', $node->attributes('filter'));

		if (!isset ($extension)) {
			// alias for extension
			$extension = $node->attributes('scope');
			if (!isset ($extension)) {
				$extension = 'com_content';
			}
		}

		if (!$class) {
			$class = "inputbox";
		}

		if (count($filter) < 1) {
			$filter = null;
		}

		return JHtml::_('list.category', $control_name.'['.$name.']', $extension, $extension.'.view', $filter, (int) $value, $class, null, 1, $control_name.$name);
	}
}