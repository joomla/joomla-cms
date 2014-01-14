<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for creating different select lists
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JHtmlList
{
	/**
	 * Get a grouped list of pre-Joomla 1.6 access levels
	 *
	 * @param   object  &$row  The object (must have an access property).
	 *
	 * @return  string
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.1 Use JHtml::_('access.assetgrouplist', 'access', $selected) instead
	 * @see     JHtmlAccess::assetgrouplist
	 */
	public static function accesslevel(&$row)
	{
		// Deprecation warning.
		JLog::add('JList::accesslevel is deprecated.', JLog::WARNING, 'deprecated');

		return JHtml::_('access.assetgrouplist', 'access', $row->access);
	}

	/**
	 * Build the select list to choose an image
	 *
	 * @param   string  $name        The name of the field
	 * @param   string  $active      The selected item
	 * @param   string  $javascript  Alternative javascript
	 * @param   string  $directory   Directory the images are stored in
	 * @param   string  $extensions  Allowed extensions
	 *
	 * @return  array  Image names
	 *
	 * @since   11.1
	 */
	public static function images($name, $active = null, $javascript = null, $directory = null, $extensions = "bmp|gif|jpg|png")
	{
		if (!$directory)
		{
			$directory = '/images/';
		}

		if (!$javascript)
		{
			$javascript = "onchange=\"if (document.forms.adminForm." . $name
				. ".options[selectedIndex].value!='') {document.imagelib.src='..$directory' + document.forms.adminForm." . $name
				. ".options[selectedIndex].value} else {document.imagelib.src='media/system/images/blank.png'}\"";
		}

		jimport('joomla.filesystem.folder');
		$imageFiles = JFolder::files(JPATH_SITE . '/' . $directory);
		$images = array(JHtml::_('select.option', '', JText::_('JOPTION_SELECT_IMAGE')));

		foreach ($imageFiles as $file)
		{
			if (preg_match('#(' . $extensions . ')$#', $file))
			{
				$images[] = JHtml::_('select.option', $file);
			}
		}

		$images = JHtml::_(
			'select.genericlist',
			$images,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="1" ' . $javascript,
				'list.select' => $active
			)
		);

		return $images;
	}

	/**
	 * Returns an array of options
	 *
	 * @param   string   $sql  	SQL with 'ordering' AS value and 'name field' AS text
	 * @param   integer  $chop  The length of the truncated headline
	 *
	 * @return  array  An array of objects formatted for JHtml list processing
	 *
	 * @since   11.1
	 */
	public static function genericordering($sql, $chop = '30')
	{
		$db = JFactory::getDbo();
		$options = array();
		$db->setQuery($sql);

		$items = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}

		if (empty($items))
		{
			$options[] = JHtml::_('select.option', 1, JText::_('JOPTION_ORDER_FIRST'));
			return $options;
		}

		$options[] = JHtml::_('select.option', 0, '0 ' . JText::_('JOPTION_ORDER_FIRST'));
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$items[$i]->text = JText::_($items[$i]->text);
			if (JString::strlen($items[$i]->text) > $chop)
			{
				$text = JString::substr($items[$i]->text, 0, $chop) . "...";
			}
			else
			{
				$text = $items[$i]->text;
			}

			$options[] = JHtml::_('select.option', $items[$i]->value, $items[$i]->value . '. ' . $text);
		}
		$options[] = JHtml::_('select.option', $items[$i - 1]->value + 1, ($items[$i - 1]->value + 1) . ' ' . JText::_('JOPTION_ORDER_LAST'));

		return $options;
	}

	/**
	 * Build a select list with a specific ordering
	 *
	 * @param   integer  $value     The scalar value
	 * @param   integer  $id        The id for an existing item in the list
	 * @param   string   $query     The query
	 * @param   integer  $neworder  1 if new and first, -1 if new and last,
	 *                              0  or null if existing item
	 *
	 * @return  string  Html for the ordered list
	 *
	 * @since   11.1
	 *
	 * @see         JHtmlList::ordering
	 * @deprecated  12.1  Use JHtml::_('list.ordering') instead
	 */
	public static function specificordering($value, $id, $query, $neworder = 0)
	{
		if (is_object($value))
		{
			$value = $value->ordering;
		}

		if ($id)
		{
			$neworder = 0;
		}
		else
		{
			if ($neworder)
			{
				$neworder = 1;
			}
			else
			{
				$neworder = -1;
			}
		}
		return JHtmlList::ordering('ordering', $query, '', $value, $neworder);
	}

	/**
	 * Build the select list for Ordering derived from a query
	 *
	 * @param   integer  $name      The scalar value
	 * @param   string   $query     The query
	 * @param   string   $attribs   HTML tag attributes
	 * @param   string   $selected  The selected item
	 * @param   integer  $neworder  1 if new and first, -1 if new and last, 0  or null if existing item
	 * @param   string   $chop      The length of the truncated headline
	 *
	 * @return  string   Html for the select list
	 *
	 * @since   11.1
	 */
	public static function ordering($name, $query, $attribs = null, $selected = null, $neworder = null, $chop = null)
	{
		if (empty($attribs))
		{
			$attribs = 'class="inputbox" size="1"';
		}

		if (empty($neworder))
		{
			$orders = JHtml::_('list.genericordering', $query);
			$html = JHtml::_('select.genericlist', $orders, $name, array('list.attr' => $attribs, 'list.select' => (int) $selected));
		}
		else
		{
			if ($neworder > 0)
			{
				$text = JText::_('JGLOBAL_NEWITEMSLAST_DESC');
			}
			elseif ($neworder <= 0)
			{
				$text = JText::_('JGLOBAL_NEWITEMSFIRST_DESC');
			}
			$html = '<input type="hidden" name="' . $name . '" value="' . (int) $selected . '" />' . '<span class="readonly">' . $text . '</span>';
		}
		return $html;
	}

	/**
	 * Select list of active users
	 *
	 * @param   string   $name        The name of the field
	 * @param   string   $active      The active user
	 * @param   integer  $nouser      If set include an option to select no user
	 * @param   string   $javascript  Custom javascript
	 * @param   string   $order       Specify a field to order by
	 * @param   string   $reg         Deprecated  Excludes users who are explicitly in group 2.
	 *
	 * @return  string   The HTML for a list of users list of users
	 *
	 * @since  11.1
	 */
	public static function users($name, $active, $nouser = 0, $javascript = null, $order = 'name', $reg = 1)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($reg)
		{
			// Does not include registered users in the list
			// @deprecated
			$query->where('m.group_id != 2');
		}

		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('LEFT', '#__user_usergroup_map AS m ON m.user_id = u.id');
		$query->where('u.block = 0');
		$query->order($order);
		$db->setQuery($query);

		if ($nouser)
		{
			$users[] = JHtml::_('select.option', '0', JText::_('JOPTION_NO_USER'));
			$users = array_merge($users, $db->loadObjectList());
		}
		else
		{
			$users = $db->loadObjectList();
		}

		$users = JHtml::_(
			'select.genericlist',
			$users,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="1" ' . $javascript,
				'list.select' => $active
			)
		);

		return $users;
	}

	/**
	 * Select list of positions - generally used for location of images
	 *
	 * @param   string   $name        Name of the field
	 * @param   string   $active      The active value
	 * @param   string   $javascript  Alternative javascript
	 * @param   boolean  $none        Null if not assigned
	 * @param   boolean  $center      Null if not assigned
	 * @param   boolean  $left        Null if not assigned
	 * @param   boolean  $right       Null if not assigned
	 * @param   boolean  $id          Null if not assigned
	 *
	 * @return  array  The positions
	 *
	 * @since   11.1
	 */
	public static function positions($name, $active = null, $javascript = null, $none = 1, $center = 1, $left = 1, $right = 1, $id = false)
	{
		$pos = array();
		if ($none)
		{
			$pos[''] = JText::_('JNONE');
		}

		if ($center)
		{
			$pos['center'] = JText::_('JGLOBAL_CENTER');
		}

		if ($left)
		{
			$pos['left'] = JText::_('JGLOBAL_LEFT');
		}

		if ($right)
		{
			$pos['right'] = JText::_('JGLOBAL_RIGHT');
		}

		$positions = JHtml::_(
			'select.genericlist', $pos, $name,
			array(
				'id' => $id,
				'list.attr' => 'class="inputbox" size="1"' . $javascript,
				'list.select' => $active,
				'option.key' => null,
			)
		);

		return $positions;
	}

	/**
	 * Crates a select list of categories
	 *
	 * @param   string   $name        Name of the field
	 * @param   string   $extension   Extension for which the categories will be listed
	 * @param   string   $selected    Selected value
	 * @param   string   $javascript  Custom javascript
	 * @param   integer  $order       Not used.
	 * @param   integer  $size        Size of the field
	 * @param   boolean  $sel_cat     If null do not include a Select Categories row
	 *
	 * @return  string
	 *
	 * @deprecated    12.1  Use JHtmlCategory instead
	 * @since   11.1
	 * @see     JHtmlCategory
	 */
	public static function category($name, $extension, $selected = null, $javascript = null, $order = null, $size = 1, $sel_cat = 1)
	{
		// Deprecation warning.
		JLog::add('JList::category is deprecated.', JLog::WARNING, 'deprecated');

		$categories = JHtml::_('category.options', $extension);
		if ($sel_cat)
		{
			array_unshift($categories, JHtml::_('select.option', '0', JText::_('JOPTION_SELECT_CATEGORY')));
		}

		$category = JHtml::_(
			'select.genericlist', $categories, $name, 'class="inputbox" size="' . $size . '" ' . $javascript, 'value', 'text',
			$selected
		);

		return $category;
	}
}
