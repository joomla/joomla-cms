<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\MasterAssociationsHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Contact HTML helper class.
 *
 * @since  1.6
 */
class AdministratorService
{
	/**
	 * Get the associated language flags
	 *
	 * @param   integer  $contactid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception
	 */
	public function association($contactid)
	{
		// Defaults
		$html             = '';
		$globalMasterLang = Associations::getGlobalMasterLanguage();

		// Check if versions are enabled
		$saveHistory      = ComponentHelper::getParams('com_contact')->get('save_history', 0);

		// Get the associations
		if ($associations = Associations::getAssociations('com_contact', '#__contact_details', 'com_contact.item', $contactid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated contact items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.name as title')
				->select('l.sef as lang_sef, lang_code')
				->from('#__contact_details as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no master language used.
			if (!$globalMasterLang)
			{
				$query->where('c.id != ' . $contactid);
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
				// Check if current item is the master item.
				$isMaster = (array_key_exists($contactid, $items) && ($items[$contactid]->lang_code === $globalMasterLang))
					? true
					: false;

				// Check if there is a master item in the association and get its id if so.
				$masterId = array_key_exists($globalMasterLang, $associations)
					? $associations[$globalMasterLang]
					: '';

				// Get master dates of each item of associations.
				$assocMasterDates = MasterAssociationsHelper::getMasterDates($associations, 'com_contact.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$masterInfo = '';
					$labelClass = 'badge-success';
					$url        = Route::_('index.php?option=com_contact&task=contact.edit&id=' . (int) $item->id);

					if ($globalMasterLang)
					{
						// Don't continue for master, because it has been set just before as new array item
						if ($key === 'master')
						{
							continue;
						}

						$classMasterInfoItems = MasterAssociationsHelper::setMasterAndChildInfos(
							$contactid, $items, $key, $item, $globalMasterLang, $isMaster, $masterId, $assocMasterDates, $saveHistory
						);
						$labelClass  = $classMasterInfoItems[0];
						$masterInfo  = $classMasterInfoItems[1];
						$items       = $classMasterInfoItems[2];
						$needsUpdate = $classMasterInfoItems[3];

						$url = Route::_(
							MasterAssociationsHelper::getAssociationUrl(
								$item->id, $globalMasterLang, 'com_contact.contact', $item->lang_code, $key, $masterId, $needsUpdate
							)
						);
					}

					$text    = strtoupper($item->lang_sef);
					$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title) . $masterInfo;
					$classes = 'badge ' . $labelClass;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes . '">' . $text . '</a>'
						. '<div role="tooltip" id="tip' . (int) $item->id . '">' . $tooltip . '</div>';

					// Reorder the array, so the master item gets to the first place
					if ($item->lang_code === $globalMasterLang)
					{
						$items = array('master' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}

				// If a master item doesn't exist, display that there is no association with the master language
				if ($globalMasterLang && !$masterId)
				{
					$link = MasterAssociationsHelper::addNotAssociatedMasterLink($globalMasterLang, $contactid, 'com_contact.contact');

					// Add this on the top of the array
					$items = array('master' => array('link' => $link)) + $items;
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Show the featured/not-featured icon.
	 *
	 * @param   integer  $value      The featured value.
	 * @param   integer  $i          Id of the item.
	 * @param   boolean  $canChange  Whether the value can be changed or not.
	 *
	 * @return  string	The anchor tag to toggle featured/unfeatured contacts.
	 *
	 * @since   1.6
	 */
	public function featured($value = 0, $i, $canChange = true)
	{

		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'contacts.featured', 'COM_CONTACT_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'contacts.unfeatured', 'JFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="tbody-icon hasTooltip disabled' . ($value == 1 ? ' active' : '')
				. '" title="' . HTMLHelper::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
