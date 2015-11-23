<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  mod_cjforumcategories
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_ROOT.'/components/com_cjforum/helpers');

class CjForumCategoriesHelper 
{
	public static function getCategories($nodes, $excluded)
	{
		$content = '<ul class="cat-list">';
		foreach($nodes as $node)
		{
			if(in_array($node->id, $excluded)) continue;
			$value = CjLibUtils::escape($node->title);
	
			if(!empty($node->numitems))
			{
				$value = $value . ' <span class="text-muted">(' . $node->numitems . ')</span>';
			}
	
			$content = $content . '<li rel="'.$node->id.'">';
			$content = $content . JHtml::link(JRoute::_(CjForumHelperRoute::getCategoryRoute($node->id)), $value);
			$children = $node->getChildren();
			
			if(!empty($children)) 
			{
				$content = $content . CjForumCategoriesHelper::getCategories($children, $excluded);
			}
	
			$content = $content . '</li>';
		}
	
		$content = $content . '</ul>';
	
		return $content;
	}
}