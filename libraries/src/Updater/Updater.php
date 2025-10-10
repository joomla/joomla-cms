<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\LegacyPropertyManagementTrait;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Update as UpdateTable;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\DI\ContainerAwareInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Updater Class
 *
 * @since  1.7.0
 */
class Updater implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;
    use LegacyPropertyManagementTrait;

    /**
     * Development snapshots, nightly builds, pre-release versions and so on
     *
     * @var    integer
     * @since  3.4
     */
    public const STABILITY_DEV = 0;

    /**
     * Alpha versions (work in progress, things are likely to be broken)
     *
     * @var    integer
     * @since  3.4
     */
    public const STABILITY_ALPHA = 1;

    /**
     * Beta versions (major functionality in place, show-stopper bugs are likely to be present)
     *
     * @var    integer
     * @since  3.4
     */
    public const STABILITY_BETA = 2;

    /**
     * Release Candidate versions (almost stable, minor bugs might be present)
     *
     * @var    integer
     * @since  3.4
     */
    public const STABILITY_RC = 3;

    /**
     * Stable versions (production quality code)
     *
     * @var    integer
     * @since  3.4
     */
    public const STABILITY_STABLE = 4;

    /**
     * Updater instance container.
     *
     * @var    Updater
     * @since  1.7.3
     */
    protected static $instance;

    /**
     * Array of installer adapters
     *
     * @var    string[]|UpdateAdapter[]
     * @since  6.0.0
     */
    private $adapters = [];

    /**
     * Adapter Class Prefix
     *
     * @var    string
     * @since  6.0.0
     */
    private $classprefix = '\\Joomla\\CMS\\Updater\\Adapter';

    /**
     * Base Path for the installer adapters
     *
     * @var    string
     * @since  6.0.0
     */
    private $adapterfolder;

    /**
     * Constructor
     *
     * @param   string  $basepath       Base Path of the adapters
     * @param   string  $classprefix    Class prefix of adapters
     * @param   string  $adapterfolder  Name of folder to append to base path
     *
     * @since   3.1
     */
    public function __construct($basepath = __DIR__, $classprefix = '\\Joomla\\CMS\\Updater\\Adapter', $adapterfolder = 'Adapter')
    {
        $this->adapterfolder = $basepath . '/' . $adapterfolder;
        $this->classprefix   = $classprefix;
        $this->loadAdapters();
    }

    /**
     * Returns a reference to the global Installer object, only creating it
     * if it doesn't already exist.
     *
     * @return  Updater  An installer object
     *
     * @since   1.7.0
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
            self::$instance->setDatabase(Factory::getDbo());
        }

        return self::$instance;
    }

    /**
     * Finds the update for an extension. Any discovered updates are stored in the #__updates table.
     *
     * @param   int|array  $eid               Extension Identifier or list of Extension Identifiers; if zero use all
     *                                        sites
     * @param   integer    $cacheTimeout      How many seconds to cache update information; if zero, force reload the
     *                                        update information
     * @param   integer    $minimumStability  Minimum stability for the updates; 0=dev, 1=alpha, 2=beta, 3=rc,
     *                                        4=stable
     * @param   boolean    $includeCurrent    Should I include the current version in the results?
     *
     * @return  boolean True if there are updates
     *
     * @since   1.7.0
     */
    public function findUpdates($eid = 0, $cacheTimeout = 0, $minimumStability = self::STABILITY_STABLE, $includeCurrent = false)
    {
        $retval = false;

        $results = $this->getUpdateSites($eid);

        if (empty($results)) {
            return $retval;
        }

        $now              = time();
        $earliestTime     = $now - $cacheTimeout;
        $sitesWithUpdates = [];

        if ($cacheTimeout > 0) {
            $sitesWithUpdates = $this->getSitesWithUpdates($earliestTime);
        }

        foreach ($results as $result) {
            /**
             * If we have already checked for updates within the cache timeout period we will report updates available
             * only if there are update records matching this update site. Then we skip processing of the update site
             * since it's already processed within the cache timeout period.
             */
            if (
                ($cacheTimeout > 0)
                && isset($result['last_check_timestamp'])
                && ($result['last_check_timestamp'] >= $earliestTime)
            ) {
                $retval = $retval || \in_array($result['update_site_id'], $sitesWithUpdates);

                continue;
            }

            // Make sure there is no update left over in the database.
            $db    = $this->getDatabase();
            $query = $db->createQuery()
                ->delete($db->quoteName('#__updates'))
                ->where($db->quoteName('update_site_id') . ' = :id')
                ->bind(':id', $result['update_site_id'], ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();

            $updateObjects = $this->getUpdateObjectsForSite($result, $minimumStability, $includeCurrent);

            if (!empty($updateObjects)) {
                $retval = true;

                /** @var \Joomla\CMS\Table\Update $update */
                foreach ($updateObjects as $update) {
                    $update->check();
                    $update->store();
                }
            }

            // Finally, update the last update check timestamp
            $this->updateLastCheckTimestamp($result['update_site_id']);
        }

        return $retval;
    }

    /**
     * Returns available updates
     *
     * @param integer $eid
     * @param string $minimumStability
     * @param boolean $includeCurrent
     *
     * @return array
     */
    public function getAvailableUpdates(int $eid, string $minimumStability = self::STABILITY_STABLE): array
    {
        $results = $this->getUpdateSites($eid);

        if (empty($results)) {
            return [];
        }

        $updateObjects = [];

        foreach ($results as $result) {
            $updateElements = $this->getUpdateObjectsForSite($result, $minimumStability);

            foreach ($updateElements as $updateElement) {
                $updateObjects[] = get_object_vars($updateElement);
            }
        }

        return $updateObjects;
    }

    /**
     * Returns the update site records for an extension with ID $eid. If $eid is zero all enabled update sites records
     * will be returned.
     *
     * @param   int  $eid  The extension ID to fetch.
     *
     * @return  array
     *
     * @since   3.6.0
     */
    private function getUpdateSites($eid = 0)
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();

        $query->select(
            [
                'DISTINCT ' . $db->quoteName('a.update_site_id'),
                $db->quoteName('a.type'),
                $db->quoteName('a.location'),
                $db->quoteName('a.last_check_timestamp'),
                $db->quoteName('a.extra_query'),
            ]
        )
            ->from($db->quoteName('#__update_sites', 'a'))
            ->where($db->quoteName('a.enabled') . ' = 1');

        if ($eid) {
            $query->join(
                'INNER',
                $db->quoteName('#__update_sites_extensions', 'b'),
                $db->quoteName('a.update_site_id') . ' = ' . $db->quoteName('b.update_site_id')
            );

            if (\is_array($eid)) {
                $query->whereIn($db->quoteName('b.extension_id'), $eid);
            } elseif ($eid = (int) $eid) {
                $query->where($db->quoteName('b.extension_id') . ' = :eid')
                    ->bind(':eid', $eid, ParameterType::INTEGER);
            }
        }

        $db->setQuery($query);

        $result = $db->loadAssocList();

        if (!\is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * Loads the contents of an update site record $updateSite and returns the update objects
     *
     * @param   array  $updateSite        The update site record to process
     * @param   int    $minimumStability  Minimum stability for the returned update records
     * @param   bool   $includeCurrent    Should I also include the current version?
     *
     * @return  array  The update records. Empty array if no updates are found.
     *
     * @since   3.6.0
     */
    private function getUpdateObjectsForSite($updateSite, $minimumStability = self::STABILITY_STABLE, $includeCurrent = false)
    {
        $retVal = [];

        try {
            // Get the update information from the remote update XML document
            /** @var UpdateAdapter $adapter */
            $adapter = $this->getAdapter($updateSite['type']);
        } catch (\InvalidArgumentException $e) {
            // Ignore update sites requiring adapters we don't have installed
            return $retVal;
        }

        $updateSite['minimum_stability'] = $minimumStability;
        $update_result                   = $adapter->findUpdate($updateSite);

        // Version comparison operator.
        $operator = $includeCurrent ? 'ge' : 'gt';

        if (\is_array($update_result)) {
            // If we have additional update sites in the remote (collection) update XML document, parse them
            if (\array_key_exists('update_sites', $update_result) && \count($update_result['update_sites'])) {
                $thisUrl = trim($updateSite['location']);
                $thisId  = (int) $updateSite['update_site_id'];

                foreach ($update_result['update_sites'] as $extraUpdateSite) {
                    $extraUrl = trim($extraUpdateSite['location']);
                    $extraId  = (int) $extraUpdateSite['update_site_id'];

                    // Do not try to fetch the same update site twice
                    if (($thisId == $extraId) || ($thisUrl == $extraUrl)) {
                        continue;
                    }

                    $extraUpdates = $this->getUpdateObjectsForSite($extraUpdateSite, $minimumStability);

                    if (\count($extraUpdates)) {
                        $retVal = array_merge($retVal, $extraUpdates);
                    }
                }
            }

            if (\array_key_exists('updates', $update_result) && \count($update_result['updates'])) {
                /** @var \Joomla\CMS\Table\Update $current_update */
                foreach ($update_result['updates'] as $current_update) {
                    $current_update->extra_query = $updateSite['extra_query'];

                    $update    = new UpdateTable($this->getDatabase());
                    $extension = new Extension($this->getDatabase());

                    $uid = $update
                        ->find(
                            [
                                'element'   => $current_update->element,
                                'type'      => $current_update->type,
                                'client_id' => $current_update->client_id,
                                'folder'    => $current_update->folder ?? '',
                            ]
                        );

                    $eid = $extension
                        ->find(
                            [
                                'element'   => $current_update->element,
                                'type'      => $current_update->type,
                                'client_id' => $current_update->client_id,
                                'folder'    => $current_update->folder ?? '',
                            ]
                        );

                    if (!$uid) {
                        // Set the extension id
                        if ($eid) {
                            // We have an installed extension, check the update is actually newer
                            $extension->load($eid);
                            $data = json_decode($extension->manifest_cache, true);

                            if (version_compare($current_update->version, $data['version'], $operator) == 1) {
                                $current_update->extension_id = $eid;
                                $retVal[]                     = $current_update;
                            }
                        } else {
                            // A potentially new extension to be installed
                            $retVal[] = $current_update;
                        }
                    } else {
                        $update->load($uid);

                        // We already have an update in the database lets check whether it has an extension_id
                        if ((int) $update->extension_id === 0 && $eid) {
                            // The current update does not have an extension_id but we found one. Let's use it.
                            $current_update->extension_id = $eid;
                        }

                        // If there is an update, check that the version is newer then replaces
                        if (version_compare($current_update->version, $update->version, $operator) == 1) {
                            $retVal[] = $current_update;
                        }
                    }
                }
            }
        }

        return $retVal;
    }

    /**
     * Returns the IDs of the update sites with cached updates
     *
     * @param   int  $timestamp  Optional. If set, only update sites checked before $timestamp will be taken into
     *                           account.
     *
     * @return  array  The IDs of the update sites with cached updates
     *
     * @since   3.6.0
     */
    private function getSitesWithUpdates($timestamp = 0)
    {
        $db        = $this->getDatabase();
        $timestamp = (int) $timestamp;

        $query = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('update_site_id'))
            ->from($db->quoteName('#__updates'));

        if ($timestamp) {
            $subQuery = $db->createQuery()
                ->select($db->quoteName('update_site_id'))
                ->from($db->quoteName('#__update_sites'))
                ->where(
                    [
                        $db->quoteName('last_check_timestamp') . ' IS NULL',
                        $db->quoteName('last_check_timestamp') . ' <= :timestamp',
                    ],
                    'OR'
                );

            $query->where($db->quoteName('update_site_id') . ' IN (' . $subQuery . ')')
                ->bind(':timestamp', $timestamp, ParameterType::INTEGER);
        }

        $retVal = $db->setQuery($query)->loadColumn(0);

        if (empty($retVal)) {
            return [];
        }

        return $retVal;
    }

    /**
     * Update the last check timestamp of an update site
     *
     * @param   int  $updateSiteId  The update site ID to mark as just checked
     *
     * @return  void
     *
     * @since   3.6.0
     */
    private function updateLastCheckTimestamp($updateSiteId)
    {
        $timestamp    = time();
        $db           = $this->getDatabase();
        $updateSiteId = (int) $updateSiteId;

        $query = $db->createQuery()
            ->update($db->quoteName('#__update_sites'))
            ->set($db->quoteName('last_check_timestamp') . ' = :timestamp')
            ->where($db->quoteName('update_site_id') . ' = :id')
            ->bind(':timestamp', $timestamp, ParameterType::INTEGER)
            ->bind(':id', $updateSiteId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Discover all adapters in the adapterfolder path
     *
     * @return  void
     *
     * @since  6.0.0
     */
    protected function loadAdapters()
    {
        $files    = new \DirectoryIterator($this->adapterfolder);

        // Process the core adapters
        foreach ($files as $file) {
            $fileName = $file->getFilename();

            // Only load php files.
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            // Derive the class name from the filename.
            $name  = str_ireplace('.php', '', trim($fileName));
            $name  = str_ireplace('adapter', '', trim($name));
            $class = rtrim($this->classprefix, '\\') . '\\' . ucfirst($name) . 'Adapter';

            if (!class_exists($class)) {
                // Not namespaced
                $class = $this->classprefix . ucfirst($name);
            }

            // Core adapters should autoload based on classname, keep this fallback just in case
            if (!class_exists($class)) {
                // Try to load the adapter object
                \JLoader::register($class, $this->adapterfolder . '/' . $fileName);

                if (!class_exists($class)) {
                    // Skip to next one
                    continue;
                }
            }

            $this->adapters[strtolower($name)] = $class;
        }
    }

    /**
     * Gets a list of available update adapters.
     *
     * @param   array  $options  An array of options to inject into the adapter
     * @param   array  $custom   Array of custom update adapters
     *
     * @return  string[]  An array of the class names of available install adapters.
     *
     * @since   3.4
     */
    public function getAdapters($options = [], array $custom = [])
    {
        if (\count($custom)) {
            foreach ($custom as $adapter) {
                // Setup the class name
                // @todo - Can we abstract this to not depend on the Joomla class namespace without PHP namespaces?
                $class = $this->classprefix . ucfirst(trim($adapter));

                // If the class doesn't exist we have nothing left to do but look at the next type. We did our best.
                if (!class_exists($class)) {
                    continue;
                }

                $this->adapters[$adapter] = $class;
            }
        }

        return array_keys($this->adapters);
    }

    /**
     * Get an update adapter instance
     *
     * @param   string  $name     Adapter name
     * @param   array   $options  Adapter options
     *
     * @return  UpdateAdapter
     *
     * @throws  \InvalidArgumentException
     * @since   6.0.0
     */
    public function getAdapter($name, $options = [])
    {
        $name = strtolower($name);

        if (!isset($this->adapters[$name])) {
            throw new \InvalidArgumentException(\sprintf('The %s update adapter does not exist.', $name));
        }

        if (\is_string($this->adapters[$name])) {
            $class = $this->adapters[$name];

            // Ensure the adapter type is part of the options array
            $options['type'] = $name;

            // Check for a possible service from the container otherwise manually instantiate the class
            if (Factory::getContainer()->has($class)) {
                return Factory::getContainer()->get($class);
            }

            $adapter = new $class($this, $this->getDatabase(), $options);

            if ($adapter instanceof ContainerAwareInterface) {
                $adapter->setContainer(Factory::getContainer());
            }

            $this->adapters[$name] = $adapter;
        }

        return $this->adapters[$name];
    }

    /**
     * Set an adapter by name
     *
     * @param   string                $name     Adapter name
     * @param   UpdateAdapter|string  $adapter  Adapter object or class name
     *
     * @return  boolean  True if successful
     *
     * @since   6.0.0
     */
    public function setAdapter($name, $adapter)
    {
        if (\is_object($adapter)) {
            $this->adapters[$name] = $adapter;

            return true;
        }

        if (class_exists($adapter)) {
            $this->adapters[$name] = $adapter;

            return true;
        }

        return false;
    }
}
