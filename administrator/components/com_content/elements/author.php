<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Articles
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Renders a author element
 *
 * @package 	Joomla
 * @subpackage	Articles
 * @since		1.5
 */
class JElementAuthor extends JElement
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Author';

	function fetchElement($name, $value, &$node, $control_name)
	{
		return JHtml::_('list.users', $control_name.'['.$name.']', $value);
	}
}