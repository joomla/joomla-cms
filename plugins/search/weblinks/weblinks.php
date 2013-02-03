<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_weblinks/helpers/route.php';

/**
 * Weblinks Search plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Search.weblinks
 * @since		1.6
 */
class plgSearchWeblinks extends JPlugin
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
	function onContentSearchAreas() {
		static $areas = array(
			'weblinks' => 'PLG_SEARCH_WEBLINKS_WEBLINKS'
			);
			return $areas;
	}

	/**
	 * Weblink Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
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
		$state = array();
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
		$section	= JText::_('PLG_SEARCH_WEBLINKS');

		$wheres	= array();
		switch ($phrase)
		{
			case 'exact':
				$text		= $db->Quote('%'.$db->escape($text, true).'%', false);
				$wheres2	= array();
				$wheres2[]	= 'a.url LIKE '.$text;
				$wheres2[]	= 'a.description LIKE '.$text;
				$wheres2[]	= 'a.title LIKE '.$text;
				$where		= '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words	= explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word		= $db->Quote('%'.$db->escape($word, true).'%', false);
					$wheres2	= array();
					$wheres2[]	= 'a.url LIKE '.$word;
					$wheres2[]	= 'a.description LIKE '.$word;
					$wheres2[]	= 'a.title LIKE '.$word;
					$wheres[]	= implode(' OR ', $wheres2);
				}
				$where	= '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
		}

		$return = array();
		if (!empty($state)) {
			$query	= $db->getQuery(true);
	        //sqlsrv changes
	        $case_when = ' CASE WHEN ';
	        $case_when .= $query->charLength('a.alias');
	        $case_when .= ' THEN ';
	        $a_id = $query->castAsChar('a.id');
	        $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
	        $case_when .= ' ELSE ';
	        $case_when .= $a_id.' END as slug';

	        $case_when1 = ' CASE WHEN ';
	        $case_when1 .= $query->charLength('c.alias');
	        $case_when1 .= ' THEN ';
	        $c_id = $query->castAsChar('c.id');
	        $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
	        $case_when1 .= ' ELSE ';
	        $case_when1 .= $c_id.' END as catslug';

			$query->select('a.title AS title, a.description AS text, a.created AS created, a.url, '
						.$case_when.','.$case_when1.', '
						.$query->concatenate(array($db->Quote($section), "c.title"), " / ").' AS section, \'1\' AS browsernav');
			$query->from('#__weblinks AS a');
			$query->innerJoin('#__categories AS c ON c.id = a.catid');
			$query->where('('.$where.')' . ' AND a.state in ('.implode(',', $state).') AND  c.published=1 AND  c.access IN ('.$groups.')');
			$query->order($order);

			// Filter by language
			if ($app->isSite() && $app->getLanguageFilter()) {
				$tag = JFactory::getLanguage()->getTag();
				$query->where('a.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
				$query->where('c.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
			}

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			$return = array();
			if ($rows) {
				foreach($rows as $key => $row) {
					$rows[$key]->href = WeblinksHelperRoute::getWeblinkRoute($row->slug, $row->catslug);
				}

				foreach($rows as $key => $weblink) {
					if (searchHelper::checkNoHTML($weblink, $searchText, array('url', 'text', 'title'))) {
						$return[] = $weblink;
					}
				}
			}
		}
		return $return;
	}
}
