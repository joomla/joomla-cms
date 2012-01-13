<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_content/helpers/route.php';

/**
 * Categories Search plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Search.categories
 * @since		1.6
 */
class plgSearchCategories extends JPlugin
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
		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas()
	{
		static $areas = array(
		'categories' => 'PLG_SEARCH_CATEGORIES_CATEGORIES'
		);
		return $areas;
	}

	/**
	 * Categories Search method
	 *
	 * The sql must return the following fields that are
	 * used in a common display routine: href, title, section, created, text,
	 * browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= JFactory::getDbo();
		$user	= JFactory::getUser();
		$app	= JFactory::getApplication();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$searchText = $text;

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		$sContent		= $this->params->get('search_content',		1);
		$sArchived		= $this->params->get('search_archived',		1);
		$limit			= $this->params->def('search_limit',		50);
		$state			= array();
		if ($sContent) {
			$state[]=1;
		}
		if ($sArchived) {
			$state[]=2;
		}


		$text = trim($text);
		if ($text == '') {
			return array();
		}

		switch($phrase) {
			case 'exact':
				$text		= $db->Quote('%'.$db->escape($text, true).'%', false);
				$wheres2 	= array();
				$wheres2[]	= 'a.title LIKE '.$text;
				$wheres2[]	= 'a.description LIKE '.$text;
				$where		= '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'any':
			case 'all';
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word) {
					$word		= $db->Quote('%'.$db->escape($word, true).'%', false);
					$wheres2 	= array();
					$wheres2[]	= 'a.title LIKE '.$word;
					$wheres2[]	= 'a.description LIKE '.$word;
					$wheres[]	= implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering) {
			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
			case 'popular':
			case 'newest':
			case 'oldest':
			default:
				$order = 'a.title DESC';
		}

		$text	= $db->Quote('%'.$db->escape($text, true).'%', false);
		$query	= $db->getQuery(true);

		$return = array();
		if (!empty($state)) {
			//sqlsrv changes
			$case_when = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias');
			$case_when .= ' THEN ';
			$a_id = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id.' END as slug';
			$query->select('a.title, a.description AS text, "" AS created, "2" AS browsernav, a.id AS catid, ' . $case_when);
			$query->from('#__categories AS a');
			$query->where('(a.title LIKE '. $text .' OR a.description LIKE '. $text .') AND a.published IN ('.implode(',', $state).') AND a.extension = \'com_content\''
						.'AND a.access IN ('. $groups .')' );
			$query->group('a.id');
			$query->order($order);
			if ($app->isSite() && $app->getLanguageFilter()) {
				$query->where('a.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
			}

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			if ($rows) {
				$count = count($rows);
				for ($i = 0; $i < $count; $i++) {
					$rows[$i]->href = ContentHelperRoute::getCategoryRoute($rows[$i]->slug);
					$rows[$i]->section	= JText::_('JCATEGORY');
				}

				foreach($rows as $key => $category) {
					if (searchHelper::checkNoHTML($category, $searchText, array('name', 'title', 'text'))) {
						$return[] = $category;
					}
				}
			}
		}
		return $return;
	}
}
