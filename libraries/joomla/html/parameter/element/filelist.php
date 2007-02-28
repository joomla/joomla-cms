<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a filelist element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementFilelist extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Filelist';

	function fetchElement($name, $value, &$node, $control_name)
	{
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );

		// path to images directory
		$path		= JPATH_ROOT.DS.$node->attributes('directory');
		$filter		= $node->attributes('filter');
		$exclude	= $node->attributes('exclude');
		$stripExt	= $node->attributes('stripext');
		$files		= JFolder::files($path, $filter);

		$options = array ();

		if (!$node->attributes('hide_none'))
		{
			$options[] = JHTMLSelect::option('-1', '- '.JText::_('Do not use').' -');
		}

		if (!$node->attributes('hide_default'))
		{
			$options[] = JHTMLSelect::option('', '- '.JText::_('Use default').' -');
		}

		foreach ($files as $file)
		{
			if ($exclude)
			{
				if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $file ))
				{
					continue;
				}
			}
			if ($stripExt)
			{
				$file = JFile::stripExt( $file );
			}
			$options[] = JHTMLSelect::option($file, $file);
		}

		return JHTMLSelect::genericList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, "param$name");
	}
}
?>
