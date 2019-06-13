<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Registry\Registry;

/**
 * Menus HTML helper class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.7
 */
class Menus
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
	 * @throws \Exception If there is an error on the query
	 */
	public function association($itemid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = MenusHelper::getAssociations($itemid))
		{
			// Get the associated menu items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('m.id, m.title')
				->select('l.sef as lang_sef, l.lang_code')
				->select('mt.title as menu_title')
				->from('#__menu as m')
				->join('LEFT', '#__menu_types as mt ON mt.menutype=m.menutype')
				->where('m.id IN (' . implode(',', array_values($associations)) . ')')
				->where('m.id != ' . $itemid)
				->join('LEFT', '#__languages as l ON m.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500);
			}

			// Construct html
			if ($items)
			{
				foreach ($items as &$item)
				{
					$text    = strtoupper($item->lang_sef);
					$url     = Route::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id);
					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('COM_MENUS_MENU_SPRINTF', $item->menu_title);
					$classes = 'hasPopover badge badge-secondary';

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			HTMLHelper::_('bootstrap.popover');

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
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
	public function visibility($params)
	{
		$registry = new Registry;

		try
		{
			$registry->loadString($params);
		}
		catch (\Exception $e)
		{
			// Invalid JSON
		}

		$show_menu = $registry->get('menu_show');

		return ($show_menu === 0) ? '<span class="badge badge-secondary">' . Text::_('COM_MENUS_LABEL_HIDDEN') . '</span>' : '';
	}

	/**
	 * Returns the enabled state of the component
	 *
	 * @param   string  $componentname Component name of the item.
	 *
	 * @return  string  The Html code
	 *
	 * @since   4.0
	 */
	public function enabled($componentname)
	{
		if (!empty($componentname))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('a.enabled'))
				->from($db->quoteName('#__extensions', 'a'))
				->where($db->quoteName('a.element') . ' = ' . $db->quote($componentname));

			$db->setQuery($query);

			$disabled = (int) $db->loadResult();

			return ($disabled === 0) ? '<div><span class="badge badge-secondary">' . Text::_('COM_MENUS_LABEL_DISABLED') . '</span></div>' : '';
		}
	}
}
