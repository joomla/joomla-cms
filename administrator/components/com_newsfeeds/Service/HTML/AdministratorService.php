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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

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
		$html = '';
		$globalMasterLanguage = Associations::getGlobalMasterLanguage();

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
				->select('c.id, c.name as title')
				->select('l.sef as lang_sef, lang_code')
				->from('#__newsfeeds as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')');

			// Don't get the id of the item itself when there is no master language used
			if (!$globalMasterLanguage)
			{
				$query->where('c.id != ' . $newsfeedid);
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
				throw new \Exception($e->getMessage(), 500);
			}

			// Check whether the current article is written in the global master language.
			$masterElement = ($globalMasterLanguage && $items[$newsfeedid]->lang_code === $globalMasterLanguage) ? true : false;

			if ($items)
			{
				foreach ($items as $key => &$item)
				{
					// Don't continue for master, because it has been set here before
					if ($key === 'master')
					{
						continue;
					}

					// Don't display other children if the current item is a child of the master language.
					if ($key !== $newsfeedid && $globalMasterLanguage !== $item->lang_code && !$masterElement && $globalMasterLanguage)
					{
						unset($items[$key]);
					}

					$text    = strtoupper($item->lang_sef);
					$url     = Route::_('index.php?option=com_newsfeeds&task=newsfeed.edit&id=' . (int) $item->id);
					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover badge badge-success';

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';

					// Reorder the array, so the master item gets to the first place
					if ($item->lang_code === $globalMasterLanguage)
					{
						$items = array('master' => $items[$key]) + $items;
						unset($items[$key]);
					}
				}
			}

			HTMLHelper::_('bootstrap.popover');

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
