<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Multilanguage;

/**
 * Menu class
 *
 * @since  1.5
 */
class SiteMenu extends AbstractMenu
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  3.5
	 */
	protected $app;

	/**
	 * Database driver
	 *
	 * @var    \JDatabaseDriver
	 * @since  3.5
	 */
	protected $db;

	/**
	 * Language object
	 *
	 * @var    Language
	 * @since  3.5
	 */
	protected $language;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  An array of configuration options.
	 *
	 * @since   1.5
	 */
	public function __construct($options = array())
	{
		// Extract the internal dependencies before calling the parent constructor since it calls $this->load()
		$this->app      = isset($options['app']) && $options['app'] instanceof CMSApplication ? $options['app'] : \JFactory::getApplication();
		$this->db       = isset($options['db']) && $options['db'] instanceof \JDatabaseDriver ? $options['db'] : \JFactory::getDbo();
		$this->language = isset($options['language']) && $options['language'] instanceof Language ? $options['language'] : \JFactory::getLanguage();

		parent::__construct($options);
	}

	/**
	 * Loads the entire menu table into memory.
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   1.5
	 */
	public function load()
	{
		// For PHP 5.3 compat we can't use $this in the lambda function below
		$db = $this->db;

		$loader = function () use ($db)
		{
			$query = $db->getQuery(true)
				->select('m.id, m.menutype, m.title, m.alias, m.note, m.path AS route, m.link, m.type, m.level, m.language')
				->select($db->quoteName('m.browserNav') . ', m.access, m.params, m.home, m.img, m.template_style_id, m.component_id, m.parent_id')
				->select('e.element as component')
				->from('#__menu AS m')
				->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
				->where('m.published = 1')
				->where('m.parent_id > 0')
				->where('m.client_id = 0')
				->order('m.lft');

			// Set the query
			$db->setQuery($query);

			return $db->loadObjectList('id', 'Joomla\\CMS\\Menu\\MenuItem');
		};

		try
		{
			/** @var \JCacheControllerCallback $cache */
			$cache = \JFactory::getCache('com_menus', 'callback');

			$this->_items = $cache->get($loader, array(), md5(get_class($this)), false);
		}
		catch (\JCacheException $e)
		{
			try
			{
				$this->_items = $loader();
			}
			catch (\JDatabaseExceptionExecuting $databaseException)
			{
				\JError::raiseWarning(500, \JText::sprintf('JERROR_LOADING_MENUS', $databaseException->getMessage()));

				return false;
			}
		}
		catch (\JDatabaseExceptionExecuting $e)
		{
			\JError::raiseWarning(500, \JText::sprintf('JERROR_LOADING_MENUS', $e->getMessage()));

			return false;
		}

		foreach ($this->_items as &$item)
		{
			// Get parent information.
			$parent_tree = array();

			if (isset($this->_items[$item->parent_id]))
			{
				$parent_tree  = $this->_items[$item->parent_id]->tree;
			}

			// Create tree.
			$parent_tree[] = $item->id;
			$item->tree = $parent_tree;

			// Create the query array.
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);

			parse_str($url, $item->query);
		}

		return true;
	}

	/**
	 * Gets menu items by attribute
	 *
	 * @param   string   $attributes  The field name
	 * @param   string   $values      The value of the field
	 * @param   boolean  $firstonly   If true, only returns the first item found
	 *
	 * @return  MenuItem|MenuItem[]  An array of menu item objects or a single object if the $firstonly parameter is true
	 *
	 * @since   1.6
	 */
	public function getItems($attributes, $values, $firstonly = false)
	{
		$attributes = (array) $attributes;
		$values     = (array) $values;

		if ($this->app->isClient('site'))
		{
			// Filter by language if not set
			if (($key = array_search('language', $attributes)) === false)
			{
				if (Multilanguage::isEnabled())
				{
					$attributes[] = 'language';
					$values[]     = array(\JFactory::getLanguage()->getTag(), '*');
				}
			}
			elseif ($values[$key] === null)
			{
				unset($attributes[$key], $values[$key]);
			}

			// Filter by access level if not set
			if (($key = array_search('access', $attributes)) === false)
			{
				$attributes[] = 'access';
				$values[] = $this->user->getAuthorisedViewLevels();
			}
			elseif ($values[$key] === null)
			{
				unset($attributes[$key], $values[$key]);
			}
		}

		// Reset arrays or we get a notice if some values were unset
		$attributes = array_values($attributes);
		$values = array_values($values);

		return parent::getItems($attributes, $values, $firstonly);
	}

	/**
	 * Get menu item by id
	 *
	 * @param   string  $language  The language code.
	 *
	 * @return  MenuItem|null  The item object or null when not found for given language
	 *
	 * @since   1.6
	 */
	public function getDefault($language = '*')
	{
		if (array_key_exists($language, $this->_default) && $this->app->isClient('site') && $this->app->getLanguageFilter())
		{
			return $this->_items[$this->_default[$language]];
		}

		if (array_key_exists('*', $this->_default))
		{
			return $this->_items[$this->_default['*']];
		}

		return;
	}
}
