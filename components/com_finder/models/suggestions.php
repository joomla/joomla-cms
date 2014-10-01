<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

define('FINDER_PATH_INDEXER', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer');
JLoader::register('FinderIndexerHelper', FINDER_PATH_INDEXER . '/helper.php');

/**
 * Suggestions model class for the Finder package.
 *
 * @since       2.5
 */
class FinderModelSuggestions extends JModelList
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'com_finder.suggestions';

	/**
	 * Method to get an array of data items.
	 *
	 * @return  array  An array of data items.
	 *
	 * @since   2.5
	 */
	public function getItems()
	{
		// Get the items.
		$items = parent::getItems();

		// Convert them to a simple array.
		foreach ($items as $k => $v)
		{
			$items[$k] = $v->term;
		}

		return $items;
	}

	/**
	 * Method to build a database query to load the list data.
	 *
	 * @return  JDatabaseQuery  A database query
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		if (in_array($db->name, array('mysqli', 'mysql'))){
			//attempt to change mysql for error in large select
			$db->setQuery('SET SQL_BIG_SELECTS=1');
			$db->query();
		}
		$query = $db->getQuery(true);

		// Set variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$input = $app->input;
		$request = $input->request;

		// Select required fields
		$query->select('t.term')
			->from($db->quoteName('#__finder_terms') . ' AS t')
			->where('t.term LIKE ' . $db->quote($db->escape($this->getState('input'), true) . '%'))
			->where('t.common = 0')
			->where('t.language IN (' . $db->quote($db->escape($this->getState('language'), true)) . ', ' . $db->quote('*') . ')')
			->order('t.links DESC')
			->order('t.weight DESC');

		$linkjoin = '';

		// Iterate through each term mapping table and add the join.
		for ($i = 0; $i < 16; $i++)
		{
			// We use the offset because each join needs a unique alias.
			$query->join('LEFT', $db->quoteName('#__finder_links_terms'.dechex($i)) . ' AS lterms'. $i .' ON lterms'. $i .'.term_id = t.term_id');
			$linkjoin .= 'lterms'.$i.'.link_id=l.link_id';
			if($i < 15)
				$linkjoin .= ' or ';
		}
		$query->join('INNER', $db->quoteName('#__finder_links') . ' AS l ON ('.$linkjoin.')')
			->where($db->quoteName('l.access') . ' IN (' . $groups . ')')
			->where($db->quoteName('l.state') . ' = 1');

		// Get the null date and the current date, minus seconds.
		$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(substr_replace(JFactory::getDate()->toSQL(), '00', -2));

		// Add the publish up and publish down filters.
		$query->where('(' . $db->quoteName('l.publish_start_date') . ' = ' . $nullDate . ' OR ' . $db->quoteName('l.publish_start_date') . ' <= ' . $nowDate . ')')
			->where('(' . $db->quoteName('l.publish_end_date') . ' = ' . $nullDate . ' OR ' . $db->quoteName('l.publish_end_date') . ' >= ' . $nowDate . ')');

		if (!is_null($request->get('f')))
		{
			$query->join('INNER', $db->quoteName('#__finder_taxonomy_map') . ' AS tm ON (tm.link_id=l.link_id)')
				->join('INNER', $db->quoteName('#__finder_filters') . ' AS ff ON (ff.data=tm.node_id)')
				->where($db->quoteName('ff.filter_id') . ' = '.$request->get('f', '', 'int'));

		}
/*
 * 		Didn't know what these do, so i commented them out
 *
		// Add the start date filter to the query.
		if (!empty($this->query->date1))
		{
			// Escape the date.
			$date1 = $db->quote($this->query->date1);

			// Add the appropriate WHERE condition.
			if ($this->query->when1 == 'before')
			{
				$query->where($db->quoteName('l.start_date') . ' <= ' . $date1);
			}
			elseif ($this->query->when1 == 'after')
			{
				$query->where($db->quoteName('l.start_date') . ' >= ' . $date1);
			}
			else
			{
				$query->where($db->quoteName('l.start_date') . ' = ' . $date1);
			}
		}
		// Add the end date filter to the query.
		if (!empty($this->query->date2))
		{
			// Escape the date.
			$date2 = $db->quote($this->query->date2);

			// Add the appropriate WHERE condition.
			if ($this->query->when2 == 'before')
			{
				$query->where($db->quoteName('l.start_date') . ' <= ' . $date2);
			}
			elseif ($this->query->when2 == 'after')
			{
				$query->where($db->quoteName('l.start_date') . ' >= ' . $date2);
			}
			else
			{
				$query->where($db->quoteName('l.start_date') . ' = ' . $date2);
			}
		}
*/
		return $query;
	}

	/**
	 * Method to get a store id based on model the configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id. [optional]
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.5
	 */
	protected function getStoreId($id = '')
	{
		// Add the search query state.
		$id .= ':' . $this->getState('input');
		$id .= ':' . $this->getState('language');

		// Add the list state.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.  Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Get the configuration options.
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = JComponentHelper::getParams('com_finder');
		$user = JFactory::getUser();

		// Get the query input.
		$this->setState('input', $input->request->get('q', '', 'string'));

		// Set the query language
		if (JLanguageMultilang::isEnabled())
		{
			$lang = JFactory::getLanguage()->getTag();
		}
		else
		{
			$lang = FinderIndexerHelper::getDefaultLanguage();
		}

		$lang = FinderIndexerHelper::getPrimaryLanguage($lang);
		$this->setState('language', $lang);

		// Load the list state.
		$this->setState('list.start', 0);
		$this->setState('list.limit', 10);

		// Load the parameters.
		$this->setState('params', $params);

		// Load the user state.
		$this->setState('user.id', (int) $user->get('id'));
	}
}
