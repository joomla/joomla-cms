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
class WeblinksViewCategories extends JView
{
	function display( $tpl = null)
	{
		global $mainframe;

		$document =& JFactory::getDocument();

		$categories	=& $this->get('data');
		$total		=& $this->get('total');
		$state		=& $this->get('state');

		// Get the page/component configuration
		$params = &$mainframe->getParams();

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'Web Links' ));
			}
		} else {
			$params->set('page_title',	JText::_( 'Web Links' ));
		}

		$document->setTitle( $params->get( 'page_title' ) );

		// Set some defaults if not set for params
		$params->def('comp_description', JText::_('WEBLINKS_DESC'));

		// Define image tag attributes
		if ($params->get('image') != -1)
		{
			if($params->get('image_align')!="")
				$attribs['align'] = $params->get('image_align');
			else
				$attribs['align'] = '';
			$attribs['hspace'] = 6;

			// Use the static HTML library to build the image tag
			$image = JHtml::_('image', 'images/stories/'.$params->get('image'), JText::_('Web Links'), $attribs);
		}

		for($i = 0; $i < count($categories); $i++)
		{
			$category =& $categories[$i];
			$category->link = JRoute::_('index.php?option=com_weblinks&view=category&id='. $category->slug);

			// Prepare category description
			$category->description = JHtml::_('content.prepare', $category->description);
		}

		$this->assignRef('image',		$image);
		$this->assignRef('params',		$params);
		$this->assignRef('categories',	$categories);

		parent::display($tpl);
	}
}
?>
