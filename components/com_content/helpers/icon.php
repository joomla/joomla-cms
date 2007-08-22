<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component HTML Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class JHTMLIcon
{
	function create($article, $params, $access, $attribs = array())
	{
		global $Itemid;

		$user =& JFactory::getUser();

		$url = 'index.php?task=new&id=0&sectionid='.$article->sectionid;

		if ($params->get('show_icons')) {
			$text = JHTML::_('image.site', 'new.png', '/images/M_images/', NULL, NULL, JText::_('New'), JText::_('New'). $article->id );
		} else {
			$text = JText::_('New').'&nbsp;';
		}

		$attribs	= array( 'title' => JText::_( 'New' ));
		return JHTML::_('link', JRoute::_($url), $text, $attribs);
	}

	function pdf($article, $params, $access, $attribs = array())
	{
		global $Itemid;

		$user =& JFactory::getUser();

		$url  = 'index.php?view=article;';
		$url .=  @$article->catslug ? '&catid='.$article->catslug : '';
		$url .= '&id='.$article->slug.'&format=pdf';

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons')) {
			$text = JHTML::_('image.site', 'pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
		} else {
			$text = JText::_('PDF').'&nbsp;';
		}

		$attribs['title']	= JText::_( 'PDF' );
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']     = 'nofollow';

		return JHTML::_('link', JRoute::_($url), $text, $attribs);
	}

	function email($article, $params, $access, $attribs = array())
	{
		global $Itemid;

		$user =& JFactory::getUser();

		$link	= JURI::base().JRoute::_("index.php?view=article&id=".$article->slug, false);
		$url	= 'index.php?option=com_mailto&tmpl=component&link='.base64_encode( $link );

		$status = 'width=400,height=300,menubar=yes,resizable=yes';

		if ($params->get('show_icons')) 	{
			$text = JHTML::_('image.site', 'emailButton.png', '/images/M_images/', NULL, NULL, JText::_('Email'), JText::_('Email'));
		} else {
			$text = '&nbsp;'.JText::_('Email');
		}

		$attribs['title']	= JText::_( 'Email' );
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";

		$output = JHTML::_('link', JRoute::_($url), $text, $attribs);
		return $output;
	}

	function edit($article, $params, $access, $attribs = array())
	{
		global $Itemid;

		$user =& JFactory::getUser();

		if ($params->get('popup')) {
			return;
		}

		if ($article->state < 0) {
			return;
		}

		if (!$access->canEdit && !($access->canEditOwn && $article->created_by == $user->get('id'))) {
			return;
		}

		JHTML::_('behavior.tooltip');

		$url = 'index.php?view=article&id='.$article->id.'&task=edit&Returnid='.$Itemid;
		$text = JHTML::_('image.site', 'edit.png', '/images/M_images/', NULL, NULL, JText::_('Edit'), JText::_('Edit'). $article->id );

		if ($article->state == 0) {
			$overlib = JText::_('Unpublished');
		} else {
			$overlib = JText::_('Published');
		}
		$date = JHTML::_('date', $article->created);
		$author = $article->created_by_alias ? $article->created_by_alias : $article->author;

		$overlib .= '<br />';
		$overlib .= JText::_($article->groups);
		$overlib .= '<br />';
		$overlib .= $date;
		$overlib .= '<br />';
		$overlib .= $author;

		$button = JHTML::_('link', JRoute::_($url), $text);

		$output = '<span class="hasTip" title="'.JText::_( 'Edit Item' ).' :: '.$overlib.'">'.$button.'</span>';
		return $output;
	}


	function print_popup($article, $params, $access, $attribs = array())
	{
		global $Itemid;

		$user =& JFactory::getUser();

		$url  = 'index.php?view=article';
		$url .=  @$article->catslug ? '&catid='.$article->catslug : '';
		$url .= '&id='.$article->slug.'&tmpl=component&print=1&page='.@ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ( $params->get( 'show_icons' ) ) {
			$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
		} else {
			$text = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
		}

		$attribs['title']	= JText::_( 'Print' );
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";

		return JHTML::_('link', JRoute::_($url), $text, $attribs);
	}

	function print_screen($article, $params, $access, $attribs = array())
	{
		global $Itemid;

		$user =& JFactory::getUser();

		// checks template image directory for image, if non found default are loaded
		if ( $params->get( 'show_icons' ) ) {
			$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
		} else {
			$text = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
		}
		return '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
	}

}
?>
