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

require_once (JPATH_COMPONENT.DS.'view.php');

/**
 * Frontpage View class
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewFrontpage extends ContentView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Initialize variables
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();

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
		$items =& $this->get('data' );
		$total =& $this->get('total');

		// Create a user access object for the user
		$access				= new stdClass();
		$access->canEdit	= $user->authorize('com_content', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('com_content', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('com_content', 'publish', 'content', 'all');

		//add alternate feed link
		if($params->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		// Keep a copy for safe keeping this is soooooo dirty -- must deal with in a later version
		// @todo -- oh my god we need to find this reference issue in 1.6 :)
		$this->_params = $params->toArray();

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
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getCmd('task');

		$linkOn		= null;
		$linkText	= null;

		$item =& $this->items[$index];
		$item->text = $item->introtext;

		// Get the page/component configuration and article parameters
		$params = new JParameter('');
		$params->loadArray($this->_params);
		$aparams = new JParameter($item->attribs);

		// Merge article parameters into the page configuration
		$params->merge($aparams);

		// Process the content preparation plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('show_readmore') && @ $item->readmore) || $params->get('link_titles'))
		{
			// checks if the item is a public or registered/special item
			if ($item->access <= $user->get('aid', 0))
			{
				$linkOn = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid));
				$linkText = true;
			}
			else
			{
				$linkOn = JRoute::_("index.php?option=com_user&task=register");
				$linkText = false;
			}
		}

		$item->readmore_link = $linkOn;
		$item->readmore_text = $linkText;

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $item, & $params,0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $item, & $params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $item, & $params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		return $item;
	}
}
