<?php
/**
* @version $Id: filelist.php 3520 2006-05-16 07:11:19Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a filelist element
 *
 * @author 		Andrew Eddie
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 */

class JElement_FolderList extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'FolderList';

	function fetchElement($name, $value, &$node, $control_name)
	{
		jimport( 'joomla.filesystem.folder' );

		// path to images directory
		$path = JPATH_SITE.$node->attributes('directory');
		$filter = $node->attributes('filter');
		$folders = JFolder::folders($path, $filter);

		$options = array ();
		foreach ($folders as $folder) {
			$options[] = mosHTML::makeOption($folder, $folder);
		}
		if (!$node->attributes('hide_none')) {
			array_unshift($options, mosHTML::makeOption('-1', '- '.JText::_('Do not use').' -'));
		}
		if (!$node->attributes('hide_default')) {
			array_unshift($options, mosHTML::makeOption('', '- '.JText::_('Use default').' -'));
		}

		return mosHTML::selectList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, "param$name");
	}
}
?>