<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;

/**
 * Base class for a database aware Joomla Model
 *
 * Acts as a Factory class for application specific objects and provides many supporting API functions.
 *
 * @since  2.5.5
 */
abstract class BaseDatabaseModel extends BaseModel implements DatabaseModelInterface
{
	use DatabaseAwareTrait;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $option = null;

	/**
	 * The event to trigger when cleaning cache.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $event_clean_cache = null;

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		// Guess the option from the class name (Option)Model(View).
		if (empty($this->option))
		{
			$r = null;

			if (!preg_match('/(.*)Model/i', get_class($this), $r))
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}

			$this->option = ComponentHelper::getComponentName($this, $r[1]);
		}

		$this->setDb(array_key_exists('dbo', $config) ? $config['dbo'] : Factory::getDbo());

		// Set the default view search path
		if (array_key_exists('table_path', $config))
		{
			$this->addTablePath($config['table_path']);
		}
		// @codeCoverageIgnoreStart
		elseif (defined('JPATH_COMPONENT_ADMINISTRATOR'))
		{
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/table');
		}
		// @codeCoverageIgnoreEnd

		// Set the clean cache event
		if (isset($config['event_clean_cache']))
		{
			$this->event_clean_cache = $config['event_clean_cache'];
		}
		elseif (empty($this->event_clean_cache))
		{
			$this->event_clean_cache = 'onContentCleanCache';
		}
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since   3.0
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->getDb()->setQuery($query, $limitstart, $limit);

		return $this->getDb()->loadObjectList();
	}

	/**
	 * Returns a record count for the query.
	 *
	 * @param   \JDatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   3.0
	 */
	protected function _getListCount($query)
	{
		// Use fast COUNT(*) on \JDatabaseQuery objects if there is no GROUP BY or HAVING clause:
		if ($query instanceof \JDatabaseQuery
			&& $query->type == 'select'
			&& $query->group === null
			&& $query->merge === null
			&& $query->querySet === null
			&& $query->having === null)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');

			$this->getDb()->setQuery($query);

			return (int) $this->getDb()->loadResult();
		}

		// Otherwise fall back to inefficient way of counting all results.

		// Remove the limit and offset part if it's a \JDatabaseQuery object
		if ($query instanceof \JDatabaseQuery)
		{
			$query = clone $query;
			$query->clear('limit')->clear('offset');
		}

		$this->getDb()->setQuery($query);
		$this->getDb()->execute();

		return (int) $this->getDb()->getNumRows();
	}

	/**
	 * Method to load and return a table object.
	 *
	 * @param   string  $name    The name of the view
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration settings to pass to \JTable::getInstance
	 *
	 * @return  Table|boolean  Table object or boolean false if failed
	 *
	 * @since   3.0
	 * @see     \JTable::getInstance()
	 */
	protected function _createTable($name, $prefix = 'Table', $config = array())
	{
		// Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config))
		{
			$config['dbo'] = $this->getDb();
		}

		return $this->getMVCFactory()->createTable($name, $prefix, $config);
	}

	/**
	 * Method to get the database driver object
	 *
	 * @return  DatabaseDriver
	 *
	 * @since       3.0
	 * @deprecated  5.0 Use getDb() instead
	 */
	public function getDbo()
	{
		return $this->getDb();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($name = '', $prefix = '', $options = array())
	{
		if (empty($name))
		{
			$name = $this->getName();
		}

		// We need this ugly code to deal with non-namespaced MVC code
		if (empty($prefix) && $this->getMVCFactory() instanceof LegacyFactory)
		{
			$prefix = 'Table';
		}

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}

	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer  $version_id  Key to the version history table.
	 * @param   Table    &$table      Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.2
	 */
	public function loadHistory($version_id, Table &$table)
	{
		// Only attempt to check the row in if it exists, otherwise do an early exit.
		if (!$version_id)
		{
			return false;
		}

		// Get an instance of the row to checkout.
		$historyTable = Table::getInstance('Contenthistory');

		if (!$historyTable->load($version_id))
		{
			$this->setError($historyTable->getError());

			return false;
		}

		$rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));
		$typeId   = Table::getInstance('Contenttype')->getTypeId($this->typeAlias);

		if ($historyTable->ucm_type_id != $typeId)
		{
			$this->setError(\JText::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));

			$key = $table->getKeyName();

			if (isset($rowArray[$key]))
			{
				$table->checkIn($rowArray[$key]);
			}

			return false;
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}

	/**
	 * Method to check if the given record is checked out by the current user
	 *
	 * @param   \stdClass  $item  The record to check
	 *
	 * @return  bool
	 */
	public function isCheckedOut($item)
	{
		$table = $this->getTable();
		$checkedOutField = $table->getColumnAlias('checked_out');

		if (property_exists($item, $checkedOutField) && $item->{$checkedOutField} != \JFactory::getUser()->id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to set the database driver object
	 *
	 * @param   DatabaseDriver  $db  A DatabaseDriver based object
	 *
	 * @return  void
	 *
	 * @since       3.0
	 * @deprecated  5.0 Use setDb() instead
	 */
	public function setDbo($db)
	{
		$this->setDb($db);
	}

	/**
	 * Clean the cache
	 *
	 * @param   string  $group  The cache group
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function cleanCache($group = null)
	{
		$conf = \JFactory::getConfig();

		$options = [
			'defaultgroup' => $group ?: ($this->option ?? \JFactory::getApplication()->input->get('option')),
			'cachebase'    => $conf->get('cache_path', JPATH_CACHE),
			'result'       => true,
		];

		try
		{
			/** @var \JCacheControllerCallback $cache */
			$cache = \JCache::getInstance('callback', $options);
			$cache->clean();
		}
		catch (\JCacheException $exception)
		{
			$options['result'] = false;
		}

		// Trigger the onContentCleanCache event.
		\JFactory::getApplication()->triggerEvent($this->event_clean_cache, $options);
	}

	/**
	 * Boots the component with the given name.
	 *
	 * @param   string  $component  The component name, eg. com_content.
	 *
	 * @return  ComponentInterface  The service container
	 *
	 * @since   4.0.0
	 */
	protected function bootComponent($component): ComponentInterface
	{
		return Factory::getApplication()->bootComponent($component);
	}
}
