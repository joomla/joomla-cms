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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\DefaultAssocLangHelper;
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
		$defaultAssocLang = Associations::getDefaultAssocLang();

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
				->where('m.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no default association language used
			if (!$defaultAssocLang)
			{
				$query->where('m.id != ' . $itemid);
			}

			$query->join('LEFT', '#__languages as l ON m.language=l.lang_code')
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

			if ($defaultAssocLang)
			{
				// Check if current item is the parent.
				$isParent = (array_key_exists($itemid, $items) && ($items[$itemid]->lang_code === $defaultAssocLang))
					? true
					: false;

				// Check if there is a parent in the association and get its id if so
				$parentId = array_key_exists($defaultAssocLang, $associations)
					? $associations[$defaultAssocLang]
					: '';
			}

			// Construct html
			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$parentChildInfo = '';
					$classes    = 'badge badge-success';
					$url        = Route::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id);

					if ($defaultAssocLang)
					{
						// Don't continue for parent, because it has been set here before
						if ($key === 'parent')
						{
							continue;
						}

						// Don't display other children if the current item is a child.
						if ($key !== $itemid && $defaultAssocLang !== $item->lang_code && !$isParent)
						{
							unset($items[$key]);
						}

						if ($key === $parentId)
						{
							$classes   .= ' parent-item';
							$parentChildInfo = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_DEFAULT_ASSOC_LANG_ITEM');
						}

						$url = Route::_(DefaultAssocLangHelper::getAssociationUrl($item->id, $defaultAssocLang, 'com_menus.item', $item->lang_code, $key, $parentId));
					}

					$text    = strtoupper($item->lang_sef);
					$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('COM_MENUS_MENU_SPRINTF', $item->menu_title)
						. $parentChildInfo;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the parent gets to the first place
					if ($item->lang_code === $defaultAssocLang)
					{
						$items = array('parent' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a parent doesn't exist, display that there is no association with the default association language.
				if ($defaultAssocLang && !$parentId)
				{
					$link = DefaultAssocLangHelper::addNotAssociatedParentLink($defaultAssocLang, $itemid, 'com_menus.item');

					// Add this on the top of the array
					$items = array('parent' => array('link' => $link)) + $items;
				}
			}

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
}
