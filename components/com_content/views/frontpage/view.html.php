<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

require_once (JPATH_COMPONENT.DS.'view.php');

/**
 * Frontpage View class
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewFrontpage extends ContentView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Initialize variables
		$user		= &JFactory::getUser();
		$document	= &JFactory::getDocument();

		// Request variables
		$id			= JRequest::getVar('id', null, '', 'int');
		$limit		= JRequest::getVar('limit', 5, '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Get the page/component configuration
		$params = &$mainframe->getParams();

		// parameters
		$intro			= $params->def('num_intro_articles',	4);
		$leading		= $params->def('num_leading_articles',	1);
		$links			= $params->def('num_links', 			4);

		$descrip		= $params->def('show_description', 		1);
		$descrip_image	= $params->def('show_description_image',1);

		$params->set('show_intro', 	1);

		$limit = $intro + $leading + $links;
		JRequest::setVar('limit', (int) $limit);

		//set data model
		$items = &$this->get('data');
		$total = &$this->get('total');

		// Create a user access object for the user
		$access				= new stdClass();
		$access->canEdit	= $user->authorize('com_content.article.edit_article');
		$access->canEditOwn	= $user->authorize('com_content.article.edit_own');
		$access->canPublish	= $user->authorize('com_content.article.publish');

		//add alternate feed link
		if ($params->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	 htmlspecialchars_decode($mainframe->getCfg('sitename')));
			}
		} else {
			$params->set('page_title',	 htmlspecialchars_decode($mainframe->getCfg('sitename')));
		}
		$document->setTitle($params->get('page_title'));

		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($total, $limitstart, $limit - $links);

		$this->assign('total',			$total);

		$this->assignRef('user',		$user);
		$this->assignRef('access',		$access);
		$this->assignRef('params',		$params);
		$this->assignRef('items',		$items);

		parent::display($tpl);
	}

	function &getItem($index = 0, &$params)
	{
		global $mainframe;

		// Initialize some variables
		$user		= &JFactory::getUser();
		$groups		= $user->authorisedLevels();
		$dispatcher	= &JDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getCmd('task');

		$linkOn		= null;
		$linkText	= null;

		$item = &$this->items[$index];
		$item->text = $item->introtext;

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
				$item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid));
				$item->readmore_register = false;
			}
			else
			{
				$item->readmore_link = JRoute::_("index.php?option=com_users&view=login");
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
}
