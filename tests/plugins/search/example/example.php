<?php
/**
 * @package        Joomla
 * @copyright      Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This example search plugin searches banner description for the search text.
 * To aid understanding, I've avoided some complexity found in other plugins.  PN 25-Mar-11
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Example Search plugin
 *
 * @package        Joomla
 * @subpackage     Search
 * @since          1.6
 */
class plgSearchExample extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		//Loads the plugin language file:
		$this->loadLanguage();
	}

	/**
	 * Sets the checkbox(es) to be diplayed in the Search Only box:
	 * @return array An array of search areas
	 */
	function onContentSearchAreas()
	{
		static $areas = array(
			'Example' => 'PLG_SEARCH_EXAMPLE_BANNERS'
		);
		return $areas;
	}

	/**
	 * Example Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine:
	 * - title;
	 * - href:         link associated with the title;
	 * - browsernav    if 1, link opens in a new window, otherwise in the same window;
	 * - section       in parenthesis below the title;
	 * - text;
	 * - created;
	 * @param string Target search string
	 * @param string matching option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed  An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db = JFactory::getDbo();

		//Set the database query offset & limit (may be parameterised):
		$offset = 0;
		$limit = 50;

		//Check that the this search area has been selected:
		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		//Strip white space from the search term:
		$searchText = trim($text);

		//If no search text, exit:
		if ($searchText == '')
		{
			return array();
		}

		//Initialise the array of where statements:
		$wheres = array();

		//Swiching on the string matching option (exact|any|all)...
		switch ($phrase)
		{
			//Exact match - match the whole search text:
			case 'exact':
				//Prepare the search text to be a mySQL wildcard and construct the where statement:
				$searchText = $db->quote('%' . $searchText . '%', true);
				$where = 'b.description LIKE ' . $searchText;
				break;
			case 'all':
			case 'any':
			default:
				//Convert the words to an arry:
				$words = explode(' ', $searchText);

				//Initialise the array of where statements:
				$wheres = array();

				//For each word in the search text...
				foreach ($words as $word)
				{
					//Prepare the search text as a mySQL wildcard and append to the where statement array:
					$word = $db->quote('%' . $word . '%', true);
					//$wheres	= array();
					$wheres[] = 'b.description LIKE ' . $word;
				}
				//Concatenate the where statements:
				$operator = $phrase == 'all' ? 'AND' : 'OR';
				$where = '(' . implode(' ' . $operator . ' ', $wheres) . ')';
				break;
		}

		//Switch on ordering (in this case trivial):
		switch ($ordering)
		{
			case 'oldest':
			case 'popular':
			case 'alpha':
			case 'category':
			case 'newest':
			default:
				$order = 'b.created DESC';
		}

		//Get a new query object:
		$query = $db->getQuery(true);

		//Construct the query:
		$query->select(
			'b.name AS title, b.clickurl as href, "1" AS browsernav, ' .
				'c.title AS section, b.description AS text, b.created AS created'
		);
		$query->from('#__banners AS b')
			->join('INNER', '#__categories AS c ON c.id = b.catid')
			->where('(' . $where . ') AND (b.state=1) AND  (c.published=1)')
			->order($order);

		//Prepare & execute the query - offset & limit can be parameterised:
		$db->setQuery($query, $offset, $limit);
		$rows = $db->loadObjectList();

		/*
		The resulting executed query will be similar to this...
			SELECT
			  b.name AS title,
			  b.clickurl as href,
			  "1" AS browsernav,
			  c.title AS section,
			  b.description AS text,
			  b.created AS created
			FROM j16_banners AS b
			INNER JOIN j16_categories AS c
			  ON c.id = b.catid
			WHERE ((b.description LIKE '%yourstring%'))
			  AND (b.state=1)
			  AND (c.published=1)
			ORDER BY b.created DESC
			LIMIT 0, 50
		*/

		//Initialise the return array:
		$return = array();

		//If there's data...
		if ($rows)
		{
			//For each row of data...
			foreach ($rows AS $key => $banner)
			{
				//If the search text can be found even after stripping HTML
				if (searchHelper::checkNoHTML($banner, $text, array('text')))
				{
					//Append to the return array:
					$return[] = $banner;
				}
			}
		}
		return $return;
	}
}
