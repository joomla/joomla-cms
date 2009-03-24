<?php
/**
 * @version		$Id: category.php 11653 2009-03-08 11:11:10Z hackwar $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * Content Component Category Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.6
 */
abstract class ContentHelperCategory
{
	public static $_categories = NULL;
	
	function getCategory($id)
	{
		if(!isset(self::$_categories[(int)$id]))
		{
			ContentHelperCategory::_loadCategories((int)$id, true);
		}
		if(isset(self::$_categories[(int)$id]))
		{
			return self::$_categories[(int)$id];
		} else {
			return false;
		}
	}
	
	function getCategories($rootid = 0)
	{
		if(!isset(self::$_categories[$rootid]))
		{
			ContentHelperCategory::_loadCategories($rootid);
		}
		if($rootid == 0)
		{
			return self::$_categories;
		}
		if(isset(self::$_categories[$rootid]))
		{
			return self::$_categories[$rootid]->children;
		} else {
			return false;
		}
	}

	function _loadCategories($id, $root = false)
	{
		$db	=& JFactory::getDBO();
		$user =& JFactory::getUser();
		if($root || true)
		{
			$subquery = 'SELECT c.id, c.lft, c.rgt'.
				' FROM jos_categories AS c'.
				' JOIN jos_categories AS cp ON cp.lft >= c.lft AND c.rgt >= cp.rgt'.
				' WHERE c.extension = \'com_content\''.
				' AND cp.id = '.$id.' AND c.parent_id = 0';
		} else {
			$subquery = 'SELECT lft, rgt FROM #__categories WHERE id = '.$id;
		}
		$query = 'SELECT c.*, COUNT( b.id ) AS numitems, ' .
			' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug'.
			' FROM #__categories AS c' .
			' LEFT JOIN #__content AS b ON b.catid = c.id '.
			' JOIN ('.$subquery.') AS cp ON c.lft >= cp.lft AND c.rgt <= cp.rgt'.
			' WHERE c.extension = \'com_content\''.
			//' AND c.access IN ('.implode(',', $user->authorisedLevels()).')'.
			' GROUP BY c.id'.
			' ORDER BY c.lft';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$categories = array();
		foreach($results as $result)
		{
			$result->children = array();
			$categories[$result->id] = $result;
			if($result->parent_id > 0)
			{
				$categories[$result->parent_id]->children[$result->id] = &$categories[$result->id];
				$categories[$result->id]->parent = &$categories[$result->parent_id]; 
			}
		}
		self::$_categories = $categories; 
	}
}