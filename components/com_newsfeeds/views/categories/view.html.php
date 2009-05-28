<?php
/**
* version $Id$
 * @package		Joomla.Site
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
*
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewCategories extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		// Load the menu object and parameters
		$params	= &$mainframe->getParams();

		$categories = &$this->get('data');

		for($i = 0; $i < count($categories); $i++)
		{
			$category = &$categories[$i];
			$category->link = JRoute::_('index.php?view=category&id='. $category->slug);

			// Prepare category description
			$category->description = JHtml::_('content.prepare', $category->description);
		}
		// Define image tag attributes
		if ($params->get('image') != -1)
		{
			$attribs['align'] = $params->get('image_align');
			$attribs['hspace'] = 6;

			// Use the static HTML library to build the image tag

			$image = JHtml::_('image', 'images/stories/'.$params->get('image'), JText::_('NEWS_FEEDS'), $attribs);
		}

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	JText::_('Newsfeeds'));
			}
		} else {
			$params->set('page_title',	JText::_('Newsfeeds'));
		}
		$document	= &JFactory::getDocument();
		$document->setTitle($params->get('page_title'));

		$this->assignRef('image',		$image);
		$this->assignRef('params',		$params);
		$this->assignRef('categories',	$categories);

		parent::display($tpl);
	}
}
?>
