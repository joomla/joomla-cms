<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksViewCategories extends JView
{
	function display( $tpl = null)
	{
		global $Itemid, $mainframe;
		
		$menus  = &JMenu::getInstance();
		$menu   = $menus->getItem($Itemid);
		
		$this->params->def('header', $menu->name);
		$this->params->def('pageclass_sfx', '');
		$this->params->def('headings', 1);
		$this->params->def('hits', $mainframe->getCfg('hits'));
		$this->params->def('item_description', 1);
		$this->params->def('other_cat_section', 1);
		$this->params->def('other_cat', 1);
		$this->params->def('description', 1);
		$this->params->def('description_text', JText::_('WEBLINKS_DESC'));
		$this->params->def('image', -1);
		$this->params->def('weblink_icons', '');
		$this->params->def('image_align', 'right');

		// Handle the type
		$this->params->set('type', 'section');
		
		// Define image tag attributes
		if ($this->params->get('image') != -1)
		{
			$attribs['align'] = '"'. $this->params->get('image_align').'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$this->image = JHTML::Image('/images/stories/'.$this->params->get('image'), JText::_('Web Links'), $attribs);
		}

		for($i = 0; $i < count($this->categories); $i++)
		{
			$category =& $this->categories[$i];
			$category->link = sefRelToAbs('index.php?option=com_weblinks&amp;task=category&amp;catid='. $category->catid .'&amp;Itemid='. $Itemid);
		}

		parent::display($tpl);
	}
}
?>