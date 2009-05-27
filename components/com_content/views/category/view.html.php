<?php
/**
 * @version		$Id: view.html.php 11713 2009-03-27 09:18:26Z willebil $
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

require_once (JPATH_COMPONENT.DS.'view.php');

/**
 * HTML View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewCategory extends ContentView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Initialize some variables
		$user		= &JFactory::getUser();
		$uri 		= &JFactory::getURI();
		$document	= &JFactory::getDocument();
		$pathway	= &$mainframe->getPathway();

		// Get the menu item object
		$menus = &JSite::getMenu();
		$menu  = $menus->getActive();

		// Get the page/component configuration
		$params = clone($mainframe->getParams('com_content'));

		// Request variables
		$layout     = JRequest::getCmd('layout');
		$task		= JRequest::getCmd('task');

		// Parameters
		$params->def('num_leading_articles', 	1);
		$params->def('num_intro_articles', 		4);
		$params->def('num_columns',				2);
		$params->def('num_links', 				4);
		$params->def('show_headings', 			1);
		$params->def('show_pagination',			2);
		$params->def('show_pagination_results',	1);
		$params->def('show_pagination_limit',	1);
		$params->def('filter',					1);
		if (($params->def('filter_type', 'title') != 'hits') && ($params->def('filter_type', 'title') != 'author')) {
			$params->set('filter_type', 'title');
		}

		$intro		= $params->get('num_intro_articles');
		$leading	= $params->get('num_leading_articles');
		$links		= $params->get('num_links');

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		if ($layout == 'blog') {
			$default_limit = $intro + $leading + $links;
		} else {
			$params->def('display_num', $mainframe->getCfg('list_limit'));
			$default_limit = $params->get('display_num');
		}
		$limit = $mainframe->getUserStateFromRequest('com_content.'.$this->getLayout().'.limit', 'limit', $default_limit, 'int');

		JRequest::setVar('limit', (int) $limit);

		$contentConfig = &JComponentHelper::getParams('com_content');
		$params->def('show_page_title', 	$contentConfig->get('show_title'));

		// Get some data from the model
		$items		= & $this->get('Data');
		$total		= & $this->get('Total');
		$category	= & $this->get('Category');

		//add alternate feed link
		if ($params->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('com_content.article.edit_article');
		$access->canEditOwn		= $user->authorize('com_content.article.edit_own');
		$access->canPublish		= $user->authorize('com_content.article.publish');

		// Set page title per category
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	$category->title);
			}
		} else {
			$params->set('page_title',	$category->title);
		}
		$document->setTitle($params->get('page_title'));

		//set breadcrumbs
		if (is_object($menu) && $menu->query['view'] != 'category') {
			$pathway->addItem($category->title, '');
		}

		// Prepare category description
		$category->description = JHtml::_('content.prepare', $category->description);

		$params->def('date_format',	JText::_('DATE_FORMAT_LC1'));

		// Keep a copy for safe keeping this is soooooo dirty -- must deal with in a later version
		// @todo -- oh my god we need to find this reference issue in 1.6 :)
		$this->_params = $params->toArray();

		jimport('joomla.html.pagination');
		//In case we are in a blog view set the limit
		if ($layout == 'blog') {
			$pagination = new JPagination($total, $limitstart, $limit - $links);
		} else {
			$pagination = new JPagination($total, $limitstart, $limit);
		}

		$this->assign('total',		$total);
		$this->assign('action', 	str_replace('&', '&amp;', $uri->toString()));

		$this->assignRef('items',		$items);
		$this->assignRef('params',		$params);
		$this->assignRef('category',	$category);
		$this->assignRef('user',		$user);
		$this->assignRef('access',		$access);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}

	function &getItems()
	{
		global $mainframe;

		//create select lists
		$user	= &JFactory::getUser();
		$groups	= $user->authorisedLevels();
		$lists	= $this->_buildSortLists();

		if (!count($this->items))
		{
			$this->assign('lists',	$lists);
			$return = array();
			return $return;
		}

		//create paginatiion
		if ($lists['filter']) {
			$this->data->link .= '&amp;filter='.urlencode($lists['filter']);
		}

		$k = 0;
		$i = 0;
		foreach($this->items as $key => $item)
		{
			// checks if the item is a public or registered/special item
			if (in_array($item->access, $groups))
			{
				$item->link	= JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid));
				$item->readmore_register = false;
			}
			else
			{
				$item->link = JRoute::_('index.php?option=com_users&task=register');
				$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid), false);
				$fullURL = new JURI($item->link);
				$fullURL->setVar('return', base64_encode($returnURL));
				$item->link = $fullURL->toString();
				$item->readmore_register = true;
			}
			$item->created	= JHtml::_('date', $item->created, $this->params->get('date_format'));

			$item->odd		= $k;
			$item->count    = $i;

			$this->items[$key] = $item;
			$k = 1 - $k;
			$i++;
		}

		$this->assign('lists',	$lists);

		return $this->items;
	}

	function &getItem($index = 0, &$params)
	{
		global $mainframe;

		// Initialize some variables
		$user		= &JFactory::getUser();
		$groups		= $user->authorisedLevels();
		$dispatcher	= &JDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$item 		= &$this->items[$index];
		$item->text = $item->introtext;

		$category	= & $this->get('Category');
		$item->category = $category->title;
		$item->section  = $category->sectiontitle;

		// Get the page/component configuration and article parameters
		$item->params = clone($params);
		$aparams = new JParameter($item->attribs);

		// Merge article parameters into the page configuration
		$item->params->merge($aparams);

		// Process the content preparation plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $item->params, 0));

		// Build the link and text of the readmore button
		if (($item->params->get('show_readmore') && @ $item->readmore) || $item->params->get('link_titles'))
		{
			// checks if the item is a public or registered/special item
			if (in_array($item->access, $groups))
			{
				//$item->readmore_link = JRoute::_('index.php?view=article&catid='.$this->category->slug.'&id='.$item->slug);
				$item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid));
				$item->readmore_register = false;
			}
			else
			{
				$item->readmore_link = JRoute::_('index.php?option=com_users&view=login');
				$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid),false);
				$fullURL = new JURI($item->readmore_link);
				$fullURL->setVar('return', base64_encode($returnURL));
				$item->readmore_link = $fullURL->toString();
				$item->readmore_register = true;
			}
		}

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $item, & $item->params,0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $item, & $item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $item, & $item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		return $item;
	}

	function _buildSortLists()
	{
		// Table ordering values
		$filter				= JRequest::getString('filter');
		global $mainframe;
		$itemid = JRequest::getInt('id',0) . ':' . JRequest::getInt('Itemid',0);
		$filter_order  = $mainframe->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$lists['task']      = 'category';
		$lists['filter']    = $filter;
		$lists['order']     = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		return $lists;
	}
}
?>
