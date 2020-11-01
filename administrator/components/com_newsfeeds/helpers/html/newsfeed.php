<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('NewsfeedsHelper', JPATH_ADMINISTRATOR . '/components/com_newsfeeds/helpers/newsfeeds.php');

/**
 * Utility class for creating HTML Grids.
 *
 * @since  1.5
 */
class JHtmlNewsfeed
{
	/**
	 * Get the associated language flags
	 *
	 * @param   int  $newsfeedid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception  Throws a 500 Exception on Database failure
	 */
	public static function association($newsfeedid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = JLanguageAssociations::getAssociations('com_newsfeeds', '#__newsfeeds', 'com_newsfeeds.item', $newsfeedid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated newsfeed items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.name as title')
				->select('l.sef as lang_sef, lang_code')
				->from('#__newsfeeds as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->where('c.id != ' . $newsfeedid)
				->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_newsfeeds&task=newsfeed.edit&id=' . (int) $item->id);
					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br />' . JText::sprintf('JCATEGORY_SPRINTF', $item->category_title);
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
}
