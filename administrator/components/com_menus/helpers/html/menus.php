<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * Menus HTML helper class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.7
 */
abstract class MenusHtmlMenus
{
	/**
	 * Generate the markup to display the item associations
	 *
	 * @param   int  $itemid  The menu item id
	 *
	 * @return  string
	 *
	 * @since   3.0
	 *
	 * @throws Exception If there is an error on the query
	 */
	public static function association($itemid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = MenusHelper::getAssociations($itemid))
		{
			// Get the associated menu items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('m.id, m.title')
				->select('l.sef as lang_sef, l.lang_code')
				->select('mt.title as menu_title')
				->from('#__menu as m')
				->join('LEFT', '#__menu_types as mt ON mt.menutype=m.menutype')
				->where('m.id IN (' . implode(',', array_values($associations)) . ')')
				->join('LEFT', '#__languages as l ON m.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (runtimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
			}

			// Construct html
			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id);

					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br />' . JText::sprintf('COM_MENUS_MENU_SPRINTF', $item->menu_title);
					$classes = 'hasPopover label label-association label-' . $item->lang_sef;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			JHtml::_('bootstrap.popover');

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer  $value     The state value.
	 * @param   integer  $i         The row index
	 * @param   boolean  $enabled   An optional setting for access control on the action.
	 * @param   string   $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string        The Html code
	 *
	 * @see JHtmlJGrid::state
	 *
	 * @since   1.7.1
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states = array(
			9  => array(
				'unpublish',
				'',
				'COM_MENUS_HTML_UNPUBLISH_HEADING',
				'',
				true,
				'publish',
				'publish',
			),
			8  => array(
				'publish',
				'',
				'COM_MENUS_HTML_PUBLISH_HEADING',
				'',
				true,
				'unpublish',
				'unpublish',
			),
			7  => array(
				'unpublish',
				'',
				'COM_MENUS_HTML_UNPUBLISH_SEPARATOR',
				'',
				true,
				'publish',
				'publish',
			),
			6  => array(
				'publish',
				'',
				'COM_MENUS_HTML_PUBLISH_SEPARATOR',
				'',
				true,
				'unpublish',
				'unpublish',
			),
			5  => array(
				'unpublish',
				'',
				'COM_MENUS_HTML_UNPUBLISH_ALIAS',
				'',
				true,
				'publish',
				'publish',
			),
			4  => array(
				'publish',
				'',
				'COM_MENUS_HTML_PUBLISH_ALIAS',
				'',
				true,
				'unpublish',
				'unpublish',
			),
			3  => array(
				'unpublish',
				'',
				'COM_MENUS_HTML_UNPUBLISH_URL',
				'',
				true,
				'publish',
				'publish',
			),
			2  => array(
				'publish',
				'',
				'COM_MENUS_HTML_PUBLISH_URL',
				'',
				true,
				'unpublish',
				'unpublish',
			),
			1  => array(
				'unpublish',
				'COM_MENUS_EXTENSION_PUBLISHED_ENABLED',
				'COM_MENUS_HTML_UNPUBLISH_ENABLED',
				'COM_MENUS_EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish',
			),
			0  => array(
				'publish',
				'COM_MENUS_EXTENSION_UNPUBLISHED_ENABLED',
				'COM_MENUS_HTML_PUBLISH_ENABLED',
				'COM_MENUS_EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish',
			),
			-1 => array(
				'unpublish',
				'COM_MENUS_EXTENSION_PUBLISHED_DISABLED',
				'COM_MENUS_HTML_UNPUBLISH_DISABLED',
				'COM_MENUS_EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning',
			),
			-2 => array(
				'publish',
				'COM_MENUS_EXTENSION_UNPUBLISHED_DISABLED',
				'COM_MENUS_HTML_PUBLISH_DISABLED',
				'COM_MENUS_EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'trash',
				'trash',
			),
			-3 => array(
				'publish',
				'',
				'COM_MENUS_HTML_PUBLISH',
				'',
				true,
				'trash',
				'trash',
			),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, 'items.', $enabled, true, $checkbox);
	}

	/**
	 * Returns a visibility state on a grid
	 *
	 * @param   integer  $params  Params of item.
	 *
	 * @return  string  The Html code
	 *
	 * @since   3.7.0
	 */
	public static function visibility($params)
	{
		$registry = new Registry;

		try
		{
			$registry->loadString($params);
		}
		catch (Exception $e)
		{
			// Invalid JSON
		}

		$show_menu = $registry->get('menu_show');

		return ($show_menu === 0) ? '<span class="label">' . JText::_('COM_MENUS_LABEL_HIDDEN') . '</span>' : '';
	}
}
