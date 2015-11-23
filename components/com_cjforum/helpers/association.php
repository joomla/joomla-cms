<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjForumHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

abstract class CjForumHelperAssociation extends CategoryHelperAssociation
{
	public static function getAssociations ($id = 0, $view = null)
	{
		jimport('helper.route', JPATH_COMPONENT_SITE);
		
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$view = is_null($view) ? $jinput->get('view') : $view;
		$id = empty($id) ? $jinput->getInt('id') : $id;
		
		if ($view == 'topic')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_cjforum', '#__cjforum_topics', 'com_cjforum.item', $id);
				
				$return = array();
				
				foreach ($associations as $tag => $item)
				{
					$return[$tag] = CjForumHelperRoute::getTopicRoute($item->id, $item->catid, $item->language);
				}
				
				return $return;
			}
		}
		
		if ($view == 'category' || $view == 'categories')
		{
			return self::getCategoryAssociations($id, 'com_cjforum');
		}
		
		return array();
	}
}
