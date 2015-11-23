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

class CjForumModelMigrate extends JModelLegacy
{
	protected function migrate($step = 0)
	{
		return 1;
	}
	
	protected function analyse()
	{
		return true;
	}
	
	protected function syncTopics()
	{
		return true;
	}
	
	public function rebuildAssets()
	{
		CJLib::import('corejoomla.nestedtree.core');
		$db = JFactory::getDbo();
// 		$tree = new CjNestedTree($db, '#__assets', array(), 'id', 'title', 'lft', 'rgt', 'parent_id', 'level');
		
		$this->shapeshift(1, 1, true);
// 		$tree->udpate_level();
	}
	
	/** credit: http://www.sitepoint.com/forums/showthread.php?320444-Adjacency-list-table-to-Nested-Set-table-conversion-algorithm&s=0a186a3e9aa0b43c2c9f00bdad6575e5&p=4789281&viewfull=1#post4789281 */
	function shapeshift($lft = 1, $father = 0, $isroot=false)
	{
		$db = JFactory::getDBO();
		$tbl_from = '#__assets';
		$tbl_to   = '#__assets';
	
		$query = "select * from $tbl_from where id = $father";
		$db->setQuery($query);
		$cat = $db->loadObject();
		$rgt = $lft+1;
		
		$query = "select * from $tbl_from where parent_id = $father";
		$db->setQuery($query);
		$sibs = $db->loadObjectList();
		
		foreach ($sibs as $sib)
		{
			$rgt = $this->shapeshift($rgt,$sib->id);
		}
		
		if($cat->id)
		{
			$query = "update $tbl_to set lft = $lft,rgt = $rgt where id = $cat->id";
			$db->setQuery($query);
			$db->Query();
		}
		
		$rgt+=1;
		
		return $rgt;
	}
}