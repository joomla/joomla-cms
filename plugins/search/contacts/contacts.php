<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Search.contacts
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contacts search plugin.
 *
 * @since  1.6
 */
class PlgSearchContacts extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'contacts' => 'PLG_SEARCH_CONTACTS_CONTACTS'
		);

		return $areas;
	}

	/**
	 * Search content (contacts).
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
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		JLoader::register('ContactHelperRoute', JPATH_SITE . '/components/com_contact/helpers/route.php');

		$db     = JFactory::getDbo();
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$sContent  = $this->params->get('search_content', 1);
		$sArchived = $this->params->get('search_archived', 1);
		$limit     = $this->params->def('search_limit', 50);
		$state     = array();

		if ($sContent)
		{
			$state[] = 1;
		}

		if ($sArchived)
		{
			$state[] = 2;
		}

		if (empty($state))
		{
			return array();
		}

		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		$section = JText::_('PLG_SEARCH_CONTACTS_CONTACTS');

		switch ($ordering)
		{
			case 'alpha':
				$order = 'a.name ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.name ASC';
				break;

			case 'popular':
			case 'newest':
			case 'oldest':
			default:
				$order = 'a.name DESC';
		}

		$text = $db->quote('%' . $db->escape($text, true) . '%', false);

		$query = $db->getQuery(true);

		// SQLSRV changes.
		$case_when  = ' CASE WHEN ';
		$case_when .= $query->charLength('a.alias', '!=', '0');
		$case_when .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $a_id . ' END as slug';

		$case_when1  = ' CASE WHEN ';
		$case_when1 .= $query->charLength('c.alias', '!=', '0');
		$case_when1 .= ' THEN ';
		$c_id        = $query->castAsChar('c.id');
		$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when1 .= ' ELSE ';
		$case_when1 .= $c_id . ' END as catslug';

		$query->select(
			'a.name AS title, \'\' AS created, a.con_position, a.misc, '
				. $case_when . ',' . $case_when1 . ', '
				. $query->concatenate(array('a.name', 'a.con_position', 'a.misc'), ',') . ' AS text,'
				. $query->concatenate(array($db->quote($section), 'c.title'), ' / ') . ' AS section,'
				. '\'2\' AS browsernav'
		);
		$query->from('#__contact_details AS a')
			->join('INNER', '#__categories AS c ON c.id = a.catid')
			->where(
				'(a.name LIKE ' . $text . ' OR a.misc LIKE ' . $text . ' OR a.con_position LIKE ' . $text
					. ' OR a.address LIKE ' . $text . ' OR a.suburb LIKE ' . $text . ' OR a.state LIKE ' . $text
					. ' OR a.country LIKE ' . $text . ' OR a.postcode LIKE ' . $text . ' OR a.telephone LIKE ' . $text
					. ' OR a.fax LIKE ' . $text . ') AND a.published IN (' . implode(',', $state) . ') AND c.published=1 '
					. ' AND a.access IN (' . $groups . ') AND c.access IN (' . $groups . ')'
			)
			->order($order);

		// Filter by language.
		if ($app->isClient('site') && JLanguageMultilang::isEnabled())
		{
			$tag = JFactory::getLanguage()->getTag();
			$query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
				->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
		}

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
			foreach ($rows as $key => $row)
			{
				$rows[$key]->href  = ContactHelperRoute::getContactRoute($row->slug, $row->catslug);
				$rows[$key]->text  = $row->title;
				$rows[$key]->text .= $row->con_position ? ', ' . $row->con_position : '';
				$rows[$key]->text .= $row->misc ? ', ' . $row->misc : '';
			}
		}

		return $rows;
	}
}
