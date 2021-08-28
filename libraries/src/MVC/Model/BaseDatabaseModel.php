<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseQuery;

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
	use MVCFactoryAwareTrait;

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
		parent::__construct($config);

		// Guess the option from the class name (Option)Model(View).
		if (empty($this->option))
		{
			$r = null;

			if (!preg_match('/(.*)Model/i', \get_class($this), $r))
			{
				throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
			}

			$this->option = ComponentHelper::getComponentName($this, $r[1]);
		}

		$this->setDbo(\array_key_exists('dbo', $config) ? $config['dbo'] : Factory::getDbo());

		// Set the default view search path
		if (\array_key_exists('table_path', $config))
		{
			$this->addTablePath($config['table_path']);
		}
		// @codeCoverageIgnoreStart
		elseif (\defined('JPATH_COMPONENT_ADMINISTRATOR'))
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

		if ($factory)
		{
			$this->setMVCFactory($factory);

			return;
		}

		$component = Factory::getApplication()->bootComponent($this->option);

		if ($component instanceof MVCFactoryServiceInterface)
		{
			$this->setMVCFactory($component->getMVCFactory());
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
		if (\is_string($query))
		{
			$query = $this->getDbo()->getQuery(true)->setQuery($query);
		}

		$query->setLimit($limit, $limitstart);
		$this->getDbo()->setQuery($query);

		return $this->getDbo()->loadObjectList();
	}

	/**
	 * Returns a record count for the query.
	 *
	 * Note: Current implementation of this method assumes that getListQuery() returns a set of unique rows,
	 * thus it uses SELECT COUNT(*) to count the rows. In cases that getListQuery() uses DISTINCT
	 * then either this method must be overridden by a custom implementation at the derived Model Class
	 * or a GROUP BY clause should be used to make the set unique.
	 *
	 * @param   DatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   3.0
	 */
	protected function _getListCount($query)
	{
		// Use fast COUNT(*) on DatabaseQuery objects if there is no GROUP BY or HAVING clause:
		if ($query instanceof DatabaseQuery
			&& $query->type === 'select'
			&& $query->group === null
			&& $query->merge === null
			&& $query->querySet === null
			&& $query->having === null)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');

			$this->getDbo()->setQuery($query);

			return (int) $this->getDbo()->loadResult();
		}

		// Otherwise fall back to inefficient way of counting all results.

		// Remove the limit, offset and order parts if it's a DatabaseQuery object
		if ($query instanceof DatabaseQuery)
		{
			$query = clone $query;
			$query->clear('limit')->clear('offset')->clear('order');
		}

		$this->getDbo()->setQuery($query);
		$this->getDbo()->execute();

		return (int) $this->getDbo()->getNumRows();
	}

	/**
	 * Method to load and return a table object.
	 *
	 * @param   string  $name    The name of the view
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration settings to pass to Table::getInstance
	 *
	 * @return  Table|boolean  Table object or boolean false if failed
	 *
	 * @since   3.0
	 * @see     \JTable::getInstance()
	 */
	protected function _createTable($name, $prefix = 'Table', $config = array())
	{
		// Make sure we are returning a DBO object
		if (!\array_key_exists('dbo', $config))
		{
			$config['dbo'] = $this->getDbo();
		}

		return $this->getMVCFactory()->createTable($name, $prefix, $config);
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

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
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

		if (property_exists($item, $checkedOutField) && $item->{$checkedOutField} != Factory::getUser()->id)
		{
			return true;
		}

		return false;
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
		$app = Factory::getApplication();

		$options = [
			'defaultgroup' => $group ?: ($this->option ?? $app->input->get('option')),
			'cachebase'    => $app->get('cache_path', JPATH_CACHE),
			'result'       => true,
		];

		try
		{
			/** @var CallbackController $cache */
			$cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController('callback', $options);
			$cache->clean();
		}
		catch (CacheExceptionInterface $exception)
		{
			$options['result'] = false;
		}

		// Trigger the onContentCleanCache event.
		$app->triggerEvent($this->event_clean_cache, $options);
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
