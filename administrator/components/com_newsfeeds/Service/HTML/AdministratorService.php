<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\DefaultAssocLangHelper;
use Joomla\Database\ParameterType;

/**
 * Utility class for creating HTML Grids.
 *
 * @since  1.5
 */
class AdministratorService
{
	/**
	 * Get the associated language flags
	 *
	 * @param   int  $newsfeedid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  \Exception  Throws a 500 Exception on Database failure
	 */
	public function association($newsfeedid)
	{
		// Defaults
		$html             = '';
		$defaultAssocLang = Associations::getDefaultAssocLang();

		// Check if versions are enabled
		$saveHistory = ComponentHelper::getParams('com_newsfeeds')->get('save_history', 0);

		// Get the associations
		if ($associations = Associations::getAssociations('com_newsfeeds', '#__newsfeeds', 'com_newsfeeds.item', $newsfeedid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated newsfeed items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(['c.id', 'lang_code', 'l.image']))
				->select($db->quoteName(['c.name', 'l.sef', 'cat.title', 'l.title'], ['title', 'lang_sef', 'category_title', 'language_title']))
				->from($db->quoteName('#__newsfeeds', 'c'))
				->leftJoin($db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
				->whereIN($db->quoteName('c.id'), array_values($associations));

			// Don't get the id of the item itself when there is no default association language used
			if (!$defaultAssocLang)
			{
				$query->where($db->quoteName('c.id') . ' != :id')
					->bind(':id', $newsfeedid, ParameterType::INTEGER);
			}

			$query->leftJoin($db->quoteName('#__languages', 'l'),  $db->quoteName('c.language') . ' = ' .  $db->quoteName('l.lang_code'));
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
				// Check if current item is a parent.
				$isParent = (array_key_exists($newsfeedid, $items) && ($items[$newsfeedid]->lang_code === $defaultAssocLang))
					? true
					: false;

				// Check if there is a parent in the association and get its id if so.
				$parentId = array_key_exists($defaultAssocLang, $associations)
					? $associations[$defaultAssocLang]
					: '';

				// Get parent dates of each item of associations.
				$assocParentDates = DefaultAssocLangHelper::getParentDates($associations, 'com_newsfeeds.item');
			}

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					$parentChildInfo = '';
					$labelClass = 'badge-success';
					$url        = Route::_('index.php?option=com_newsfeeds&task=newsfeed.edit&id=' . (int) $item->id);

					if ($defaultAssocLang)
					{
						// Don't continue for parent, because it has been set here before
						if ($key === 'parent')
						{
							continue;
						}

						$classParentInfoItems = DefaultAssocLangHelper::setParentAndChildInfos(
							$newsfeedid, $items, $key, $item, $defaultAssocLang, $isParent, $parentId, $assocParentDates, $saveHistory
						);
						$labelClass      = $classParentInfoItems[0];
						$parentChildInfo = $classParentInfoItems[1];
						$items           = $classParentInfoItems[2];
						$needsUpdate     = $classParentInfoItems[3];

						$url = Route::_(
							DefaultAssocLangHelper::getAssociationUrl(
								$item->id, $defaultAssocLang, 'com_newsfeeds.newsfeed', $item->lang_code, $key, $parentId, $needsUpdate
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
					$link = DefaultAssocLangHelper::addNotAssociatedParentLink($defaultAssocLang, $newsfeedid, 'com_newsfeeds.newsfeed');

					// Add this on the top of the array
					$items = array('parent' => array('link' => $link)) + $items;
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
