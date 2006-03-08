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
 * Renders an upload popup window button
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage 	Presentation
 * @since		1.1
 */
class JButton_Upload extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Upload';

	function fetchButton( $type='Upload', $text = 'Upload', $directory = '' )
	{
		$text	= JText::_($text);
		$class	= $this->fetchIconClass('upload');
		$doTask	= $this->_getCommand($directory);

		$html  = "<a onclick=\"$doTask\">\n";
		$html .= "<div class=\"$class\" title=\"$text\" type=\"$type\">\n";
		$html .= "</div>\n";
		$html .= "$text\n";
		$html .= "</a>\n";

		return $html;
	}
	
	/**
	 * Get the button id
	 * 
	 * Redefined from JButton class
	 * 
	 * @access		public
	 * @param		string	$name	Button name
	 * @return		string	Button CSS Id
	 * @since		1.1
	 */
	function fetchId($name)
	{
		return $this->_parent->_name.'-'."upload";
	}
	
	/**
	 * Get the JavaScript command for the button
	 * 
	 * @access	private
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.1
	 */
	function _getCommand($directory)
	{
		$cmd = "popupWindow('index3.php?option=com_media&amp;task=popupUpload&amp;directory=$directory','win1',550,200,'no');";
		
		return $cmd;
	}
}
?>