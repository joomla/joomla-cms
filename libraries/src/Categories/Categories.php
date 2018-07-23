<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

/**
 * Categories Class.
 *
 * @since  1.6
 */
class Categories
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    Categories[]
	 * @since  1.6
	 */
	public static $instances = array();

	/**
	 * Array of category nodes
	 *
	 * @var    CategoryNode[]
	 * @since  1.6
	 */
	protected $_nodes;

	/**
	 * Array of checked categories -- used to save values when _nodes are null
	 *
	 * @var    boolean[]
	 * @since  1.6
	 */
	protected $_checkedCategories;

	/**
	 * Name of the extension the categories belong to
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_extension = null;

	/**
	 * Name of the linked content table to get category content count
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_table = null;

	/**
	 * Name of the category field
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_field = null;

	/**
	 * Name of the key field
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_key = null;

	/**
	 * Name of the items state field
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_statefield = null;

	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $_options = null;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.6
	 */
	public function __construct($options)
	{
		$this->_extension  = $options['extension'];
		$this->_table      = $options['table'];
		$this->_field      = isset($options['field']) && $options['field'] ? $options['field'] : 'catid';
		$this->_key        = isset($options['key']) && $options['key'] ? $options['key'] : 'id';
		$this->_statefield = isset($options['statefield']) ? $options['statefield'] : 'state';

		$options['access']      = isset($options['access']) ? $options['access'] : 'true';
		$options['published']   = isset($options['published']) ? $options['published'] : 1;
		$options['countItems']  = isset($options['countItems']) ? $options['countItems'] : 0;
		$options['currentlang'] = Multilanguage::isEnabled() ? Factory::getLanguage()->getTag() : 0;

		$this->_options = $options;
	}

	/**
	 * Returns a reference to a Categories object
	 *
	 * @param   string  $extension  Name of the categories extension
	 * @param   array   $options    An array of options
	 *
	 * @return  Categories|boolean  Categories object on success, boolean false if an object does not exist
	 *
	 * @since   1.6
	 */
	public static function getInstance($extension, $options = array())
	{
		$hash = md5(strtolower($extension) . serialize($options));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		$parts = explode('.', $extension);
		$component = 'com_' . strtolower($parts[0]);
		$section = count($parts) > 1 ? $parts[1] : '';
		$classname = ucfirst(substr($component, 4)) . ucfirst($section) . 'Categories';

		if (!class_exists($classname))
		{
			$path = JPATH_SITE . '/components/' . $component . '/helpers/category.php';

			\JLoader::register($classname, $path);

			if (!class_exists($classname))
			{
				return false;
			}
		}

		self::$instances[$hash] = new $classname($options);

		return self::$instances[$hash];
	}

	/**
	 * Loads a specific category and all its children in a CategoryNode object
	 *
	 * @param   mixed    $id         an optional id integer or equal to 'root'
	 * @param   boolean  $forceload  True to force  the _load method to execute
	 *
	 * @return  CategoryNode|null|boolean  CategoryNode object or null if $id is not valid
	 *
	 * @since   1.6
	 */
	public function get($id = 'root', $forceload = false)
	{
		if ($id !== 'root')
		{
			$id = (int) $id;

			if ($id == 0)
			{
				$id = 'root';
			}
		}

		// If this $id has not been processed yet, execute the _load method
		if ((!isset($this->_nodes[$id]) && !isset($this->_checkedCategories[$id])) || $forceload)
		{
			$this->_load($id);
		}

		// If we already have a value in _nodes for this $id, then use it.
		if (isset($this->_nodes[$id]))
		{
			return $this->_nodes[$id];
		}
		// If we processed this $id already and it was not valid, then return null.
		elseif (isset($this->_checkedCategories[$id]))
		{
			return;
		}

		return false;
	}

	/**
	 * Load method
	 *
	 * @param   integer  $id  Id of category to load
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _load($id)
	{
		/** @var JDatabaseDriver */
		$db   = Factory::getDbo();
		$app  = Factory::getApplication();
		$user = Factory::getUser();
		$extension = $this->_extension;

		// Record that has this $id has been checked
		$this->_checkedCategories[$id] = true;

		$query = $db->getQuery(true)
			->select('c.id, c.asset_id, c.access, c.alias, c.checked_out, c.checked_out_time,
				c.created_time, c.created_user_id, c.description, c.extension, c.hits, c.language, c.level,
				c.lft, c.metadata, c.metadesc, c.metakey, c.modified_time, c.note, c.params, c.parent_id,
				c.path, c.published, c.rgt, c.title, c.modified_user_id, c.version'
			);

		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('c.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';

		$query->select($case_when)
			->where('(c.extension=' . $db->quote($extension) . ' OR c.extension=' . $db->quote('system') . ')');

		if ($this->_options['access'])
		{
			$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		if ($this->_options['published'] == 1)
		{
			$query->where('c.published = 1');
		}

		$query->order('c.lft');

		// Note: s for selected id
		if ($id != 'root')
		{
			// Get the selected category
			$query->from($db->quoteName('#__categories', 's'))
				->where('s.id = ' . (int) $id);

			if ($app->isClient('site') && Multilanguage::isEnabled())
			{
				// For the most part, we use c.lft column, which index is properly used instead of c.rgt
				$query->innerJoin(
					$db->quoteName('#__categories', 'c')
					. ' ON (s.lft < c.lft AND c.lft < s.rgt AND c.language IN ('
					. $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . '))'
					. ' OR (c.lft <= s.lft AND s.rgt <= c.rgt)'
				);
			}
			else
			{
				$query->innerJoin(
					$db->quoteName('#__categories', 'c')
					. ' ON (s.lft <= c.lft AND c.lft < s.rgt)'
					. ' OR (c.lft < s.lft AND s.rgt < c.rgt)'
				);
			}
		}
		else
		{
			$query->from($db->quoteName('#__categories', 'c'));

			if ($app->isClient('site') && Multilanguage::isEnabled())
			{
				$query->where('c.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
			}
		}

		// Note: i for item
		if ($this->_options['countItems'] == 1)
		{
			$subQuery = $db->getQuery(true)
				->select('COUNT(i.' . $db->quoteName($this->_key) . ')')
				->from($db->quoteName($this->_table, 'i'))
				->where('i.' . $db->quoteName($this->_field) . ' = c.id');

			if ($this->_options['published'] == 1)
			{
				$subQuery->where('i.' . $this->_statefield . ' = 1');
			}

			if ($this->_options['currentlang'] !== 0)
			{
				$subQuery->where('(i.language = ' . $db->quote('*')
					. ' OR i.language = ' . $db->quote($this->_options['currentlang']) . ')'
				);
			}

			$query->select('(' . $subQuery . ') AS numitems');
		}

		// Get the results
		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		$childrenLoaded = false;

		if (count($results))
		{
			// Foreach categories
			foreach ($results as $result)
			{
				// Deal with root category
				if ($result->id == 1)
				{
					$result->id = 'root';
				}

				// Deal with parent_id
				if ($result->parent_id == 1)
				{
					$result->parent_id = 'root';
				}

				// Create the node
				if (!isset($this->_nodes[$result->id]))
				{
					// Create the CategoryNode and add to _nodes
					$this->_nodes[$result->id] = new CategoryNode($result, $this);

					// If this is not root and if the current node's parent is in the list or the current node parent is 0
					if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 1))
					{
						// Compute relationship between node and its parent - set the parent in the _nodes field
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}

					// If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
					// then remove the node from the list
					if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->id]);
						continue;
					}

					if ($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}
				}
				elseif ($result->id == $id || $childrenLoaded)
				{
					// Create the CategoryNode
					$this->_nodes[$result->id] = new CategoryNode($result, $this);

					if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id))
					{
						// Compute relationship between node and its parent
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}

					// If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
					// then remove the node from the list
					if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->id]);
						continue;
					}

					if ($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}
				}
			}
		}
		else
		{
			$this->_nodes[$id] = null;
		}
	}
}
