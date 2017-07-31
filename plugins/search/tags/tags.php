<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Search.tags
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Tags search plugin.
 *
 * @since  3.3
 */
class PlgSearchTags extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   3.3
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'tags' => 'PLG_SEARCH_TAGS_TAGS'
		);

		return $areas;
	}

	/**
	 * Search content (tags).
	 *
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   string  $areas     An array if the search is to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   3.3
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$lang  = JFactory::getLanguage();

		$section = JText::_('PLG_SEARCH_TAGS_TAGS');
		$limit   = $this->params->def('search_limit', 50);

		if (is_array($areas) && !array_intersect($areas, array_keys($this->onContentSearchAreas())))
		{
			return array();
		}

		$text = trim($text);

		if ($text === '')
		{
			return array();
		}

		$text = $db->quote('%' . $db->escape($text, true) . '%', false);

		switch ($ordering)
		{
			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'newest':
				$order = 'a.created_time DESC';
				break;

			case 'oldest':
				$order = 'a.created_time ASC';
				break;

			case 'popular':
			default:
				$order = 'a.title DESC';
		}

		$query->select('a.id, a.title, a.alias, a.note, a.published, a.access'
			. ', a.checked_out, a.checked_out_time, a.created_user_id'
			. ', a.path, a.parent_id, a.level, a.lft, a.rgt'
			. ', a.language, a.created_time AS created, a.description');

		$case_when_item_alias  = ' CASE WHEN ';
		$case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
		$case_when_item_alias .= ' THEN ';
		$a_id                  = $query->castAsChar('a.id');
		$case_when_item_alias .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id . ' END as slug';
		$query->select($case_when_item_alias);

		$query->from('#__tags AS a');
		$query->where('a.alias <> ' . $db->quote('root'));

		$query->where('(a.title LIKE ' . $text . ' OR a.alias LIKE ' . $text . ')');

		$query->where($db->qn('a.published') . ' = 1');

		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		if ($app->isClient('site') && JLanguageMultilang::isEnabled())
		{
			$tag = JFactory::getLanguage()->getTag();
			$query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
		}

		$query->order($order);

		$db->setQuery($query, 0, $limit);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$rows = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		if ($rows)
		{
			JLoader::register('TagsHelperRoute', JPATH_SITE . '/components/com_tags/helpers/route.php');

			foreach ($rows as $key => $row)
			{
				$rows[$key]->href       = TagsHelperRoute::getTagRoute($row->id);
				$rows[$key]->text       = ($row->description !== '' ? $row->description : $row->title);
				$rows[$key]->text      .= $row->note;
				$rows[$key]->section    = $section;
				$rows[$key]->created    = $row->created;
				$rows[$key]->browsernav = 0;
			}
		}

		if (!$this->params->get('show_tagged_items'))
		{
			return $rows;
		}
		else
		{
			$final_items = $rows;
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tags/models');
			$tag_model = JModelLegacy::getInstance('Tag', 'TagsModel');
			$tag_model->getState();

			foreach ($rows as $key => $row)
			{
				$tag_model->setState('tag.id', $row->id);
				$tagged_items = $tag_model->getItems();

				if ($tagged_items)
				{
					foreach ($tagged_items as $k => $item)
					{
						// For 3rd party extensions we need to load the component strings from its sys.ini file
						$parts = explode('.', $item->type_alias);
						$comp = array_shift($parts);
						$lang->load($comp, JPATH_SITE, null, false, true)
						|| $lang->load($comp, JPATH_SITE . '/components/' . $comp, null, false, true);

						// Making up the type string
						$type = implode('_', $parts);
						$type = $comp . '_CONTENT_TYPE_' . $type;

						$new_item        = new stdClass;
						$new_item->href  = $item->link;
						$new_item->title = $item->core_title;
						$new_item->text  = $item->core_body;

						if ($lang->hasKey($type))
						{
							$new_item->section = JText::sprintf('PLG_SEARCH_TAGS_ITEM_TAGGED_WITH', JText::_($type), $row->title);
						}
						else
						{
							$new_item->section = JText::sprintf('PLG_SEARCH_TAGS_ITEM_TAGGED_WITH', $item->content_type_title, $row->title);
						}

						$new_item->created    = $item->displayDate;
						$new_item->browsernav = 0;
						$final_items[]        = $new_item;
					}
				}
			}

			return $final_items;
		}
	}
}
