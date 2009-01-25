<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage		HTML
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlList
{
	/**
	 * Use JHtml::_('acl.assetgroups', $selected) instead
	 * @deprecated
	 */
	public static function accesslevel(&$row)
	{
		return JHtml::_('acl.assetgroups', $row->access);
	}

	/**
	* Build the select list to choose an image
	*/
	public static function images($name, $active = NULL, $javascript = NULL, $directory = NULL, $extensions =  "bmp|gif|jpg|png")
	{
		if (!$directory) {
			$directory = '/images/';
		}

		if (!$javascript) {
			$javascript = "onchange=\"javascript:if (document.forms.adminForm." . $name . ".options[selectedIndex].value!='') {document.imagelib.src='..$directory' + document.forms.adminForm." . $name . ".options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		}

		jimport('joomla.filesystem.folder');
		$imageFiles = JFolder::files(JPATH_SITE.DS.$directory);
		$images = array( JHtml::_('select.option',  '', '- '. JText::_('Select Image') .' -'));
		foreach ($imageFiles as $file) {
			if (eregi($extensions, $file)) {
				$images[] = JHtml::_('select.option', $file);
			}
		}
		$images = JHtml::_(
			'select.genericlist',
			$images,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="1" '. $javascript,
				'list.select' => $active
			)
		);
		return $images;
	}

	/**
	 * Description
	 *
 	 * @param string SQL with ordering As value and 'name field' AS text
 	 * @param integer The length of the truncated headline
 	 * @since 1.5
 	 */
	public static function genericordering($sql, $chop = '30')
	{
		$db =& JFactory::getDBO();
		$order = array();
		$db->setQuery($sql);
		try {
			$order = $db->loadObjectList();
		} catch(JException $e) {
			return false;
		}

		if(empty($orders)) {
			$order[] = JHtml::_('select.option',  1, JText::_('first'));
			return $order;
		}

		$order[] = JHtml::_('select.option',  0, '0 '. JText::_('first'));
		for ($i=0, $n=count($orders); $i < $n; $i++) {

			if (JString::strlen($orders[$i]->text) > $chop) {
				$text = JString::substr($orders[$i]->text,0,$chop)."...";
			} else {
				$text = $orders[$i]->text;
			}

			$order[] = JHtml::_('select.option',  $orders[$i]->value, $orders[$i]->value.' ('.$text.')');
		}
		$order[] = JHtml::_('select.option',  $orders[$i-1]->value+1, ($orders[$i-1]->value+1).' '. JText::_('last'));

		return $order;
	}

	/**
	* Build the select list for Ordering of a specified Table
	*/
	public static function specificordering(&$row, $id, $query, $neworder = 0)
	{
		if ($id) {
			$order = JHtml::_('list.genericordering', $query);
			$ordering = JHtml::_(
				'select.genericlist',
				$order,
				'ordering',
				array('list.attr' => 'class="inputbox" size="1"', 'list.select' => intval($row->ordering))
			);
		} else {
			if ($neworder) {
				$text = JText::_('descNewItemsFirst');
			} else {
				$text = JText::_('descNewItemsLast');
			}
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $text;
		}
		return $ordering;
	}

	/**
	* Select list of active users
	*/
	public static function users($name, $active, $nouser = 0, $javascript = NULL, $order = 'name', $reg = 1)
	{
		$db =& JFactory::getDBO();

		$and = '';
		if ($reg) {
		// does not include registered users in the list
			$and = ' AND gid > 18';
		}

		$query = 'SELECT id AS value, name AS text'
		. ' FROM #__users'
		. ' WHERE block = 0'
		. $and
		. ' ORDER BY '. $order
		;
		$db->setQuery($query);
		if ($nouser) {
			$users[] = JHtml::_('select.option',  '0', '- '. JText::_('No User') .' -');
			try {
				$users = array_merge($users, $db->loadObjectList());
			} catch(JException $e) {
				//ignore the error here
			}
		} else {
			$users = $db->loadObjectList();
		}

		$users = JHtml::_(
			'select.genericlist',
			$users,
			$name,
			array('list.attr' => 'class="inputbox" size="1" '. $javascript, 'list.select' => $active)
		);
		return $users;
	}

	/**
	* Select list of positions - generally used for location of images
	*/
	public static function positions(
		$name,
		$active = null,
		$javascript = null,
		$none = 1,
		$center = 1,
		$left = 1,
		$right = 1,
		$id = false
	) {
		$pos = array();
		if ($none) {
			$pos[''] = JText::_('None');
		}
		if ($center) {
			$pos['center'] = JText::_('Center');
		}
		if ($left) {
			$pos['left'] = JText::_('Left');
		}
		if ($right) {
			$pos['right'] = JText::_('Right');
		}

		$positions = JHtml::_(
			'select.genericlist',
			$pos,
			$name,
			array(
				'id' => $id,
				'list.attr' => 'class="inputbox" size="1"'. $javascript,
				'list.select' => $active,
				'option.key' => null,
			)
		);

		return $positions;
	}

	/**
	* Select list of active categories for components
	*/
	public static function category($name, $section, $active = NULL, $javascript = NULL, $order = 'ordering', $size = 1, $sel_cat = 1)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT id AS value, title AS text'
		. ' FROM #__categories'
		. ' WHERE section = '.$db->Quote($section)
		. ' AND published = 1'
		. ' ORDER BY '. $order
		;
		$db->setQuery($query);
		if ($sel_cat) {
			$categories[] = JHtml::_('select.option',  '0', '- '. JText::_('Select a Category') .' -');
			try {
				$categories = array_merge($categories, $db->loadObjectList());
			} catch (JException $e) {
				//Ignore error
			}
		} else {
			try {
				$categories = $db->loadObjectList();
			} catch (JException $e) {
				$categories = array();
			}
		}

		$category = JHtml::_(
			'select.genericlist',
			$categories,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="'. $size .'" '. $javascript,
				'list.select' => $active
			)
		);
		return $category;
	}

	/**
	* Select list of active sections
	*/
	public static function section($name, $active = NULL, $javascript = NULL, $order = 'ordering', $uncategorized = true)
	{
		$db =& JFactory::getDBO();

		$categories[] = JHtml::_('select.option',  '-1', '- '. JText::_('Select Section') .' -');

		if ($uncategorized) {
			$categories[] = JHtml::_('select.option',  '0', JText::_('Uncategorized'));
		}

		$query = 'SELECT id AS value, title AS text'
		. ' FROM #__sections'
		. ' WHERE published = 1'
		. ' ORDER BY ' . $order
		;
		$db->setQuery($query);
		try {
			$sections = array_merge($categories, $db->loadObjectList());
		} catch(JException $e) {
			//ignore error
		}

		$category = JHtml::_(
			'select.genericlist',
			$sections,
			$name,
			array('list.attr' => 'class="inputbox" size="1" '. $javascript, 'list.select' => $active)
		);

		return $category;
	}
}
