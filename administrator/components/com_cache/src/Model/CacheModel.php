<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cache\Administrator\Model;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Cache\Exception\CacheConnectingException;
use Joomla\CMS\Cache\Exception\UnsupportedCacheException;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Cache Model
 *
 * @since  1.6
 */
class CacheModel extends ListModel
{
    /**
     * An Array of CacheItems indexed by cache group ID
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Group total
     *
     * @var integer
     */
    protected $_total = null;

    /**
     * Pagination object
     *
     * @var object
     */
    protected $_pagination = null;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   3.5
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'group',
                'count',
                'size',
                'client_id',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Field for ordering.
     * @param   string  $direction  Direction of ordering.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'group', $direction = 'asc')
    {
        // Load the filter state.
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   3.5
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Method to get cache data
     *
     * @return array
     */
    public function getData()
    {
        if (empty($this->_data)) {
            try {
                $cache = $this->getCache();
                $data  = $cache->getAll();

                if ($data && \count($data) > 0) {
                    // Process filter by search term.
                    if ($search = $this->getState('filter.search')) {
                        foreach ($data as $key => $cacheItem) {
                            if (stripos($cacheItem->group, $search) === false) {
                                unset($data[$key]);
                            }
                        }
                    }

                    // Process ordering.
                    $listOrder = $this->getState('list.ordering', 'group');
                    $listDirn  = $this->getState('list.direction', 'ASC');

                    $this->_data = ArrayHelper::sortObjects($data, $listOrder, strtolower($listDirn) === 'desc' ? -1 : 1, true, true);

                    // Process pagination.
                    $limit = (int) $this->getState('list.limit', 25);

                    if ($limit !== 0) {
                        $start = (int) $this->getState('list.start', 0);

                        return \array_slice($this->_data, $start, $limit);
                    }
                } else {
                    $this->_data = [];
                }
            } catch (CacheConnectingException $exception) {
                $this->setError(Text::_('COM_CACHE_ERROR_CACHE_CONNECTION_FAILED'));
                $this->_data = [];
            } catch (UnsupportedCacheException $exception) {
                $this->setError(Text::_('COM_CACHE_ERROR_CACHE_DRIVER_UNSUPPORTED'));
                $this->_data = [];
            }
        }

        return $this->_data;
    }

    /**
     * Method to get cache instance.
     *
     * @return CacheController
     */
    public function getCache()
    {
        $app = Factory::getApplication();

        $options = [
            'defaultgroup' => '',
            'storage'      => $app->get('cache_handler', ''),
            'caching'      => true,
            'cachebase'    => $app->get('cache_path', JPATH_CACHE)
        ];

        return Cache::getInstance('', $options);
    }

    /**
     * Get the number of current Cache Groups.
     *
     * @return  integer
     */
    public function getTotal()
    {
        if (empty($this->_total)) {
            $this->_total = count($this->getData());
        }

        return $this->_total;
    }

    /**
     * Method to get a pagination object for the cache.
     *
     * @return  Pagination
     */
    public function getPagination()
    {
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('list.start'), $this->getState('list.limit'));
        }

        return $this->_pagination;
    }

    /**
     * Clean out a cache group as named by param.
     * If no param is passed clean all cache groups.
     *
     * @param   string  $group  Cache group name.
     *
     * @return  boolean  True on success, false otherwise
     */
    public function clean($group = '')
    {
        try {
            $this->getCache()->clean($group);
        } catch (CacheConnectingException $exception) {
            return false;
        } catch (UnsupportedCacheException $exception) {
            return false;
        }

        Factory::getApplication()->triggerEvent('onAfterPurge', [$group]);

        return true;
    }

    /**
     * Purge an array of cache groups.
     *
     * @param   array  $array  Array of cache group names.
     *
     * @return  array  Array with errors, if they exist.
     */
    public function cleanlist($array)
    {
        $errors = [];

        foreach ($array as $group) {
            if (!$this->clean($group)) {
                $errors[] = $group;
            }
        }

        return $errors;
    }

    /**
     * Purge all cache items.
     *
     * @return  boolean  True if successful; false otherwise.
     */
    public function purge()
    {
        try {
            Factory::getCache('')->gc();
        } catch (CacheConnectingException $exception) {
            return false;
        } catch (UnsupportedCacheException $exception) {
            return false;
        }

        Factory::getApplication()->triggerEvent('onAfterPurge', []);

        return true;
    }
}
