<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage		HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

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
	 * Use JHtml::_('access.assetgroups', 'access', $selected) instead
	 * @deprecated
	 */
	public static function accesslevel(&$row)
	{
		return JHtml::_('access.assetgroups', 'access', $row->access);
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
		$imageFiles	= JFolder::files(JPATH_SITE.DS.$directory);
		$images		= array(JHtml::_('select.option',  '', '- '. JText::_('Select Image') .' -'));
		foreach ($imageFiles as $file) {
			if (preg_match('#('.$extensions.')$#', $file) {
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
	 * Returns an array of options
	 *
 	 * @param	string $sql		SQL with ordering As value and 'name field' AS text
 	 * @param	integer	$chop	The length of the truncated headline
 	 *
 	 * @return	array	An array of objects formatted for JHtml list processing
 	 * @since	1.5
 	 */
	public static function genericordering($sql, $chop = '30')
	{
		$db = &JFactory::getDbo();
		$options	= array();
		$db->setQuery($sql);

		$items = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}

		if (empty($items)) {
			$options[] = JHtml::_('select.option',  1, JText::_('JOption_Order_First'));
			return $options;
		}

		$options[] = JHtml::_('select.option',  0, '0 '. JText::_('JOption_Order_First'));
		for ($i=0, $n=count($items); $i < $n; $i++)
		{
			if (JString::strlen($items[$i]->text) > $chop) {
				$text = JString::substr($items[$i]->text,0,$chop)."...";
			} else {
				$text = $items[$i]->text;
			}

			$options[] = JHtml::_('select.option',  $items[$i]->value, $items[$i]->value.' ('.$text.')');
		}
		$options[] = JHtml::_('select.option',  $items[$i-1]->value+1, ($items[$i-1]->value+1).' '. JText::_('JOption_Order_Last'));

		return $options;
	}

	/**
	 * @deprecated	1.6 Use JHtml::_('list.ordering') instead
	 */
	public static function specificordering($value, $id, $query, $neworder = 0)
	{
		if (is_object($value)) {
			$value = $value->ordering;
		}

		if ($id) {
			$neworder = 0;
		} else {
			if ($neworder) {
				$neworder = 1;
			} else {
				$neworder = -1;
			}
		}
		return JHtmlList::ordering('ordering', $query, '', $value, $neworder);
	}

	/**
	 * Build the select list for Ordering derived from a query
	 *
	 * @param	int $value		The scalar value
	 * @param	string $query
	 * @param	string $attribs	HTML tag attributes
	 * @param	int $neworder	1 if new and first, -1 if new and last, 0  or null if existing item
	 * @param	string $prefix	An optional prefix for the task
	 *
	 * @return	string
	 * @since	1.6
	 */
	public static function ordering($name, $query, $attribs = null, $selected = null, $neworder = null, $chop = null)
	{
		if (empty($attribs)) {
			$attribs = 'class="inputbox" size="1"';
		}

		if (empty($neworder))
		{
			$orders	= JHtml::_('list.genericordering', $query);
			$html	= JHtml::_(
				'select.genericlist',
				$orders,
				$name,
				array('list.attr' => $attribs, 'list.select' => (int) $selected)
			);
		}
		else
		{
			if ($neworder > 0) {
				$text = JText::_('descNewItemsFirst');
			}
			else if ($neworder <= 0) {
				$text = JText::_('descNewItemsLast');
			}
			$html = '<input type="hidden" name="'.$name.'" value="'. (int) $selected .'" />'. $text;
		}
		return $html;
	}

	/**
	 * Select list of active users
	 */
	public static function users($name, $active, $nouser = 0, $javascript = NULL, $order = 'name', $reg = 1)
	{
		$db = &JFactory::getDbo();

		$and = '';
		if ($reg) {
		// does not include registered users in the list
			$and = ' AND m.group_id != 2';
		}

		$query = 'SELECT u.id AS value, u.name AS text'
		. ' FROM #__users AS u'
		. ' JOIN #__user_usergroup_map AS m WHERE m.user_id = u.id'
		. ' WHERE u.block = 0'
		. $and
		. ' ORDER BY '. $order
		;
		$db->setQuery($query);
		if ($nouser) {
			$users[] = JHtml::_('select.option',  '0', '- '. JText::_('No User') .' -');
			$users = array_merge($users, $db->loadObjectList());
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
	)
	{
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
	public static function category($name, $extension = 'com_content', $action = 'com_content.view', $root = NULL, $active = -1, $javascript = NULL, $size = 1, $sel_cat = 1, $uncat = 0)
	{
		$db = &JFactory::getDbo();
		$user = &JFactory::getUser();

		if ($root == NULL)
		{
			$root = '';
		} else {
			$root = '';// AND cp.id = '. (int) $root.' ';
		}

		$query = 'SELECT c.id, c.title, c.parent_id, 0 as depth'.
				' FROM #__categories AS c'.
				' WHERE c.section = '.$db->Quote($extension).
				$root.
				' AND c.access IN ('.implode(',', $user->authorisedLevels($action)).')'.
				' GROUP BY c.id ORDER BY c.ordering';
		$db->setQuery($query);
		$cat_list = $db->loadObjectList();
		$depth = array();
		$i = 0;
		foreach($cat_list as &$cat)
		{
			if (isset($depth[$cat->parent_id]))
			{
				$cat->depth = $depth[$cat->parent_id] + 1;
			}
			$depth[$cat->id] = $cat->depth;
		}
		$categories = array();

		if ($sel_cat)
		{
			$categories[] = JHtml::_('select.option', '-1', JText::_('Select Category'), 'id', 'title');
			$categories[] = JHtml::_('select.option', '', '----------', 'id', 'title');
		}
		if ($uncat)
		{
			$categories[] = JHtml::_('select.option', 0, JText::_('Uncategorized'), 'id', 'title');
			$categories[] = JHtml::_('select.option', '', '----------', 'id', 'title');
		}

		foreach ($cat_list as $category)
		{
			$categories[] = JHtml::_('select.option', $category->id, str_repeat('-', $category->depth).$category->title, 'id', 'title');
		}
		$category = JHtml::_('select.genericlist', $categories, $name,
			array(
				'list.attr' => 'class="inputbox" size="'. $size .'" '. $javascript,
				'list.select' => $active,
				'option.key' => 'id',
				'option.text' => 'title'
			)
		);

		return $category;
	}

	/**
	 * Select list of active sections
	 */
	function section($name, $active = NULL, $javascript = NULL, $order = 'ordering', $uncategorized = true)
	{
		$db = &JFactory::getDbo();

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
		$sections = array_merge($categories, $db->loadObjectList());

		$category = JHtml::_('select.genericlist',   $sections, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active);

		return $category;
	}
}
