<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class modBreadCrumbsHelper
{
	public static function getList(&$params)
	{
		// Get the PathWay object from the application
		$app		= JFactory::getApplication();
		$pathway	= $app->getPathway();
		$items		= $pathway->getPathWay();

		$count = count($items);
		for ($i = 0; $i < $count; $i ++)
		{
			$items[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
			$items[$i]->link = JRoute::_($items[$i]->link);
		}

		if ($params->get('showHome', 1))
		{
			$item = new stdClass;
			$item->name = htmlspecialchars($params->get('homeText', JText::_('MOD_BREADCRUMBS_HOME')));
			$item->link = JRoute::_('index.php?Itemid='.$app->getMenu()->getDefault()->id);
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
	public static function setSeparator($custom = null)
	{
		$lang = JFactory::getLanguage();

		// If a custom separator has not been provided we try to load a template
		// specific one first, and if that is not present we load the default separator
		if ($custom == null) {
			if ($lang->isRTL()){
				$_separator = JHtml::_('image', 'system/arrow_rtl.png', null, null, true);
			}
			else{
				$_separator = JHtml::_('image', 'system/arrow.png', null, null, true);
			}
		} else {
			$_separator = htmlspecialchars($custom);
		}

		return $_separator;
	}
}
