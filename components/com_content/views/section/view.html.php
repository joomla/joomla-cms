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
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewSection extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();

		// Get the menu item object
		$menus = &JMenu::getInstance();
		$menu  = $menus->getActive();

		// Get the page/component configuration
		$params = &$mainframe->getPageParameters('com_content');

		// Request variables
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		//parameters
		$intro		= $params->def('num_intro_articles', 	4);
		$leading	= $params->def('num_leading_articles', 	1);
		$links		= $params->def('num_links', 			4);

		$limit	= $intro + $leading + $links;
		JRequest::setVar('limit', $limit);

		// Get some data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$categories	= & $this->get( 'Categories' );
		$section	= & $this->get( 'Section' );

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		//add alternate feed link
		$link	= 'index.php?option=com_content&view=section&format=feed&id='.$section->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		// Set the page title
		if (!empty ($menu->name)) {
			$mainframe->setPageTitle($menu->name);
		}

		for($i = 0; $i < count($categories); $i++)
		{
			$category =& $categories[$i];
			$category->link = JRoute::_('index.php?view=category&id='.$category->slug);
		}

		$params->def('page_title', $menu->name);

		if ($total == 0) {
			$params->set('show_categories', false);
		}


		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assign('total',			$total);

		$this->assignRef('items',		$items);
		$this->assignRef('section',		$section);
		$this->assignRef('categories',	$categories);
		$this->assignRef('params',		$params);
		$this->assignRef('user',		$user);
		$this->assignRef('access',		$access);
		$this->assignRef('pagination',	$pagination);

		//Load the html helper for the view
		$this->loadHelper('html');

		parent::display($tpl);
	}

	function &getItem( $index = 0, &$params)
	{
		global $mainframe;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getVar( 'task' );

		$linkOn		= null;
		$linkText	= null;

		// Get the page/component configuration
		$state  = &$this->get('State');
		$pparams = &$state->get('parameters.menu');
		if (!is_object($pparams)) {
			$pparams = &JComponentHelper::getParams('com_content');
		}

		// Handle global overides for some article parameters if set
		$params->def('link_titles',			$pparams->get('link_titles'));
		$params->def('show_author',			$pparams->get('show_author'));
		$params->def('show_create_date',	$pparams->get('show_create_date'));
		$params->def('show_modify_date',	$pparams->get('show_modify_date'));
		$params->def('show_print_icon',		$pparams->get('show_print_icon'));
		$params->def('show_pdf_icon',		$pparams->get('show_pdf_icon'));
		$params->def('show_email_icon',		$pparams->get('show_email_icon'));
		$params->def('show_vote',			$pparams->get('show_vote'));
		$params->def('show_icons',			$pparams->get('show_icons'));
		$params->def('show_readmore',		$pparams->get('show_readmore'));
		$params->def('show_intro',			$pparams->get('show_intro'));
		$params->def('show_section',		$pparams->get('show_section'));
		$params->def('show_category',		$pparams->get('show_category'));
		$params->def('link_section',		$pparams->get('link_section'));
		$params->def('link_category',		$pparams->get('link_category'));

		$item =& $this->items[$index];
		$item->text = $item->introtext;

		// Process the content preparation plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('show_readmore') && @ $item->readmore) || $params->get('link_titles'))
		{
			// checks if the item is a public or registered/special item
			if ($item->access <= $user->get('aid', 0))
			{
				$linkOn = JRoute::_("index.php?view=article&id=".$item->slug);
				$linkText = JText::_('Read more...');
			}
			else
			{
				$linkOn = JRoute::_("index.php?option=com_user&task=register");
				$linkText = JText::_('Register to read more...');
			}
		}

		$item->readmore_link = $linkOn;
		$item->readmore_text = $linkText;

		$item->print_link = $mainframe->getCfg('live_site').'/index.php?option=com_content&view=article&id='.$item->id.'&tmpl=component';

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
