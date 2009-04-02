<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Renders a category element
 *
 * @package 	Joomla.Framework
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
		$db = &JFactory::getDBO();

		$extension	= $node->attributes('extension');
		$class		= $node->attributes('class');
		if (!$class) {
			$class = "inputbox";
		}

		if($extension == '')
		{
			$extension = 'com_content';
		}
		
		$categorylist = JHTML::_('list.category', $control_name.'['.$name.']', $extension, NULL, $value);
		return $categorylist;
	}
}
