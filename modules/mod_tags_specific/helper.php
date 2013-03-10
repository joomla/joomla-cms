<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_specific
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_specific
 *
 * @package     Joomla.Site
 * @subpackage  mod_tags_specific
 * @since       3.1
 */
abstract class ModTagsSpecificHelper
{
	public static function getList($params)
	{
		$db        = JFactory::getDbo();
		$user      = JFactory::getUser();
		$groups    = implode(',', $user->getAuthorisedViewLevels());
		$maximum   = $params->get('maximum', 5);
		$tag_ids    = $params->get('tags',array()); 

		$tags = new JTags;
		$items = $tags->getTagItems($tag_ids);

		return $items;
	}
}
