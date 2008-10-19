<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class WeblinksViewCategory extends JView
{
	function display( $tpl = null )
	{
		global $mainframe;

		// Initialize some variables
		$document	= &JFactory::getDocument();
		$uri 		= &JFactory::getURI();
		$pathway	= &$mainframe->getPathway();

		// Get the parameters of the active menu item
		$menus = &JSite::getMenu();
		$menu  = $menus->getActive();

		// Get some data from the model
		$items		= &$this->get('data' );
		$total		= &$this->get('total');
		$pagination	= &$this->get('pagination');
		$category	= &$this->get('category' );
		$state		= &$this->get('state');

		$model =& JModel::getInstance('categories', 'weblinksmodel');
		$categories =& $model->getData();

		// Get the page/component configuration
		$params = &$mainframe->getParams();

		$category->total = $total;

		// Add alternate feed link
		if($params->get('show_feed_link', 1) == 1)
		{
			$link	= '&view=category&id='.$category->slug.'&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title', $category->title);
			}
		} else {
			$params->set('page_title',	$category->title);
		}
		$document->setTitle( $params->get( 'page_title' ) );

		//set breadcrumbs
		if(is_object($menu) && $menu->query['view'] != 'category') {
			$pathway->addItem($category->title, '');
		}

		// Prepare category description
		$category->description = JHTML::_('content.prepare', $category->description);

		// table ordering
		$lists['order_Dir'] = $state->get('filter_order_dir');
		$lists['order'] = $state->get('filter_order');

		// Set some defaults if not set for params
		$params->def('comp_description', JText::_('WEBLINKS_DESC'));
		$params->def('show_numbers', '1');		// Default to "show"
		$params->def('show_report', '0');		// Default to "hide"
		$params->def('show_snapshot', '0');		// Default to "hide"
		$params->def('snapshot_width', '120');
		$params->def('snapshot_height', '90');

		// Define image tag attributes
		if (isset( $category->image ) && $category->image != '')
		{
			$attribs['align']  = $category->image_position;
			$attribs['hspace'] = 6;

			// Use the static HTML library to build the image tag
			$category->image = JHTML::_('image', 'images/stories/'.$category->image, JText::_('Web Links'), $attribs);
		}

		// icon in table display
		if ( $params->get( 'link_icons' ) <> -1 ) {
			$image = JHTML::_('image.site',  $params->get('link_icons', 'weblink.png'), '/images/M_images/', $params->get( 'weblink_icons' ), '/images/M_images/', 'Link' );
		}

		$source = $params->get('snapshot_source');
		$source_url = '';
		if ($source) {
			JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_weblinks'.DS.'models');
			$model =& JModel::getInstance('snapshotsources','WeblinksModel');
			$sites = $model->getData();
			foreach ($sites as $site) {
				if ($source == $site->name) $source_url = $site->url;
			}
		}

		$patterns = array('%u','%w','%h');
		$replacements = array(null, $params->get('snapshot_width'), $params->get('snapshot_height'));

		$k = 0;
		$count = count($items);
		for($i = 0; $i < $count; $i++)
		{
			$item =& $items[$i];

			$link = JRoute::_( 'index.php?view=weblink&catid='.$category->slug.'&id='. $item->slug);

			$item->report_link = JRoute::_('index.php?task=report&id='. $item->slug);
			if ($source_url) {
				$replacements[0] = $item->url;
				$url_snapshot = str_replace($patterns, $replacements, $source_url);
				$item->url_snapshot = JRoute::_($url_snapshot);
			}
			else {
				$item->url_snapshot = '';
				$params->set('show_snapshot', '0');
			}

			$menuclass = 'category'.$params->get( 'pageclass_sfx' );

			$itemParams = new JParameter($item->params);
			switch ($itemParams->get('target', $params->get('target')))
			{
				// cases are slightly different
				case 1:
					// open in a new window
					$item->link = '<a href="'. $link .'" target="_blank" class="'. $menuclass .'">'. $this->escape($item->title) .'</a>';
					break;

				case 2:
					// open in a popup window
					$item->link = "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">". $this->escape($item->title) ."</a>\n";
					break;

				default:
					// formerly case 2
					// open in parent window
					$item->link = '<a href="'. $link .'" class="'. $menuclass .'">'. $this->escape($item->title) .'</a>';
					break;
			}

			$item->image = $image;

			$item->odd		= $k;
			$item->count	= $i;
			$k = 1 - $k;
		}

		$count = count($categories);
		for($i = 0; $i < $count; $i++)
		{
			$cat =& $categories[$i];
			$cat->link = JRoute::_('index.php?option=com_weblinks&view=category&id='. $cat->slug);
		}

		$this->assignRef('lists',		$lists);
		$this->assignRef('params',		$params);
		$this->assignRef('category',	$category);
		$this->assignRef('categories', $categories);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->assign('action',	$uri->toString());

		parent::display($tpl);
	}
}
