<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a filelist parameter
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Parameters
 * @abstract
 * @since 1.1
 */

class JParameter_FileList extends JParameter
{
   /**
	* parameter type
	*
	* @access	protected
	* @var		string
	*/
	var	$_type = 'FileList';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		// path to images directory
		$path = JPATH_SITE.$node->getAttribute('directory');
		$filter = $node->getAttribute('filter');
		$files = mosReadDirectory($path, $filter);

		$options = array ();
		foreach ($files as $file) {
			$options[] = mosHTML::makeOption($file, $file);
		}
		if (!$node->getAttribute('hide_none')) {
			array_unshift($options, mosHTML::makeOption('-1', '- '.JText::_('Do not use an image').' -'));
		}
		if (!$node->getAttribute('hide_default')) {
			array_unshift($options, mosHTML::makeOption('', '- '.JText::_('Use Default image').' -'));
		}

		return mosHTML::selectList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, "param$name");
	}
}
?>