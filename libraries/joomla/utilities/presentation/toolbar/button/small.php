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
 * Renders a small button
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage 	Utilities
 * @since		1.1
 */
class JButton_Small extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Small';

	function fetchButton( $type='Small', $name = '', $text = '', $task = '', $list = true, $hideMenu = false )
	{
		$text	= JText::_($text);
		$class	= $this->fetchIconClass($name);

		$html .= "<a class=\"$class\" onclick=\"$doTask\" title=\"$text\" type=\"$type\">\n";
		$html .= "$text\n";
		$html .= "</a>\n";

		return $html;
	}
	
	/**
	 * Get the button CSS Id
	 * 
	 * @access	public
	 * @return	string	Button CSS Id
	 * @since	1.1
	 */
	function fetchId( $type='Small', $name = '', $text = '', $task = '', $list = true, $hideMenu = false )
	{
		return $name;
	}

}
?>