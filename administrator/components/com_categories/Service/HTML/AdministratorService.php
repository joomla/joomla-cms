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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
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
		$html                 = '';
		$globalMasterLanguage = Associations::getGlobalMasterLanguage();

		// Get the associations
		if ($associations = CategoriesHelper::getAssociations($catid, $extension))
		{
			$associations = ArrayHelper::toInteger($associations);

			// Get the associated categories
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.title')
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__categories as c')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no master language used
			if (!$globalMasterLanguage)
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

			if ($globalMasterLanguage)
			{
				// Check whether the current article is written in the global master language
				$masterElement = (array_key_exists($catid, $items)
					&& ($items[$catid]->lang_code === $globalMasterLanguage))
					? true
					: false;

				// Check if there is a master item in the association and get his id if so
				$masterId = array_key_exists($globalMasterLanguage, $associations)
					? $associations[$globalMasterLanguage]
					: '';

				$assocMasterDates = MasterAssociationsHelper::getMasterDates($associations, 'com_categories.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$labelClass    = 'badge-success';
					$languageTitle = $item->language_title;
					$text          = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$title         = $item->title;
					$url           = Route::_('index.php?option=com_categories&task=category.edit&id=' . (int) $item->id . '&extension=' . $extension);

					if ($globalMasterLanguage)
					{

						// Don't continue for master, because it has been set here before
						if ($key === 'master')
						{
							continue;
						}

						// Don't display other children if the current item is a child of the master language.
						if (($key !== $catid)
							&& ($globalMasterLanguage !== $item->lang_code)
							&& !$masterElement)
						{
							unset($items[$key]);
						}

						if ($key === $masterId)
						{
							$labelClass    .= ' master-item';
							$languageTitle = $item->language_title . ' - ' . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_LANGUAGE');
						}
						else
						{
							// get association state of child when a master exists
							if ($masterId && array_key_exists($key, $assocMasterDates)
								&& array_key_exists($masterId, $assocMasterDates))
							{
								$associatedModifiedMaster = $assocMasterDates[$key];
								$lastModifiedMaster       = $assocMasterDates[$masterId];

								if ($associatedModifiedMaster < $lastModifiedMaster)
								{
									$labelClass = 'badge-warning';
									$title      .= '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_OUTDATED_DESC') . '<br>';
								}
								else
								{
									$title .= '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_UP_TO_DATE_DESC') . '<br>';
								}
							}
						}
					}

					$classes = 'badge ' . $labelClass;
					$tooltip  = '<strong>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

					$item->link = '<a href="' . $url . '" title="' . $languageTitle . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the master item gets to the first place
					if ($item->lang_code === $globalMasterLanguage)
					{
						$items = array('master' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a master item doesn't exist, display that there is no association with the master language
				if ($globalMasterLanguage && !$masterId)
				{
					$link = MasterAssociationsHelper::addNotAssociatedMasterLink($globalMasterLanguage);

					// add this on the top of the array
					$items = array('master' => array('link' => $link)) + $items;
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
