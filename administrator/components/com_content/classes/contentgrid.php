<?php
/**
 * @package		Bricks
 * @copyright	Copyright (C) 2008 R Crawford.
 * @license		GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package 	Bricks
 */
class JHTMLContentGrid
{
	function author( $name, $filter_authorid )
	{
		$db			=& JFactory::getDBO();
		$query = 'SELECT c.created_by, u.name' .
				' FROM #__content AS c' .
				' INNER JOIN #__sections AS s ON s.id = c.sectionid' .
				' LEFT JOIN #__users AS u ON u.id = c.created_by' .
				' WHERE c.state <> -1' .
				' AND c.state <> -2' .
				' GROUP BY u.name' .
				' ORDER BY u.name';
		$authors[] = JHTML::_('select.option', '0', '- '.JText::_('Select Author').' -', 'created_by', 'name');
		$db->setQuery($query);
		$authors = array_merge($authors, $db->loadObjectList());
		return JHTML::_('select.genericlist',  $authors, $name, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'created_by', 'name', $filter_authorid);
	}

	function category( $name, $filter_catid, $filter_sectionid = null )
	{
		$db			=& JFactory::getDBO();
		$cat_filter = null;
		if (($filter_sectionid !== null) && ($filter_sectionid >= 0)) {
			$cat_filter = ' WHERE cc.section = '. (int) $filter_sectionid;
		}
		$query = 'SELECT cc.id AS value, cc.title AS text, section' .
				' FROM #__categories AS cc' .
				' INNER JOIN #__sections AS s ON s.id = cc.section ' .
				$cat_filter .
				' ORDER BY s.ordering, cc.ordering';
		$categories[] = JHTML::_('select.option', '0', '- '.JText::_('Select Category').' -');
		$db->setQuery($query);
		$categories = array_merge($categories, $db->loadObjectList());
		return JHTML::_('select.genericlist',  $categories, $name, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_catid);
	}
}
