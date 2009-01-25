<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
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
			$items[$i]->link = JRoute::_($items[$i]->link);
		}

		if ($params->get('showHome'))
		{
			$item = new stdClass();
			$item->name = $params->get('homeText', JText::_('Home'));
			$item->link = JURI::base();
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

		$lang =& JFactory::getLanguage();

		/**
	 	* If a custom separator has not been provided we try to load a template
	 	* specific one first, and if that is not present we load the default separator
	 	*/
		if ($custom == null) {
			if($lang->isRTL()){
				$_separator = JHtml::_('image.site', 'arrow_rtl.png');
			}
			else{
				$_separator = JHtml::_('image.site', 'arrow.png');
			}
		} else {
			$_separator = $custom;
		}
		return $_separator;
	}
}