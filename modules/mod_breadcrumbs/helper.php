<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

class modBreadCrumbsHelper
{
	function getList(&$params)
	{
		// Get the PathWay object from the application
		$app		= &JFactory::getApplication();
		$pathway	= &$app->getPathway();
		$items		= $pathway->getPathWay();

		$count = count($items);
		for ($i = 0; $i < $count; $i ++)
		{
			$items[$i]->name = stripslashes(htmlspecialchars($items[$i]->name));
			$items[$i]->link = JRoute::_($items[$i]->link);
		}

		if ($params->get('showHome', 1))
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
		$lang = &JFactory::getLanguage();

		// If a custom separator has not been provided we try to load a template
	 	// specific one first, and if that is not present we load the default separator
		if ($custom == null) {
			if ($lang->isRTL()){
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