<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Content Component HTML Helper
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class JHTMLIcon
{
	static function create($article, $params)
	{
		$uri = &JFactory::getURI();

		$url = 'index.php?option=com_contenttask=article.add&return='.base64_encode($uri).'&id=0&sectionid='.$article->sectionid;

		if ($params->get('show_icons')) {
			$text = JHtml::_('image', 'system/new.png', JText::_('New'), NULL, true);
		} else {
			$text = JText::_('New').'&nbsp;';
		}

		$attribs	= array('title' => JText::_('New'));
		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	static function email($article, $params, $attribs = array())
	{
		$uri	= &JURI::getInstance();
		$base	= $uri->toString(array('scheme', 'host', 'port'));
		$link	= $base.JRoute::_(ContentRoute::article($article->slug, $article->catslug) , false);
		$url	= 'index.php?option=com_mailto&tmpl=component&link='.base64_encode($link);

		$status = 'width=400,height=350,menubar=yes,resizable=yes';

		if ($params->get('show_icons')) 	{
			$text = JHtml::_('image', 'system/emailButton.png', JText::_('Email'), NULL, true);
		} else {
			$text = '&nbsp;'.JText::_('Email');
		}

		$attribs['title']	= JText::_('Email');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);
		return $output;
	}

	static function edit($article, $params, $attribs = array())
	{
		$user = &JFactory::getUser();
		$uri = &JFactory::getURI();

		if ($params->get('popup')) {
			return;
		}

		if ($article->state < 0) {
			return;
		}

		if (!$user->authorise('core.edit', 'com_content.article.'.$article->id)) {
			return;
		}

		JHtml::_('behavior.tooltip');

		$url = 'index.php?task=article.edit&id='.$article->id.'&return='.base64_encode($uri);
		$icon = $article->state ? 'edit.png' : 'edit_unpublished.png';
		$text = JHtml::_('image', 'system/'.$icon, JText::_('Edit'), NULL, true);

		if ($article->state == 0) {
			$overlib = JText::_('Unpublished');
		} else {
			$overlib = JText::_('Published');
		}
		$date = JHtml::_('date', $article->created);
		$author = $article->created_by_alias ? $article->created_by_alias : $article->created_by;

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= $author;

		$button = JHtml::_('link', JRoute::_($url), $text);

		$output = '<span class="hasTip" title="'.JText::_('EDIT_ITEM').' :: '.$overlib.'">'.$button.'</span>';
		return $output;
	}


	static function print_popup($article, $params, $attribs = array())
	{
		$url  = 'index.php?view=article';
		$url .=  @$article->catslug ? '&catid='.$article->catslug : '';
		$url .= '&id='.$article->slug.'&tmpl=component&print=1&layout=default&page='.@ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons')) {
			$text = JHtml::_('image',  'system/printButton.png', JText::_('Print'), NULL, true);
		} else {
			$text = JText::_('ICON_SEP') .'&nbsp;'. JText::_('Print') .'&nbsp;'. JText::_('ICON_SEP');
		}

		$attribs['title']	= JText::_('Print');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']     = 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	static function print_screen($article, $params, $attribs = array())
	{
		// checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons')) {
			$text = JHtml::_('image',  'system/printButton.png', JText::_('Print'), NULL, true);
		} else {
			$text = JText::_('ICON_SEP') .'&nbsp;'. JText::_('Print') .'&nbsp;'. JText::_('ICON_SEP');
		}
		return '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
	}

}
