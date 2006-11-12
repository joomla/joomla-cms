<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewCategories extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid, $option;

		$db		 	= & JFactory::getDBO();
		$user 		= & JFactory::getUser();
		$pathway 	= & $mainframe->getPathWay();
		$gid		= $user->get('gid');

		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_('News Feeds'));

		// Load the menu object and parameters
		$menus = &JMenu::getInstance();
		$menu  = $menus->getItem($Itemid);

		// Parameters
		$params = new JParameter($menu->params);
		$params->def( 'page_title', 		1 );
		$params->def( 'header', 			$menu->name );
		$params->def( 'pageclass_sfx', 		'' );
		$params->def( 'headings', 			1 );
		$params->def( 'description_text', 	'' );
		$params->def( 'image', 				-1 );
		$params->def( 'image_align', 		'right' );
		$params->def( 'other_cat_section', 	1 );
		// Category List Display control
		$params->def( 'other_cat', 			1 );
		$params->def( 'cat_description', 	1 );
		$params->def( 'cat_items', 			1 );
		// Table Display control
		$params->def( 'headings', 			1 );
		$params->def( 'name',				1 );
		$params->def( 'articles', 			1 );
		$params->def( 'link', 				1 );
		// pagination parameters
		$params->def('display', 			1 );
		$params->def('display_num', 		$mainframe->getCfg('list_limit'));

		// Handle the type
		$params->set( 'type', 'section' );

		/* Query to retrieve all categories that belong under the contacts section and that are published. */
		$query = "SELECT cc.*, a.catid, COUNT(a.id) AS numlinks"
			. "\n FROM #__categories AS cc"
			. "\n LEFT JOIN #__newsfeeds AS a ON a.catid = cc.id"
			. "\n WHERE a.published = 1"
			. "\n AND cc.section = 'com_newsfeeds'"
			. "\n AND cc.published = 1"
			. "\n AND cc.access <= $gid"
			. "\n GROUP BY cc.id"
			. "\n ORDER BY cc.ordering"
		;
		$db->setQuery( $query );
		$categories = $db->loadObjectList();

		for($i = 0; $i < count($categories); $i++)
		{
			$category =& $categories[$i];
			$category->link = sefRelToAbs('index.php?option=com_newsfeeds&amp;view=category&amp;catid='. $category->catid .'&amp;Itemid='. $Itemid);
		}
		// Define image tag attributes
		if ($params->get('image') != -1)
		{
			$attribs['align'] = '"'. $params->get('image_align').'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$image = JHTML::Image('/images/stories/'.$params->get('image'), JText::_('NEWS_FEEDS'), $attribs);
		}

		$this->assignRef('image'     , $image);
		$this->assignRef('params'    , $params);
		$this->assignRef('categories', $categories);

		parent::display($tpl);
	}
}
?>