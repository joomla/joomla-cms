<?php
/**
 * version $Id$
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

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
		$mainframe = JFactory::getApplication();

		$pathway 	= & $mainframe->getPathway();
		$document	= & JFactory::getDocument();

		// Get the parameters of the active menu item
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		$params	= &$mainframe->getParams();

		$category	= $this->get('category');
		$items		= $this->get('data');
		$total		= $this->get('total');
		$pagination	= &$this->get('pagination');

		// Set page title
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

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
		$pathwaycat = $category;
		$path = array();
		if (is_object($menu) && $menu->query['id'] != $category->id)
		{
			$path[] = array($pathwaycat->title);
			$pathwaycat = $pathwaycat->getParent();
			while($pathwaycat->id != $menu->query['id'])
			{
				$path[] = array($pathwaycat->title, $pathwaycat->slug);
				$pathwaycat = $pathwaycat->getParent();	
			}
			$path = array_reverse($path);
			foreach($path as $element)
			{
				if (isset($element[1]))
				{
					$pathway->addItem($element[0], 'index.php?option=com_newsfeeds&view=category&id='.$element[1]);
				} else {
					$pathway->addItem($element[0], '');
				}
			}
		}
		
		// Prepare category description
		$category->description = JHtml::_('content.prepare', $category->description);

		$k = 0;
		for ($i = 0; $i <  count($items); $i++)
		{
			$item = &$items[$i];

			$item->link = JRoute::_('index.php?view=newsfeed&catid='.$category->slug.'&id='. $item->slug);

			$item->odd		= $k;
			$item->count	= $i;
			$k = 1 - $k;
		}

		// Define image tag attributes
		if (!empty ($category->image))
		{
			$attribs['align'] = $category->image_position;
			$attribs['hspace'] = 6;

			// Use the static HTML library to build the image tag
			$image = JHtml::_('image', 'images/'.$category->image, JText::_('NEWS_FEEDS'), $attribs);
		}
		
		$children = $category->getChildren();
		foreach($children as &$child)
		{
			$child->link = JRoute::_('index.php?option=com_newsfeeds&view=category&id='.$child->slug); 
		}
		$this->assignRef('image',		$image);
		$this->assignRef('params',		$params);
		$this->assignRef('items',		$items);
		$this->assignRef('category',	$category);
		$this->assignRef('children', 	$children);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}
?>
