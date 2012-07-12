<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
	public static function genericordering($sql, $chop = 30)
	{
		$db = JFactory::getDbo();
		$options = array();
		$db->setQuery($sql);

		$items = $db->loadObjectList();

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
	 * Build the select list for Ordering derived from a query
	 *
	 * @param   integer  $name      The scalar value
	 * @param   string   $query     The query
	 * @param   string   $attribs   HTML tag attributes
	 * @param   string   $selected  The selected item
	 * @param   integer  $neworder  1 if new and first, -1 if new and last, 0  or null if existing item
	 *
	 * @return  string   Html for the select list
	 *
	 * @since   11.1
	 */
	public static function ordering($name, $query, $attribs = null, $selected = null, $neworder = null)
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
	 *
	 * @return  string   The HTML for a list of users list of users
	 *
	 * @since  11.1
	 */
	public static function users($name, $active, $nouser = 0, $javascript = null, $order = 'name')
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('LEFT', '#__user_usergroup_map AS m ON m.user_id = u.id');
		$query->where('u.block = 0');
		$query->order($order);
		$query->group('u.id');
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
	public static function positions($name, $active = null, $javascript = null, $none = true, $center = true, $left = true, $right = true,
		$id = false)
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
}
