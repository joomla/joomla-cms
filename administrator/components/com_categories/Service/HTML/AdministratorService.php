<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\MasterAssociationsHelper;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Administrator category HTML
 *
 * @since  3.2
 */
class AdministratorService
{
	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $catid      Category identifier to search its associations
	 * @param   string   $extension  Category Extension
	 *
	 * @return  string   The language HTML
	 *
	 * @since   3.2
	 * @throws  \Exception
	 */
	public function association($catid, $extension = 'com_content')
	{
		// Defaults
		$html             = '';
		$globalMasterLang = Associations::getGlobalMasterLanguage();

		// Check if versions are enabled.
		$saveHistory      = ComponentHelper::getParams($extension)->get('save_history', 0);

		// Get the associations
		if ($associations = CategoriesHelper::getAssociations($catid, $extension))
		{
			$associations = ArrayHelper::toInteger($associations);

			// Get the associated categories
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.title')
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__categories as c')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no master language used.
			if (!$globalMasterLang)
			{
				$query->where('c.id != ' . $catid);
			}

			$query->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($globalMasterLang)
			{
				// Check if the current item is a master item.
				$isMaster = (array_key_exists($catid, $items) && ($items[$catid]->lang_code === $globalMasterLang))
					? true
					: false;

				// Check if there is a master item in the association and get its id if so.
				$masterId = array_key_exists($globalMasterLang, $associations)
					? $associations[$globalMasterLang]
					: '';

				// Get master dates of each item of associations.
				$assocMasterDates = MasterAssociationsHelper::getMasterDates($associations, 'com_categories.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$labelClass = 'badge-success';
					$masterInfo = '';
					$url        = Route::_('index.php?option=com_categories&task=category.edit&id=' . (int) $item->id . '&extension=' . $extension);

					if ($globalMasterLang)
					{
						// Don't continue for master, because it has been set here before.
						if ($key === 'master')
						{
							continue;
						}

						$classMasterInfoItems = MasterAssociationsHelper::setMasterAndChildInfos(
							$catid, $items, $key, $item, $globalMasterLang, $isMaster, $masterId, $assocMasterDates, $saveHistory
						);
						$labelClass  = $classMasterInfoItems[0];
						$masterInfo  = $classMasterInfoItems[1];
						$items       = $classMasterInfoItems[2];
						$needsUpdate = $classMasterInfoItems[3];

						$url = Route::_(MasterAssociationsHelper::getAssociationUrl(
							$item->id, $globalMasterLang, $extension . '.category', $item->lang_code, $key, $masterId, $needsUpdate
						));
					}

					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . $masterInfo;
					$classes = 'badge ' . $labelClass;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the master item gets to the first place.
					if ($item->lang_code === $globalMasterLang)
					{
						$items = array('master' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a master item doesn't exist, display that there is no association with the master language.
				if ($globalMasterLang && !$masterId)
				{
					$link = MasterAssociationsHelper::addNotAssociatedMasterLink($globalMasterLang, $catid, $extension . '.category');

					// Add this on the top of the array.
					$items = array('master' => array('link' => $link)) + $items;
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
