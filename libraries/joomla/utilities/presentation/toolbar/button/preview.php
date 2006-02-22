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
 * Renders a preview popup window button
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage 	Utilities
 * @since		1.1
 */
class JButton_Preview extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Preview';

	function fetchButton( $type='Preview', $url = '', $updateEditor = false )
	{
		$text	= JText::_('Preview');
		$class	= $this->fetchIconClass('preview');
		$doTask	= $this->_getCommand($url, $updateEditor);

		$html .= "<a class=\"$class\" onclick=\"$doTask\" title=\"$text\" type=\"$type\">\n";
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
		return $this->_parent->_name.'-'."preview";
	}
	
	/**
	 * Get the JavaScript command for the button
	 * 
	 * @access	private
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.1
	 */
	function _getCommand($url, $update)
	{
		global $mainframe;
		
		$script = "function popup() {";
		if ($update) {
			$editor =& JEditor::getInstance();
			$script .= $editor->getEditorContents( 'editor1', 'introtext' );
			$script .= $editor->getEditorContents( 'editor2', 'fulltext' );
		}
		$script .= "window.open('$url&task=preview', 'preview', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		}";

		$doc = & $mainframe->getDocument();
		$doc->addScriptDeclaration($script);
		
		$cmd = "popup();";
		
		return $cmd;
	}
}
?>