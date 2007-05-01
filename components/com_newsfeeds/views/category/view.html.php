<?php
/**
* version $Id$
* @package		Joomla
* @subpackage	Newsfeeds
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$pathway 	= & $mainframe->getPathWay();
		$document	= & JFactory::getDocument();

		// Get the paramaters of the active menu item
		$menus	= &JMenu::getInstance();
		$menu	= $menus->getActive();
		$params	= &$menus->getParams($menu->id);

		$category	= $this->get('category');
		$items		= $this->get('data');
		$total		= $this->get('total');
		$pagination	= &$this->get('pagination');

		// Parameters
		$params->def( 'page_title', $menu->name );

		// Set page title per category
		$document->setTitle( $menu->name. ' - ' .$category->title );

		//set breadcrumbs
		if($item->query['view'] != 'category')
		{
			$pathway->addItem($category->title, '');
		}

		$k = 0;
		for($i = 0; $i <  count($items); $i++)
		{
			$item =& $items[$i];

			$item->link = JRoute::_('index.php?option=com_newsfeeds&view=newsfeed&catid='.$category->slug.'&id='. $item->slug );

			$item->odd		= $k;
			$item->count	= $i;
			$k = 1 - $k;
		}

		// Define image tag attributes
		if (!empty ($category->image))
		{
			$attribs['align'] = '"'.$category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$image = JHTML::Image('/images/stories/'.$category->image, JText::_('NEWS_FEEDS'), $attribs);
		}

		$this->assignRef('image',		$image);
		$this->assignRef('params',		$params);
		$this->assignRef('items',		$items);
		$this->assignRef('category',	$category);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}
?>
