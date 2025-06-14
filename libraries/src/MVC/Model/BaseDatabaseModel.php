<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Model\AfterCleanCacheEvent;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Exception\DatabaseNotFoundException;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\EventInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a database aware Joomla Model
 *
 * Acts as a Factory class for application specific objects and provides many supporting API functions.
 *
 * @since  2.5.5
 */
abstract class BaseDatabaseModel extends BaseModel implements
    DatabaseModelInterface,
    DispatcherAwareInterface,
    CurrentUserInterface,
    CacheControllerFactoryAwareInterface
{
    use DatabaseAwareTrait;
    use MVCFactoryAwareTrait;
    use DispatcherAwareTrait;
    use CurrentUserTrait;
    use CacheControllerFactoryAwareTrait;

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
     * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config);

        // Guess the option from the class name (Option)Model(View).
        if (empty($this->option)) {
            $r = null;

            if (!preg_match('/(.*)Model/i', \get_class($this), $r)) {
                throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
            }

            $this->option = ComponentHelper::getComponentName($this, $r[1]);
        }

        /**
         * @deprecated  4.3 will be Removed in 6.0
         *              Database instance is injected through the setter function,
         *              subclasses should not use the db instance in constructor anymore
         */
        $db = \array_key_exists('dbo', $config) ? $config['dbo'] : Factory::getDbo();

        if ($db) {
            @trigger_error('Database is not available in constructor in 6.0.', E_USER_DEPRECATED);
            $this->setDatabase($db);

            // Is needed, when models use the deprecated MVC DatabaseAwareTrait, as the trait is overriding the local functions
            $this->setDbo($db);
        }

        // Set the default view search path
        if (\array_key_exists('table_path', $config)) {
            static::addTablePath($config['table_path']);
        } elseif (\defined('JPATH_COMPONENT_ADMINISTRATOR')) {
            static::addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
            static::addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/table');
        }

        // Set the clean cache event
        if (isset($config['event_clean_cache'])) {
            $this->event_clean_cache = $config['event_clean_cache'];
        } elseif (empty($this->event_clean_cache)) {
            $this->event_clean_cache = 'onContentCleanCache';
        }

        if ($factory) {
            $this->setMVCFactory($factory);

            return;
        }

        $component = Factory::getApplication()->bootComponent($this->option);

        if ($component instanceof MVCFactoryServiceInterface) {
            $this->setMVCFactory($component->getMVCFactory());
        }
    }

    /**
     * Gets an array of objects from the results of database query.
     *
     * @param   DatabaseQuery|string   $query       The query.
     * @param   integer                $limitstart  Offset.
     * @param   integer                $limit       The number of records.
     *
     * @return  object[]  An array of results.
     *
     * @since   3.0
     * @throws  \RuntimeException
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        if (\is_string($query)) {
            $query = $this->getDatabase()->getQuery(true)->setQuery($query);
        }

        $query->setLimit($limit, $limitstart);
        $this->getDatabase()->setQuery($query);

        return $this->getDatabase()->loadObjectList();
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
        if (
            $query instanceof DatabaseQuery
            && $query->type === 'select'
            && $query->group === null
            && $query->merge === null
            && $query->querySet === null
            && $query->having === null
        ) {
            $query = clone $query;
            $query->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');

            $this->getDatabase()->setQuery($query);

            return (int) $this->getDatabase()->loadResult();
        }

        // Otherwise fall back to inefficient way of counting all results.

        // Remove the limit, offset and order parts if it's a DatabaseQuery object
        if ($query instanceof DatabaseQuery) {
            $query = clone $query;
            $query->clear('limit')->clear('offset')->clear('order');
        }

        $this->getDatabase()->setQuery($query);
        $this->getDatabase()->execute();

        return (int) $this->getDatabase()->getNumRows();
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
     * @see     Table::getInstance()
     */
    protected function _createTable($name, $prefix = 'Table', $config = [])
    {
        // Make sure we are returning a DBO object
        if (!\array_key_exists('dbo', $config)) {
            $config['dbo'] = $this->getDatabase();
        }

        $table = $this->getMVCFactory()->createTable($name, $prefix, $config);

        if ($table instanceof CurrentUserInterface) {
            $table->setCurrentUser($this->getCurrentUser());
        }

        return $table;
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
    public function getTable($name = '', $prefix = '', $options = [])
    {
        if (empty($name)) {
            $name = $this->getName();
        }

        // We need this ugly code to deal with non-namespaced MVC code
        if (empty($prefix) && $this->getMVCFactory() instanceof LegacyFactory) {
            $prefix = 'Table';
        }

        if ($table = $this->_createTable($name, $prefix, $options)) {
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
        $table           = $this->getTable();
        $checkedOutField = $table->getColumnAlias('checked_out');

        if (property_exists($item, $checkedOutField) && $item->{$checkedOutField} != $this->getCurrentUser()->id) {
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
            'defaultgroup' => $group ?: ($this->option ?? $app->getInput()->get('option')),
            'cachebase'    => $app->get('cache_path', JPATH_CACHE),
            'result'       => true,
        ];

        try {
            /** @var CallbackController $cache */
            $cache = $this->getCacheControllerFactory()->createCacheController('callback', $options);
            $cache->clean();
        } catch (CacheExceptionInterface) {
            $options['result'] = false;
        }

        // Trigger the onContentCleanCache event.
        $this->getDispatcher()->dispatch($this->event_clean_cache, new AfterCleanCacheEvent($this->event_clean_cache, $options));
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

    /**
     * Get the event dispatcher.
     *
     * The override was made to keep a backward compatibility for legacy component.
     * TODO: Remove the override in 6.0
     *
     * @return  DispatcherInterface
     *
     * @since   4.4.0
     * @throws  \UnexpectedValueException May be thrown if the dispatcher has not been set.
     */
    public function getDispatcher()
    {
        if (!$this->dispatcher) {
            @trigger_error(
                \sprintf('Dispatcher for %s should be set through MVC factory. It will throw an exception in 6.0', __CLASS__),
                E_USER_DEPRECATED
            );

            return Factory::getContainer()->get(DispatcherInterface::class);
        }

        return $this->dispatcher;
    }

    /**
     * Dispatches the given event on the internal dispatcher, does a fallback to the global one.
     *
     * @param   EventInterface  $event  The event
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @deprecated 4.4 will be removed in 6.0. Use $this->getDispatcher() directly.
     */
    protected function dispatchEvent(EventInterface $event)
    {
        $this->getDispatcher()->dispatch($event->getName(), $event);

        @trigger_error(
            \sprintf(
                'Method %s is deprecated and will be removed in 6.0. Use getDispatcher()->dispatch() directly.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
    }

    /**
     * Get the database driver.
     *
     * @return  DatabaseInterface  The database driver.
     *
     * @since   4.2.0
     * @throws  \UnexpectedValueException
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use getDatabase() instead
     *              Example: $model->getDatabase();
     */
    public function getDbo()
    {
        try {
            return $this->getDatabase();
        } catch (DatabaseNotFoundException) {
            throw new \UnexpectedValueException('Database driver not set in ' . __CLASS__);
        }
    }

    /**
     * Set the database driver.
     *
     * @param   ?DatabaseInterface  $db  The database driver.
     *
     * @return  void
     *
     * @since   4.2.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use setDatabase() instead
     *              Example: $model->setDatabase($db);
     */
    public function setDbo(?DatabaseInterface $db = null)
    {
        if ($db === null) {
            return;
        }

        $this->setDatabase($db);
    }

    /**
     * Proxy for _db variable.
     *
     * @param   string  $name  The name of the element
     *
     * @return  mixed  The value of the element if set, null otherwise
     *
     * @since   4.2.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use getDatabase() instead of directly accessing _db
     */
    public function __get($name)
    {
        if ($name === '_db') {
            return $this->getDatabase();
        }

        // Default the variable
        if (!isset($this->$name)) {
            $this->$name = null;
        }

        return $this->$name;
    }
}
