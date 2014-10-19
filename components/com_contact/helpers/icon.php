<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component HTML Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class JHtmlIcon
{
	public static function email($contact, $params, $attribs = array())
	{
		require_once JPATH_SITE . '/components/com_mailto/helpers/mailto.php';
		$uri	= JUri::getInstance();
		$base	= $uri->toString(array('scheme', 'host', 'port'));
		$link	= $base . JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid), false);
		$url	= 'index.php?option=com_mailto&tmpl=component&link=' . MailToHelper::addLink($link);

		$status = 'width=400,height=350,menubar=yes,resizable=yes';

		if ($params->get('show_icons'))
		{
			$text = JHtml::_('image', 'system/emailButton.png', JText::_('JGLOBAL_EMAIL'), null, true);
		}
		else
		{
			$text = '&#160;' . JText::_('JGLOBAL_EMAIL');
		}

		$attribs['title']	= JText::_('JGLOBAL_EMAIL');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);

		return $output;
	}

	public static function print_popup($article, $params, $attribs = array())
	{
		$url  = ContentHelperRoute::getContactRoute($contact->slug, $contact->catid);
		$url .= '&tmpl=component&print=1&layout=default&page=' . @ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// Checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons'))
		{
			$text = JHtml::_('image', 'system/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
		}
		else
		{
			$text = JText::_('JGLOBAL_ICON_SEP') . '&#160;' . JText::_('JGLOBAL_PRINT') .'&#160;' . JText::_('JGLOBAL_ICON_SEP');
		}

		$attribs['title']   = JText::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']	    = 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	public static function print_screen($contact, $params, $attribs = array())
	{
		// Checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons'))
		{
			$text = JHtml::_('image', 'system/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
		}
		else
		{
			$text = JText::_('JGLOBAL_ICON_SEP') .'&#160;' . JText::_('JGLOBAL_PRINT') . '&#160;' . JText::_('JGLOBAL_ICON_SEP');
		}

		return '<a href="#" onclick="window.print();return false;">' . $text . '</a>';
	}
}
