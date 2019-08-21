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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\DefaultAssocLangHelper;
use Joomla\Database\ParameterType;
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
		$defaultAssocLang = Associations::getDefaultAssocLang();

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
				->select($db->quoteName(['c.id', 'lang_code', 'l.image']))
				->select($db->quoteName(['c.name', 'l.sef', 'cat.title', 'l.title'], ['title', 'lang_sef', 'category_title', 'language_title']))
				->from($db->quoteName('#__contact_details', 'c'))
				->leftJoin($db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
				->whereIN($db->quoteName('c.id'), array_values($associations));

			// Don't get the id of the item itself when there is no default association language used.
			if (!$defaultAssocLang)
			{
				$query->where($db->quoteName('c.id') . ' != :id')
					->bind(':id', $contactid, ParameterType::INTEGER);
			}

			$query->leftJoin($db->quoteName('#__languages', 'l'), $db->quoteName('c.language') . ' = ' . $db->quoteName('l.lang_code'));
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($defaultAssocLang)
			{
				// Check if current item is the parent.
				$isParent = (array_key_exists($contactid, $items) && ($items[$contactid]->lang_code === $defaultAssocLang))
					? true
					: false;

				// Check if there is a parent in the association and get its id if so.
				$parentId = array_key_exists($defaultAssocLang, $associations)
					? $associations[$defaultAssocLang]
					: '';

				// Get parent dates of each item of associations.
				$assocParentDates = DefaultAssocLangHelper::getParentDates($associations, 'com_contact.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$parentChildInfo = '';
					$labelClass      = 'badge-success';
					$url             = Route::_('index.php?option=com_contact&task=contact.edit&id=' . (int) $item->id);

					if ($defaultAssocLang)
					{
						// Don't continue for parent, because it has been set just before as new array item
						if ($key === 'parent')
						{
							continue;
						}

						$classParentInfoItems = DefaultAssocLangHelper::setParentAndChildInfos(
							$contactid, $items, $key, $item, $defaultAssocLang, $isParent, $parentId, $assocParentDates, $saveHistory
						);
						$labelClass       = $classParentInfoItems[0];
						$parentChildInfo  = $classParentInfoItems[1];
						$items            = $classParentInfoItems[2];
						$needsUpdate      = $classParentInfoItems[3];

						$url = Route::_(
							DefaultAssocLangHelper::getAssociationUrl(
								$item->id, $defaultAssocLang, 'com_contact.contact', $item->lang_code, $key, $parentId, $needsUpdate
							)
						);
					}

					$text    = strtoupper($item->lang_sef);
					$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
						. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title) . $parentChildInfo;
					$classes = 'badge ' . $labelClass;

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
					$link = DefaultAssocLangHelper::addNotAssociatedParentLink($defaultAssocLang, $contactid, 'com_contact.contact');

					// Add this on the top of the array
					$items = array('parent' => array('link' => $link)) + $items;
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
	public function featured($value, $i, $canChange = true)
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
			$html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon'
				. ($value == 1 ? ' active' : '') . '" title="' . Text::_($state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="tbody-icon disabled' . ($value == 1 ? ' active' : '')
				. '" title="' . Text::_($state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
