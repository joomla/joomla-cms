<?php
/**
 * @version $Id: content.php 2851 2006-03-20 21:45:20Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// require the component helper 
require_once (JApplicationHelper::getPath('helper', 'com_content'));

/**
 * Content Component Section Model
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentSection
{

	/**
	 * Method to build data for displaying a content section
	 *
	 * @static
	 * @since 1.0
	 */
	function getSectionData()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db				= & $mainframe->getDBO();
		$user			= & $mainframe->getUser();
		$noauth		= !$mainframe->getCfg('shownoauth');
		$now			= $mainframe->get('requestTime');
		$nullDate		= $db->getNullDate();
		$gid				= $user->get('gid');
		$id				= JRequest::getVar('id', 0, '', 'int');

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Load the section data model
		$section = & JModel::getInstance('section', $db);
		$section->load($id);

		/*
		Check if section is published
		*/
		if (!$section->published)
		{
			JError::raiseError( 404, JText::_("Resource Not Found") );
		}
		/*
		* check whether section access level allows access
		*/
		if ($section->access > $gid)
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}

		/*
		 * Build menu parameters
		 */
		if ($Itemid)
		{
			$menu = JMenu::getInstance();
			$menu = $menu->getItem($Itemid);
			$params = new JParameter($menu->params);
		}
		else
		{
			$menu = null;
			$params = new JParameter();
		}

		// Set the display type parameter
		$params->set('type', 'section');

		// Set some parameter defaults
		$params->def('page_title', 1);
		$params->def('pageclass_sfx', '');
		$params->def('other_cat_section', 1);
		$params->def('empty_cat_section', 0);
		$params->def('other_cat', 1);
		$params->def('empty_cat', 0);
		$params->def('cat_items', 1);
		$params->def('cat_description', 1);
		$params->def('back_button', $mainframe->getCfg('back_button'));
		$params->def('pageclass_sfx', '');

		// Ordering control
		$orderby = $params->get('orderby', '');
		$orderby = JContentHelper::orderbySecondary($orderby);

		// Handle the access permissions part of the main database query
		if ($access->canEdit)
		{
			$xwhere = '';
			$xwhere2 = "\n AND b.state >= 0";
		}
		else
		{
			$xwhere = "\n AND a.published = 1";
			$xwhere2 = "\n AND b.state = 1" .
					"\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )" .
					"\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )";
		}

		// Determine whether to show/hide the empty categories and sections
		$empty = null;
		$empty_sec = null;
		if ($params->get('type') == 'category')
		{
			// show/hide empty categories
			if (!$params->get('empty_cat'))
			{
				$empty = "\n HAVING numitems > 0";
			}
		}
		if ($params->get('type') == 'section')
		{
			// show/hide empty categories in section
			if (!$params->get('empty_cat_section'))
			{
				$empty_sec = "\n HAVING numitems > 0";
			}
		}

		// Handle the access permissions
		$access_check = null;
		if ($noauth)
		{
			$access_check = "\n AND a.access <= $gid";
		}

		// Query of categories within section
		$query = "SELECT a.*, COUNT( b.id ) AS numitems" .
				"\n FROM #__categories AS a" .
				"\n LEFT JOIN #__content AS b ON b.catid = a.id".
				$xwhere2 .
				"\n WHERE a.section = '$section->id'".
				$xwhere.
				$access_check .
				"\n GROUP BY a.id".$empty.$empty_sec .
				"\n ORDER BY $orderby";
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		/*
		 * Lets set the page title
		 */
		if (!empty ($menu->name))
		{
			$mainframe->setPageTitle($menu->name);
		}

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $mainframe->getPathWay();
		$breadcrumbs->addItem($section->title, '');

		$cache = JFactory::getCache('com_content');
		$cache->call('JContentViewHTML::showSection', $section, $categories, $params);
	}
}

?>