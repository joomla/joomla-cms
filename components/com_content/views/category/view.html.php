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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$uri 		=& JFactory::getURI();
		$pathway	= & $mainframe->getPathWay();

		// Get the menu object of the active menu item
		$menu   =& JMenu::getInstance();
		$item   = $menu->getActive();
		$params = $menu->getParams($item->id);

		// Request variables
		$task 		= JRequest::getVar('task');
		$limit       = $mainframe->getUserStateFromRequest('com_content.limit', 'limit', $params->def('display_num', 0));
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// parameters
		$intro		= $params->def('intro', 	0);
		$leading	= $params->def('leading', 	0);
		$links		= $params->def('link', 		0);

		//In case we are in a blog view set the limit
		if($limit ==  0) $limit = $intro + $leading + $links;
		JRequest::setVar('limit', $limit);

		// Get some data from the model
		$items		= & $this->get( 'Data' );
		$total		= & $this->get( 'Total' );
		$category	= & $this->get( 'Category' );

		//add alternate feed link
		$link	= 'index.php?option=com_content&view=category&format=feed&id='.$category->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		//set breadcrumbs
		if($item->query['view'] != 'category') {
			$pathway->addItem($category->title, '');
		}

		$mainframe->setPageTitle($item->name);

		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$params->def('title',			1);
		$params->def('hits',			$contentConfig->get('hits'));
		$params->def('showAuthor',	$contentConfig->get('showAuthor'));
		$params->def('date',			$contentConfig->get('showCreateDate'));
		$params->def('date_format',	JText::_('DATE_FORMAT_LC'));
		$params->def('navigation',	2);
		$params->def('display',		1);
		$params->def('display_num',	$mainframe->getCfg('list_limit'));
		$params->def('empty_cat',		0);
		$params->def('cat_items',		1);
		$params->def('cat_description',0);
		$params->def('pageclass_sfx',	'');
		$params->def('headings',		1);
		$params->def('filter',		1);
		$params->def('filter_type',	'title');
		$params->set('intro_only', 	1);

		if ($params->def('page_title', 1)) {
			$params->def('header', $item->name);
		}

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assign('total',		$total);

		$this->assignRef('items',		$items);
		$this->assignRef('category',	$category);
		$this->assignRef('params',	$params);
		$this->assignRef('user',		$user);
		$this->assignRef('access',	$access);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}

	function &getItems()
	{
		global $mainframe;

		if (!count( $this->items ) ) {
			$return = array();
			return $return;
		}

		//create select lists
		$lists	= $this->_buildSortLists();

		//create paginatiion
		if ($lists['filter']) {
			$this->data->link .= '&amp;filter='.$lists['filter'];
		}

		$k = 0;
		for($i = 0; $i <  count($this->items); $i++)
		{
			$item =& $this->items[$i];

			$item->link		= JRoute::_('index.php?view=article&catid='.$this->category->slug.'&id='.$item->slug);
			$item->created	= JHTML::Date($item->created, $this->params->get('date_format'));

			$item->odd		= $k;
			$item->count	= $i;
			$k = 1 - $k;
		}

		$this->assign('lists',	$lists);

		return $this->items;
	}

	function &getItem($index = 0, $params)
	{
		global $mainframe;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getVar( 'task' );

		$linkOn		= null;
		$linkText	= null;

		// Get some parameters from global configuration
		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$params->def('link_titles',	$contentConfig->get('link_titles'));
		$params->def('showAuthor',	$contentConfig->get('showAuthor'));
		$params->def('createdate',	$contentConfig->get('showCreateDate'));
		$params->def('modifydate',	$contentConfig->get('showModifyDate'));
		$params->def('print',		$contentConfig->get('showPrint'));
		$params->def('pdf',		$contentConfig->get('showPdf'));
		$params->def('email',		$contentConfig->get('showEmail'));
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

		$item 		=& $this->items[$index];
		$item->text = $item->introtext;

		// Process the content preparation plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('readmore') && @ $item->readmore) || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($item->access <= $user->get('aid', 0))
				{
					$linkOn = JRoute::_('index.php?view=article&catid='.$this->category->slug.'&id='.$item->slug);
					$linkText = JText::_('Read more...');
				}
				else
				{
					$linkOn = JRoute::_("index.php?option=com_user&task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}

		$item->readmore_link = $linkOn;
		$item->readmore_text = $linkText;

		$item->print_link = $mainframe->getCfg('live_site').'/index.php?option=com_content&view=article&id='.$item->slug.'&tmpl=component';

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $item, & $params,0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $item, & $params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $item, & $params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		return $item;
	}

	function _buildSortLists()
	{
		// Table ordering values
		$filter				= JRequest::getVar('filter');
		$filter_order		= JRequest::getVar('filter_order');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir');

		$lists['task'] = 'category';
		$lists['filter'] = $filter;

		if ($filter_order_Dir == 'DESC') {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}

		$lists['order'] = $filter_order;

		return $lists;
	}
}
?>