<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Database\DatabaseQuery;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Suggestions model class for the Finder package.
 *
 * @since  2.5
 */
class SuggestionsModel extends ListModel
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
	 * @return  DatabaseQuery  A database query
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		$user   = Factory::getUser();
		$groups = ArrayHelper::toInteger($user->getAuthorisedViewLevels());
		$lang   = Helper::getPrimaryLanguage($this->getState('language'));

		// Create a new query object.
		$db = $this->getDbo();
		$termIdQuery = $db->getQuery(true);
		$termQuery = $db->getQuery(true);

		// Limit term count to a reasonable number of results to reduce main query join size
		$termIdQuery->select('ti.term_id')
			->from($db->quoteName('#__finder_terms', 'ti'))
			->where('ti.term LIKE ' . $db->quote($db->escape(StringHelper::strtolower($this->getState('input')), true) . '%', false))
			->where('ti.common = 0')
			->where('ti.language IN (' . $db->quote($lang) . ', ' . $db->quote('*') . ')')
			->order('ti.links DESC')
			->order('ti.weight DESC');

		$termIds = $db->setQuery($termIdQuery, 0, 100)->loadColumn();

		// Early return on term mismatch
		if (!count($termIds))
		{
			return $termIdQuery;
		}

		// Select required fields
		$termQuery->select('DISTINCT(t.term)')
			->from($db->quoteName('#__finder_terms', 't'))
			->whereIn('t.term_id', $termIds)
			->order('t.links DESC')
			->order('t.weight DESC');

		// Join mapping table for term <-> link relation
		$mappingTable = $db->quoteName('#__finder_links_terms', 'tm');
		$termQuery->join('INNER', $mappingTable . ' ON tm.term_id = t.term_id');

		// Join links table
		$termQuery->join('INNER', $db->quoteName('#__finder_links', 'l') . ' ON (tm.link_id = l.link_id)')
			->where('l.access IN (' . implode(',', $groups) . ')')
			->where('l.state = 1')
			->where('l.published = 1');

		return $termQuery;
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
		$app = Factory::getApplication();
		$input = $app->input;
		$params = ComponentHelper::getParams('com_finder');
		$user = Factory::getUser();

		// Get the query input.
		$this->setState('input', $input->request->get('q', '', 'string'));

		// Set the query language
		if (Multilanguage::isEnabled())
		{
			$lang = Factory::getLanguage()->getTag();
		}
		else
		{
			$lang = Helper::getDefaultLanguage();
		}

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
