<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modBreadCrumbsHelper
{
	function getList(&$params)
	{
		global $mainframe;

		// Get the PathWay object from the application
		$pathway =& $mainframe->getPathway();
		$items   = $pathway->getPathWay();

		$count = count($items);
		for ($i = 0; $i < $count; $i ++)
		{
			$items[$i]->name = stripslashes(htmlspecialchars($items[$i]->name));
			$items[$i]->link = '<a href="'.JRoute::_($items[$i]->link).'" class="pathway">'.$items[$i]->name.'</a>';
		}

		if ($params->get('showHome'))
		{
			$item = new stdClass();
			$item->name = $params->get('homeText', JText::_('Home'));
			$item->link = '<a href="'.JURI::base().'" class="pathway">'.$item->name.'</a>';
			array_unshift($items, $item);
		}

		return $items;
	}

	/**
 	 * Set the breadcrumbs separator for the breadcrumbs display.
 	 *
 	 * @param	string	$custom	Custom xhtml complient string to separate the
 	 * items of the breadcrumbs
 	 * @return	string	Separator string
 	 * @since	1.5
 	 */
	function setSeparator($custom = null)
	{
		global $mainframe;
		
		/**
	 	* If a custom separator has not been provided we try to load a template
	 	* specific one first, and if that is not present we load the default separator
	 	*/
		if ($custom == null) {
			$_separator = JHTML::_('image.site', 'arrow.png');
		} else {
			$_separator = $custom;
		}
		return $_separator;
	}
}