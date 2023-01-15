<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Controller;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Cache callback type object
 *
 * @since  1.7.0
 */
class CallbackController extends CacheController
{
    /**
     * Executes a cacheable callback if not found in cache else returns cached output and result
     *
     * @param   callable  $callback    Callback or string shorthand for a callback
     * @param   array     $args        Callback arguments
     * @param   mixed     $id          Cache ID
     * @param   boolean   $wrkarounds  True to use workarounds
     * @param   array     $woptions    Workaround options
     *
     * @return  mixed  Result of the callback
     *
     * @since   1.7.0
     */
    public function get($callback, $args = [], $id = false, $wrkarounds = false, $woptions = [])
    {
        if (!\is_array($args)) {
            $referenceArgs = !empty($args) ? [&$args] : [];
        } else {
            $referenceArgs = &$args;
        }

        // Just execute the callback if caching is disabled.
        if (empty($this->options['caching'])) {
            return \call_user_func_array($callback, $referenceArgs);
        }

        if (!$id) {
            // Generate an ID
            $id = $this->_makeId($callback, $args);
        }

        $data = $this->cache->get($id);

        $locktest = (object) ['locked' => null, 'locklooped' => null];

        if ($data === false) {
            $locktest = $this->cache->lock($id);

            // If locklooped is true try to get the cached data again; it could exist now.
            if ($locktest->locked === true && $locktest->locklooped === true) {
                $data = $this->cache->get($id);
            }
        }

        if ($data !== false) {
            if ($locktest->locked === true) {
                $this->cache->unlock($id);
            }

            $data = unserialize(trim($data));

            if ($wrkarounds) {
                echo Cache::getWorkarounds(
                    $data['output'],
                    ['mergehead' => $woptions['mergehead'] ?? 0]
                );
            } else {
                echo $data['output'];
            }

            return $data['result'];
        }

        if ($locktest->locked === false && $locktest->locklooped === true) {
            // We can not store data because another process is in the middle of saving
            return \call_user_func_array($callback, $referenceArgs);
        }

        $coptions = ['modulemode' => 0];

        if (isset($woptions['modulemode']) && $woptions['modulemode'] == 1) {
            /** @var HtmlDocument $document */
            $document = Factory::getDocument();

            if (method_exists($document, 'getHeadData')) {
                $coptions['headerbefore'] = $document->getHeadData();

                // Reset document head before rendering module. Module will cache only assets added by itself.
                $document->resetHeadData();
                $document->getWebAssetManager()->reset();

                $coptions['modulemode'] = 1;
            }
        }

        $coptions['nopathway'] = $woptions['nopathway'] ?? 1;
        $coptions['nohead']    = $woptions['nohead'] ?? 1;
        $coptions['nomodules'] = $woptions['nomodules'] ?? 1;

        ob_start();
        ob_implicit_flush(false);

        $result = \call_user_func_array($callback, $referenceArgs);
        $output = ob_get_clean();

        $data = ['result' => $result];

        if ($wrkarounds) {
            $data['output'] = Cache::setWorkarounds($output, $coptions);
        } else {
            $data['output'] = $output;
        }

        // Restore document head data and merge module head data.
        if ($coptions['modulemode'] == 1) {
            $moduleHeadData = $document->getHeadData();
            $document->resetHeadData();
            $document->mergeHeadData($coptions['headerbefore']);
            $document->mergeHeadData($moduleHeadData);
        }

        // Store the cache data
        $this->cache->store(serialize($data), $id);

        if ($locktest->locked === true) {
            $this->cache->unlock($id);
        }

        echo $output;

        return $result;
    }

    /**
     * Store data to cache by ID and group
     *
     * @param   mixed    $data        The data to store
     * @param   string   $id          The cache data ID
     * @param   string   $group       The cache data group
     * @param   boolean  $wrkarounds  True to use wrkarounds
     *
     * @return  boolean  True if cache stored
     *
     * @since   4.0.0
     */
    public function store($data, $id, $group = null, $wrkarounds = true)
    {
        $locktest = $this->cache->lock($id, $group);

        if ($locktest->locked === false && $locktest->locklooped === true) {
            // We can not store data because another process is in the middle of saving
            return false;
        }

        $result = $this->cache->store(serialize($data), $id, $group);

        if ($locktest->locked === true) {
            $this->cache->unlock($id, $group);
        }

        return $result;
    }

    /**
     * Generate a callback cache ID
     *
     * @param   mixed  $callback  Callback to cache
     * @param   array  $args      Arguments to the callback method to cache
     *
     * @return  string  MD5 Hash
     *
     * @since   1.7.0
     */
    protected function _makeId($callback, $args)
    {
        if (\is_array($callback) && \is_object($callback[0])) {
            $vars        = get_object_vars($callback[0]);
            $vars[]      = strtolower(\get_class($callback[0]));
            $callback[0] = $vars;
        }

        // A Closure can't be serialized, so to generate the ID we'll need to get its hash
        if ($callback instanceof \closure) {
            $hash = spl_object_hash($callback);

            return md5($hash . serialize([$args]));
        }

        return md5(serialize([$callback, $args]));
    }
}
