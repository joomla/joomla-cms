<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewSection extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid, $option;

		// Initialize some variables
		$user	  =& JFactory::getUser();
		$document =& JFactory::getDocument();
		$pathway  = & $mainframe->getPathWay();

		// Get the menu object of the active menu item
		$menu    =& JSiteHelper::getCurrentMenuItem();
		$params  =& JSiteHelper::getMenuParams();

		// Request variables
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		//parameters
		$intro		= $params->def('intro', 	4);
		$leading	= $params->def('leading', 	1);
		$links		= $params->def('link', 		4);

		$limit	= $intro + $leading + $links;
		JRequest::setVar('limit', $limit);

		// Get some data from the model
		$items      = & $this->get( 'Data');
		$total      = & $this->get( 'Total');
		$categories	= & $this->get( 'Categories' );
		$section    = & $this->get( 'Section' );

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		//add alternate feed link
		$link    = JURI::base() .'feed.php?option=com_content&amp;view=section&amp;id='.$section->id.'&amp;Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&amp;format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&amp;format=atom', 'alternate', 'rel', $attribs);

		// Set the page title and breadcrumbs
		$pathway->addItem($section->title, '');

		if (!empty ($menu->name)) {
			$mainframe->setPageTitle($menu->name);
		}

		for($i = 0; $i < count($categories); $i++)
		{
			$category =& $categories[$i];
			$category->link = sefRelToAbs('index.php?option=com_content&amp;view=category&amp;id='.$category->id.'&amp;Itemid='.$Itemid);
		}

		$params->def('empty_cat_section', 	0);
		$params->def('other_cat', 			1);
		$params->def('empty_cat', 			0);
		$params->def('cat_items', 			1);
		$params->def('pageclass_sfx', 		'');
		$params->set('intro_only', 			1);

		if ($total == 0) {
			$params->set('other_cat_section', false);
		}

		if ($params->def('page_title', 1)) {
			$params->def('header', $menu->name);
		}

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assign('total'        , $total);

		$this->assignRef('items'     , $items);
		$this->assignRef('section'   , $section);
		$this->assignRef('categories', $categories);
		$this->assignRef('params'    , $params);
		$this->assignRef('user'      , $user);
		$this->assignRef('access'    , $access);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}

	function &getItem( $index = 0, &$params)
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getVar( 'task' );

		$linkOn		= null;
		$linkText	= null;
		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		// Get some parameters from global configuration
		$params->def('link_titles',	$contentConfig->get('link_titles'));
		$params->def('author',		!$contentConfig->get('hideAuthor'));
		$params->def('createdate',	!$contentConfig->get('hideCreateDate'));
		$params->def('modifydate',	!$contentConfig->get('hideModifyDate'));
		$params->def('print',		!$contentConfig->get('hidePrint'));
		$params->def('pdf',		!$contentConfig->get('hidePdf'));
		$params->def('email',		!$contentConfig->get('hideEmail'));
		$params->def('rating',		$contentConfig->get('vote'));
		$params->def('icons',		$contentConfig->get('icons'));
		$params->def('readmore',	$contentConfig->get('readmore'));
		$params->def('back_button', 	$contentConfig->get('back_button'));

		// Get some item specific parameters
		$params->def('image',			1);
		$params->def('section',			0);
		$params->def('section_link',	0);
		$params->def('category',		0);
		$params->def('category_link',	0);
		$params->def('introtext',		1);
		$params->def('pageclass_sfx',	'');
		$params->def('item_title',		1);
		$params->def('url',				1);
		$params->set('image',			1);

		$item =& $this->items[$index];

		// Process the content preparation plugins
		$item->text	= ampReplace($item->introtext);
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('readmore') && @ $item->readmore) || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($item->access <= $user->get('gid'))
				{
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;view=article&amp;id=".$item->slug."&amp;Itemid=".$Itemid);
					$linkText = JText::_('Read more...');
				}
				else
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}

		$item->readmore_link = $linkOn;
		$item->readmore_text = $linkText;

		$item->print_link = $mainframe->getCfg('live_site').'/index.php?option=com_content&amp;view=article&amp;id='.$item->id.'&amp;tmpl=component&amp;Itemid='.$Itemid;

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $item, & $params,0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $item, & $params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $item, & $params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		return $item;
	}

	function getIcon($type, $attribs = array())
	{
		 global $Itemid, $mainframe;

		$url  = '';
		$text = '';

		$article = $this->item;

		switch($type)
		{
			case 'pdf' :
			{
				$url   = 'index.php?option=com_content&amp;view=article&amp;id='.$article->id.'&amp;format=pdf';
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

				// checks template image directory for image, if non found default are loaded
				if ($this->params->get('icons')) {
					$text = JAdminMenus::ImageCheck('pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
				} else {
					$text = JText::_('PDF').'&nbsp;';
				}

				$attribs['title']   = '"'.JText::_( 'PDF' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'print' :
			{
				$url    = 'index.php?option=com_content&amp;view=article&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;tmpl=component&amp;page='.@ $this->request->limitstart;
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

				// checks template image directory for image, if non found default are loaded
				if ( $this->params->get( 'icons' ) ) {
					$text = JAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
				} else {
					$text = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
				}

				$attribs['title']   = '"'.JText::_( 'Print' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'email' :
			{
				$url   = 'index.php?option=com_mailto&amp;tmpl=component&amp;link='.urlencode( JRequest::getURI());
				$status = 'width=400,height=300,menubar=yes,resizable=yes';

				if ($this->params->get('icons')) 	{
					$text = JAdminMenus::ImageCheck('emailButton.png', '/images/M_images/', NULL, NULL, JText::_('Email'), JText::_('Email'));
				} else {
					$text = '&nbsp;'.JText::_('Email');
				}

				$attribs['title']   = '"'.JText::_( 'Email ' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'edit' :
			{
				if ($this->params->get('popup')) {
					return;
				}
				if ($article->state < 0) {
					return;
				}
				if (!$this->access->canEdit && !($this->access->canEditOwn && $article->created_by == $this->user->get('id'))) {
					return;
				}

				JCommonHTML::loadOverlib();

				$url = 'index.php?option=com_content&amp;task=edit&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;Returnid='.$Itemid;
				$text = JAdminMenus::ImageCheck('edit.png', '/images/M_images/', NULL, NULL, JText::_('Edit'), JText::_('Edit'). $article->id );

				if ($article->state == 0) {
					$overlib = JText::_('Unpublished');
				} else {
					$overlib = JText::_('Published');
				}
				$date = JHTML::Date($article->created);
				$author = $article->created_by_alias ? $article->created_by_alias : $article->author;

				$overlib .= '<br />';
				$overlib .= $article->groups;
				$overlib .= '<br />';
				$overlib .= $date;
				$overlib .= '<br />';
				$overlib .= $author;

				$attribs['onmouseover'] = "\"return overlib('".$overlib."', CAPTION, '".JText::_( 'Edit Item' )."', BELOW, RIGHT)\"";
				$attribs['onmouseover'] = "\"return nd();\"";

			} break;
		}

		return JHTML::Link($url, $text, $attribs);
	}
}
?>