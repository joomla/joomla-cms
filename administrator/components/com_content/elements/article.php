<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders an article element
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 */

class JElement_Article extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Article';

	function fetchElement($name, $value, &$node, $control_name)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		$template 	= $mainframe->getTemplate();
		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$fieldName	= $control_name.'['.$name.']';
		$article =& JTable::getInstance('content', $db);
		if ($value) {
			$article->load($value);
		} else {
			$article->title = JText::_('Select an Article');
		}

		$js = "
		function jSelectArticle(id, title) {
			document.getElementById('a_id').value = id;
			document.getElementById('a_name').value = title;
			document.popup.hide();
		}";

		$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component.html';
		$doc->addScriptDeclaration($js);
		$doc->addScript($url.'includes/js/joomla/modal.js');
		$doc->addStyleSheet($url.'includes/js/joomla/modal.css');
		$html = "\n<div style=\"float: left;\"><input style=\"background: #ffffff;\" type=\"text\" id=\"a_name\" value=\"$article->title\" disabled=\"disabled\" /></div>";
		$html .= "\n &nbsp; <input class=\"inputbox\" type=\"button\" onclick=\"document.popup.show('$link', 650, 375, null);\" value=\"".JText::_('Select')."\" />";
//		$html .= "<div class=\"button2-left\"><div class=\"blank\"><a title=\"".JText::_('Select an Article')."\" onclick=\"javascript: document.popup.show('$link', 650, 375, null);\">...</a></div></div>\n";
		$html .= "\n<input type=\"hidden\" id=\"a_id\" name=\"$fieldName\" value=\"$value\" />";

		return $html;
	}
}
?>