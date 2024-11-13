<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Adapter\Adapter;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Event\Extension\AfterInstallEvent;
use Joomla\CMS\Event\Extension\AfterUninstallEvent;
use Joomla\CMS\Event\Extension\AfterUpdateEvent;
use Joomla\CMS\Event\Extension\BeforeInstallEvent;
use Joomla\CMS\Event\Extension\BeforeUninstallEvent;
use Joomla\CMS\Event\Extension\BeforeUpdateEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\DI\ContainerAwareInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla base installer class
 *
 * @since  3.1
 */
class Installer extends Adapter implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Array of paths needed by the installer
     *
     * @var    array
     * @since  3.1
     */
    protected $paths = [];

    /**
     * True if package is an upgrade
     *
     * @var    boolean
     * @since  3.1
     */
    protected $upgrade = null;

    /**
     * The manifest trigger class
     *
     * @var    object
     * @since  3.1
     */
    public $manifestClass = null;

    /**
     * True if existing files can be overwritten
     *
     * @var    boolean
     * @since  3.0.0
     */
    protected $overwrite = false;

    /**
     * Stack of installation steps
     * - Used for installation rollback
     *
     * @var    array
     * @since  3.1
     */
    protected $stepStack = [];

    /**
     * Extension Table Entry
     *
     * @var    Extension
     * @since  3.1
     */
    public $extension = null;

    /**
     * The output from the install/uninstall scripts
     *
     * @var    string
     * @since  3.1
     * */
    public $message = null;

    /**
     * The installation manifest XML object
     *
     * @var    object
     * @since  3.1
     */
    public $manifest = null;

    /**
     * The extension message that appears
     *
     * @var    string
     * @since  3.1
     */
    protected $extension_message = null;

    /**
     * The redirect URL if this extension (can be null if no redirect)
     *
     * @var    string
     * @since  3.1
     */
    protected $redirect_url = null;

    /**
     * Flag if the uninstall process was triggered by uninstalling a package
     *
     * @var    boolean
     * @since  3.7.0
     */
    protected $packageUninstall = false;

    /**
     * Backup extra_query during update_sites rebuild
     *
     * @var    string
     * @since  3.9.26
     */
    public $extraQuery = '';

    /**
     * JInstaller instances container.
     *
     * @var    Installer[]
     * @since  3.4
     */
    protected static $instances;

    /**
     * A comment marker to indicate that an update SQL query may fail without triggering an update error.
     *
     * @since  4.2.0
     */
    protected const CAN_FAIL_MARKER = '/** CAN FAIL **/';

    /**
     * The length of the CAN_FAIL_MARKER string
     *
     * @since  4.2.0
     */
    protected const CAN_FAIL_MARKER_LENGTH = 16;

    /**
     * Constructor
     *
     * @param   string  $basepath       Base Path of the adapters
     * @param   string  $classprefix    Class prefix of adapters
     * @param   string  $adapterfolder  Name of folder to append to base path
     *
     * @since   3.1
     */
    public function __construct($basepath = __DIR__, $classprefix = '\\Joomla\\CMS\\Installer\\Adapter', $adapterfolder = 'Adapter')
    {
        parent::__construct($basepath, $classprefix, $adapterfolder);

        $this->extension = Table::getInstance('extension');
    }

    /**
     * Returns the global Installer object, only creating it if it doesn't already exist.
     *
     * @param   string  $basepath       Base Path of the adapters
     * @param   string  $classprefix    Class prefix of adapters
     * @param   string  $adapterfolder  Name of folder to append to base path
     *
     * @return  Installer  An installer object
     *
     * @since   3.1
     */
    public static function getInstance($basepath = __DIR__, $classprefix = '\\Joomla\\CMS\\Installer\\Adapter', $adapterfolder = 'Adapter')
    {
        if (!isset(self::$instances[$basepath])) {
            self::$instances[$basepath] = new static($basepath, $classprefix, $adapterfolder);
            self::$instances[$basepath]->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
        }

        return self::$instances[$basepath];
    }

    /**
     * Splits a string of multiple queries into an array of individual queries.
     *
     * This is different than DatabaseDriver::splitSql. It supports the special CAN FAIL comment
     * marker which indicates that a SQL statement could fail without raising an error during the
     * installation.
     *
     * @param   string|null  $sql  Input SQL string with which to split into individual queries.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function splitSql(?string $sql): array
    {
        if (empty($sql)) {
            return [];
        }

        $start     = 0;
        $open      = false;
        $comment   = false;
        $endString = '';
        $end       = \strlen($sql);
        $queries   = [];
        $query     = '';

        for ($i = 0; $i < $end; $i++) {
            $current      = substr($sql, $i, 1);
            $current2     = substr($sql, $i, 2);
            $current3     = substr($sql, $i, 3);
            $lenEndString = \strlen($endString);
            $testEnd      = substr($sql, $i, $lenEndString);

            if (
                $current === '"' || $current === "'" || $current2 === '--'
                || ($current2 === '/*' && $current3 !== '/*!' && $current3 !== '/*+')
                || ($current === '#' && $current3 !== '#__')
                || ($comment && $testEnd === $endString)
            ) {
                // Check if quoted with previous backslash
                $n = 2;

                while (substr($sql, $i - $n + 1, 1) === '\\' && $n < $i) {
                    $n++;
                }

                // Not quoted
                if ($n % 2 === 0) {
                    if ($open) {
                        if ($testEnd === $endString) {
                            if ($comment) {
                                $comment = false;

                                if ($lenEndString > 1) {
                                    $i += ($lenEndString - 1);
                                    $current = substr($sql, $i, 1);
                                }

                                $start = $i + 1;
                            }

                            $open      = false;
                            $endString = '';
                        }
                    } else {
                        $open = true;

                        if ($current2 === '--') {
                            $endString = "\n";
                            $comment   = true;
                        } elseif ($current2 === '/*') {
                            $endString = '*/';
                            $comment   = true;
                        } elseif ($current === '#') {
                            $endString = "\n";
                            $comment   = true;
                        } else {
                            $endString = $current;
                        }

                        if ($comment && $start < $i) {
                            $query .= substr($sql, $start, $i - $start);
                        }
                    }
                }
            }

            if ($comment) {
                $start = $i + 1;
            }

            if (($current === ';' && !$open) || $i === $end - 1) {
                if ($current === ';' && !$open && $start <= $i && $start > self::CAN_FAIL_MARKER_LENGTH) {
                    $possibleMarker = substr($sql, $start - self::CAN_FAIL_MARKER_LENGTH, $i - $start + self::CAN_FAIL_MARKER_LENGTH);

                    if (strtoupper($possibleMarker) === self::CAN_FAIL_MARKER) {
                        $start -= self::CAN_FAIL_MARKER_LENGTH;
                    }
                }

                if ($start <= $i) {
                    $query .= substr($sql, $start, $i - $start + 1);
                }

                $query = trim($query);

                if ($query) {
                    if (($i === $end - 1) && ($current !== ';')) {
                        $query .= ';';
                    }

                    $queries[] = $query;
                }

                $query = '';
                $start = $i + 1;
            }

            $endComment = false;
        }

        return $queries;
    }

    /**
     * Get the allow overwrite switch
     *
     * @return  boolean  Allow overwrite switch
     *
     * @since   3.1
     */
    public function isOverwrite()
    {
        return $this->overwrite;
    }

    /**
     * Set the allow overwrite switch
     *
     * @param   boolean  $state  Overwrite switch state
     *
     * @return  boolean  True it state is set, false if it is not
     *
     * @since   3.1
     */
    public function setOverwrite($state = false)
    {
        $tmp = $this->overwrite;

        if ($state) {
            $this->overwrite = true;
        } else {
            $this->overwrite = false;
        }

        return $tmp;
    }

    /**
     * Get the redirect location
     *
     * @return  string  Redirect location (or null)
     *
     * @since   3.1
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * Set the redirect location
     *
     * @param   string  $newurl  New redirect location
     *
     * @return  void
     *
     * @since   3.1
     */
    public function setRedirectUrl($newurl)
    {
        $this->redirect_url = $newurl;
    }

    /**
     * Get whether this installer is uninstalling extensions which are part of a package
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    public function isPackageUninstall()
    {
        return $this->packageUninstall;
    }

    /**
     * Set whether this installer is uninstalling extensions which are part of a package
     *
     * @param   boolean  $uninstall  True if a package triggered the uninstall, false otherwise
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function setPackageUninstall($uninstall)
    {
        $this->packageUninstall = $uninstall;
    }

    /**
     * Get the upgrade switch
     *
     * @return  boolean
     *
     * @since   3.1
     */
    public function isUpgrade()
    {
        return $this->upgrade;
    }

    /**
     * Set the upgrade switch
     *
     * @param   boolean  $state  Upgrade switch state
     *
     * @return  boolean  True if upgrade, false otherwise
     *
     * @since   3.1
     */
    public function setUpgrade($state = false)
    {
        $tmp = $this->upgrade;

        if ($state) {
            $this->upgrade = true;
        } else {
            $this->upgrade = false;
        }

        return $tmp;
    }

    /**
     * Get the installation manifest object
     *
     * @return  \SimpleXMLElement  Manifest object
     *
     * @since   3.1
     */
    public function getManifest()
    {
        if (!\is_object($this->manifest)) {
            $this->findManifest();
        }

        return $this->manifest;
    }

    /**
     * Get an installer path by name
     *
     * @param   string  $name     Path name
     * @param   string  $default  Default value
     *
     * @return  string  Path
     *
     * @since   3.1
     */
    public function getPath($name, $default = null)
    {
        return (!empty($this->paths[$name])) ? $this->paths[$name] : $default;
    }

    /**
     * Sets an installer path by name
     *
     * @param   string  $name   Path name
     * @param   string  $value  Path
     *
     * @return  void
     *
     * @since   3.1
     */
    public function setPath($name, $value)
    {
        $this->paths[$name] = $value;
    }

    /**
     * Pushes a step onto the installer stack for rolling back steps
     *
     * @param   array  $step  Installer step
     *
     * @return  void
     *
     * @since   3.1
     */
    public function pushStep($step)
    {
        $this->stepStack[] = $step;
    }

    /**
     * Installation abort method
     *
     * @param   string  $msg   Abort message from the installer
     * @param   string  $type  Package type if defined
     *
     * @return  boolean  True if successful
     *
     * @since   3.1
     */
    public function abort($msg = null, $type = null)
    {
        $retval = true;
        $step   = array_pop($this->stepStack);

        // Raise abort warning
        if ($msg) {
            Log::add($msg, Log::WARNING, 'jerror');
        }

        while ($step != null) {
            switch ($step['type']) {
                case 'file':
                    // Remove the file
                    if (is_file($step['path']) && !($stepval = File::delete($step['path']))) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $step['path']), Log::WARNING, 'jerror');
                    }
                    break;

                case 'folder':
                    // Remove the folder
                    if (is_dir($step['path']) && !($stepval = Folder::delete($step['path']))) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $step['path']), Log::WARNING, 'jerror');
                    }
                    break;

                case 'query':
                    // Execute the query.
                    $stepval = $this->parseSQLFiles($step['script']);
                    break;

                case 'extension':
                    // Get database connector object
                    $db     = $this->getDatabase();
                    $query  = $db->getQuery(true);
                    $stepId = (int) $step['id'];

                    // Remove the entry from the #__extensions table
                    $query->delete($db->quoteName('#__extensions'))
                        ->where($db->quoteName('extension_id') . ' = :step_id')
                        ->bind(':step_id', $stepId, ParameterType::INTEGER);
                    $db->setQuery($query);

                    try {
                        $db->execute();

                        $stepval = true;
                    } catch (ExecutionFailureException $e) {
                        // The database API will have already logged the error it caught, we just need to alert the user to the issue
                        Log::add(Text::_('JLIB_INSTALLER_ABORT_ERROR_DELETING_EXTENSIONS_RECORD'), Log::WARNING, 'jerror');

                        $stepval = false;
                    }

                    break;

                default:
                    if ($type && \is_object($this->_adapters[$type])) {
                        // Build the name of the custom rollback method for the type
                        $method = '_rollback_' . $step['type'];

                        // Custom rollback method handler
                        if (method_exists($this->_adapters[$type], $method)) {
                            $stepval = $this->_adapters[$type]->$method($step);
                        }
                    } else {
                        // Set it to false
                        $stepval = false;
                    }
                    break;
            }

            // Only set the return value if it is false
            if ($stepval === false) {
                $retval = false;
            }

            // Get the next step and continue
            $step = array_pop($this->stepStack);
        }

        return $retval;
    }

    // Adapter functions

    /**
     * Package installation method
     *
     * @param   string  $path  Path to package source folder
     *
     * @return  boolean  True if successful
     *
     * @since   3.1
     */
    public function install($path = null)
    {
        if ($path && is_dir(Path::clean($path))) {
            $this->setPath('source', $path);
        } else {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_NOINSTALLPATH'));

            return false;
        }

        if (!$adapter = $this->setupInstall('install', true)) {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

            return false;
        }

        if (!\is_object($adapter)) {
            return false;
        }

        // Add the languages from the package itself
        if (method_exists($adapter, 'loadLanguage')) {
            $adapter->loadLanguage($path);
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        // Fire the onExtensionBeforeInstall event.
        PluginHelper::importPlugin('extension', null, true, $dispatcher);
        $dispatcher->dispatch('onExtensionBeforeInstall', new BeforeInstallEvent('onExtensionBeforeInstall', [
            'method'    => 'install',
            'type'      => $this->manifest->attributes()->type,
            'manifest'  => $this->manifest,
            'extension' => 0,
        ]));

        // Run the install
        $result = $adapter->install();

        // Make sure Joomla can figure out what has changed
        clearstatcache();

        // Fire the onExtensionAfterInstall
        $dispatcher->dispatch('onExtensionAfterInstall', new AfterInstallEvent('onExtensionAfterInstall', [
            'installer' => clone $this,
            'eid'       => $result,
        ]));

        if ($result !== false) {
            // Refresh versionable assets cache
            Factory::getApplication()->flushAssets();

            return true;
        }

        return false;
    }

    /**
     * Discovered package installation method
     *
     * @param   integer  $eid  Extension ID
     *
     * @return  boolean  True if successful
     *
     * @since   3.1
     */
    public function discover_install($eid = null)
    {
        if (!$eid) {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_EXTENSIONNOTVALID'));

            return false;
        }

        if (!$this->extension->load($eid)) {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_LOAD_DETAILS'));

            return false;
        }

        if ($this->extension->state != -1) {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_ALREADYINSTALLED'));

            return false;
        }

        // Load the adapter(s) for the install manifest
        $type   = $this->extension->type;
        $params = ['extension' => $this->extension, 'route' => 'discover_install'];

        $adapter = $this->loadAdapter($type, $params);

        if (!\is_object($adapter)) {
            return false;
        }

        if (!method_exists($adapter, 'discover_install') || !$adapter->getDiscoverInstallSupported()) {
            $this->abort(Text::sprintf('JLIB_INSTALLER_ERROR_DISCOVER_INSTALL_UNSUPPORTED', $type));

            return false;
        }

        // The adapter needs to prepare itself
        if (method_exists($adapter, 'prepareDiscoverInstall')) {
            try {
                $adapter->prepareDiscoverInstall();
            } catch (\RuntimeException $e) {
                $this->abort($e->getMessage());

                return false;
            }
        }

        // Add the languages from the package itself
        if (method_exists($adapter, 'loadLanguage')) {
            $adapter->loadLanguage();
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        // Fire the onExtensionBeforeInstall event.
        PluginHelper::importPlugin('extension', null, true, $dispatcher);
        $dispatcher->dispatch('onExtensionBeforeInstall', new BeforeInstallEvent('onExtensionBeforeInstall', [
            'method'    => 'discover_install',
            'type'      => $this->extension->type,
            'manifest'  => null,
            'extension' => (int) $this->extension->extension_id,
        ]));

        // Run the install
        $result = $adapter->discover_install();

        // Fire the onExtensionAfterInstall
        $dispatcher->dispatch('onExtensionAfterInstall', new AfterInstallEvent('onExtensionAfterInstall', [
            'installer' => clone $this,
            'eid'       => $result,
        ]));

        if ($result !== false) {
            // Refresh versionable assets cache
            Factory::getApplication()->flushAssets();

            return true;
        }

        return false;
    }

    /**
     * Extension discover method
     *
     * Asks each adapter to find extensions
     *
     * @return  InstallerExtension[]
     *
     * @since   3.1
     */
    public function discover()
    {
        $results = [];

        foreach ($this->getAdapters() as $adapter) {
            $instance = $this->loadAdapter($adapter);

            // Joomla! 1.5 installation adapter legacy support
            if (method_exists($instance, 'discover')) {
                $tmp = $instance->discover();

                // If its an array and has entries
                if (\is_array($tmp) && \count($tmp)) {
                    // Merge it into the system
                    $results = array_merge($results, $tmp);
                }
            }
        }

        return $results;
    }

    /**
     * Package update method
     *
     * @param   string  $path  Path to package source folder
     *
     * @return  boolean  True if successful
     *
     * @since   3.1
     */
    public function update($path = null)
    {
        if ($path && is_dir(Path::clean($path))) {
            $this->setPath('source', $path);
        } else {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_NOUPDATEPATH'));

            return false;
        }

        if (!$adapter = $this->setupInstall('update', true)) {
            $this->abort(Text::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

            return false;
        }

        if (!\is_object($adapter)) {
            return false;
        }

        // Add the languages from the package itself
        if (method_exists($adapter, 'loadLanguage')) {
            $adapter->loadLanguage($path);
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        // Fire the onExtensionBeforeUpdate event.
        PluginHelper::importPlugin('extension', null, true, $dispatcher);
        $dispatcher->dispatch('onExtensionBeforeUpdate', new BeforeUpdateEvent('onExtensionBeforeUpdate', [
            'type'     => $this->manifest->attributes()->type,
            'manifest' => $this->manifest,
        ]));

        // Run the update
        $result = $adapter->update();

        // Fire the onExtensionAfterUpdate
        $dispatcher->dispatch('onExtensionAfterUpdate', new AfterUpdateEvent('onExtensionAfterUpdate', [
            'installer' => clone $this,
            'eid'       => $result,
        ]));

        if ($result !== false) {
            return true;
        }

        return false;
    }

    /**
     * Package uninstallation method
     *
     * @param   string  $type        Package type
     * @param   mixed   $identifier  Package identifier for adapter
     *
     * @return  boolean  True if successful
     *
     * @since   3.1
     */
    public function uninstall($type, $identifier)
    {
        $params = ['extension' => $this->extension, 'route' => 'uninstall'];

        $adapter = $this->loadAdapter($type, $params);

        if (!\is_object($adapter)) {
            return false;
        }

        $dispatcher = Factory::getApplication()->getDispatcher();

        // We don't load languages here, we get the extension adapter to work it out
        // Fire the onExtensionBeforeUninstall event.
        PluginHelper::importPlugin('extension', null, true, $dispatcher);
        $dispatcher->dispatch('onExtensionBeforeUninstall', new BeforeUninstallEvent('onExtensionBeforeUninstall', [
            'eid' => (int) $identifier,
        ]));

        // Run the uninstall
        $result = $adapter->uninstall($identifier);

        // Fire the onExtensionAfterInstall
        $dispatcher->dispatch('onExtensionAfterUninstall', new AfterUninstallEvent('onExtensionAfterUninstall', [
            'installer' => clone $this,
            'eid'       => (int) $identifier,
            'removed'   => $result,
        ]));

        // Refresh versionable assets cache
        Factory::getApplication()->flushAssets();

        return $result;
    }

    /**
     * Refreshes the manifest cache stored in #__extensions
     *
     * @param   integer  $eid  Extension ID
     *
     * @return  boolean
     *
     * @since   3.1
     */
    public function refreshManifestCache($eid)
    {
        if ($eid) {
            if (!$this->extension->load($eid)) {
                $this->abort(Text::_('JLIB_INSTALLER_ABORT_LOAD_DETAILS'));

                return false;
            }

            if ($this->extension->state == -1) {
                $this->abort(Text::sprintf('JLIB_INSTALLER_ABORT_REFRESH_MANIFEST_CACHE', $this->extension->name));

                return false;
            }

            // Fetch the adapter
            $adapter = $this->loadAdapter($this->extension->type);

            if (!\is_object($adapter)) {
                return false;
            }

            if (!method_exists($adapter, 'refreshManifestCache')) {
                $this->abort(Text::sprintf('JLIB_INSTALLER_ABORT_METHODNOTSUPPORTED_TYPE', $this->extension->type));

                return false;
            }

            $result = $adapter->refreshManifestCache();

            if ($result !== false) {
                return true;
            }

            return false;
        }

        $this->abort(Text::_('JLIB_INSTALLER_ABORT_REFRESH_MANIFEST_CACHE_VALID'));

        return false;
    }

    // Utility functions

    /**
     * Prepare for installation: this method sets the installation directory, finds
     * and checks the installation file and verifies the installation type.
     *
     * @param   string   $route          The install route being followed
     * @param   boolean  $returnAdapter  Flag to return the instantiated adapter
     *
     * @return  boolean|InstallerAdapter  InstallerAdapter object if explicitly requested otherwise boolean
     *
     * @since   3.1
     */
    public function setupInstall($route = 'install', $returnAdapter = false)
    {
        // We need to find the installation manifest file
        if (!$this->findManifest()) {
            return false;
        }

        // Load the adapter(s) for the install manifest
        $type   = (string) $this->manifest->attributes()->type;
        $params = ['route' => $route, 'manifest' => $this->getManifest()];

        // Load the adapter
        $adapter = $this->loadAdapter($type, $params);

        if ($returnAdapter) {
            return $adapter;
        }

        return true;
    }

    /**
     * Backward compatible method to parse through a queries element of the
     * installation manifest file and take appropriate action.
     *
     * @param   \SimpleXMLElement  $element  The XML node to process
     *
     * @return  mixed  Number of queries processed or False on error
     *
     * @since   3.1
     */
    public function parseQueries(\SimpleXMLElement $element)
    {
        // Get the database connector object
        $db = & $this->_db;

        if (!$element || !\count($element->children())) {
            // Either the tag does not exist or has no children therefore we return zero files processed.
            return 0;
        }

        // Get the array of query nodes to process
        $queries = $element->children();

        if (\count($queries) === 0) {
            // No queries to process
            return 0;
        }

        $update_count = 0;

        // Process each query in the $queries array (children of $tagName).
        foreach ($queries as $query) {
            try {
                $db->setQuery($query)->execute();
            } catch (ExecutionFailureException $e) {
                Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()), Log::WARNING, 'jerror');

                return false;
            }

            $update_count++;
        }

        return $update_count;
    }

    /**
     * Method to extract the name of a discreet installation sql file from the installation manifest file.
     *
     * @param   object  $element  The XML node to process
     *
     * @return  mixed  Number of queries processed or False on error
     *
     * @since   3.1
     */
    public function parseSQLFiles($element)
    {
        if (!$element || !\count($element->children())) {
            // The tag does not exist.
            return 0;
        }

        $db          = &$this->_db;
        $dbDriver    = $db->getServerType();
        $updateCount = 0;

        // Get the name of the sql file to process
        foreach ($element->children() as $file) {
            $fCharset = strtolower($file->attributes()->charset) === 'utf8' ? 'utf8' : '';
            $fDriver  = strtolower($file->attributes()->driver);

            if ($fDriver === 'mysqli' || $fDriver === 'pdomysql') {
                $fDriver = 'mysql';
            } elseif ($fDriver === 'pgsql') {
                $fDriver = 'postgresql';
            }

            if ($fCharset !== 'utf8' || $fDriver != $dbDriver) {
                continue;
            }

            $sqlfile = $this->getPath('extension_root') . '/' . trim($file);

            // Check that sql files exists before reading. Otherwise raise error for rollback
            if (!file_exists($sqlfile)) {
                Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_FILENOTFOUND', $sqlfile), Log::WARNING, 'jerror');

                return false;
            }

            $buffer = file_get_contents($sqlfile);

            // Graceful exit and rollback if read not successful
            if ($buffer === false) {
                Log::add(Text::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'), Log::WARNING, 'jerror');

                return false;
            }

            // Create an array of queries from the sql file
            $queries = self::splitSql($buffer);

            if (\count($queries) === 0) {
                // No queries to process
                continue;
            }

            // Process each query in the $queries array (split out of sql file).
            foreach ($queries as $query) {
                $canFail = \strlen($query) > self::CAN_FAIL_MARKER_LENGTH + 1 &&
                    strtoupper(substr($query, -self::CAN_FAIL_MARKER_LENGTH - 1)) === (self::CAN_FAIL_MARKER . ';');
                $query   = $canFail ? (substr($query, 0, -self::CAN_FAIL_MARKER_LENGTH - 1) . ';') : $query;

                try {
                    $db->setQuery($query)->execute();
                } catch (ExecutionFailureException $e) {
                    if (!$canFail) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()), Log::WARNING, 'jerror');

                        return false;
                    }
                }

                $updateCount++;
            }
        }

        return $updateCount;
    }

    /**
     * Set the schema version for an extension by looking at its latest update
     *
     * @param   \SimpleXMLElement  $schema  Schema Tag
     * @param   integer            $eid     Extension ID
     *
     * @return  void
     *
     * @since   3.1
     */
    public function setSchemaVersion(\SimpleXMLElement $schema, $eid)
    {
        if ($eid && $schema) {
            $db          = $this->getDatabase();
            $schemapaths = $schema->children();

            if (!$schemapaths) {
                return;
            }

            if (\count($schemapaths)) {
                $dbDriver = $db->getServerType();

                $schemapath = '';

                foreach ($schemapaths as $entry) {
                    $attrs = $entry->attributes();

                    if ($attrs['type'] == $dbDriver) {
                        $schemapath = $entry;
                        break;
                    }
                }

                if ($schemapath !== '') {
                    $files = str_replace('.sql', '', Folder::files($this->getPath('extension_root') . '/' . $schemapath, '\.sql$'));
                    usort($files, 'version_compare');

                    // Update the database
                    $query = $db->getQuery(true)
                        ->delete('#__schemas')
                        ->where('extension_id = :extension_id')
                        ->bind(':extension_id', $eid, ParameterType::INTEGER);
                    $db->setQuery($query);

                    if ($db->execute()) {
                        $schemaVersion = end($files);

                        $query->clear()
                            ->insert($db->quoteName('#__schemas'))
                            ->columns([$db->quoteName('extension_id'), $db->quoteName('version_id')])
                            ->values(':extension_id, :version_id')
                            ->bind(':extension_id', $eid, ParameterType::INTEGER)
                            ->bind(':version_id', $schemaVersion);
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            }
        }
    }

    /**
     * Method to process the updates for an item
     *
     * @param   \SimpleXMLElement  $schema  The XML node to process
     * @param   integer            $eid     Extension Identifier
     *
     * @return  boolean|int  Number of SQL updates executed; false on failure.
     *
     * @since   3.1
     */
    public function parseSchemaUpdates(\SimpleXMLElement $schema, $eid)
    {
        $updateCount = 0;

        // Ensure we have an XML element and a valid extension id
        if (!$eid || !$schema) {
            return $updateCount;
        }

        $db          = $this->getDatabase();
        $schemapaths = $schema->children();

        if (!\count($schemapaths)) {
            return $updateCount;
        }

        $dbDriver = $db->getServerType();

        $schemapath = '';

        foreach ($schemapaths as $entry) {
            $attrs = $entry->attributes();

            // Assuming that the type is a mandatory attribute but if it is not mandatory then there should be a discussion for it.
            $uDriver = strtolower($attrs['type']);

            if ($uDriver === 'mysqli' || $uDriver === 'pdomysql') {
                $uDriver = 'mysql';
            } elseif ($uDriver === 'pgsql') {
                $uDriver = 'postgresql';
            }

            if ($uDriver == $dbDriver) {
                $schemapath = $entry;
                break;
            }
        }

        if ($schemapath === '') {
            return $updateCount;
        }

        $files = Folder::files($this->getPath('extension_root') . '/' . $schemapath, '\.sql$');

        if (empty($files)) {
            return $updateCount;
        }

        Log::add(Text::_('JLIB_INSTALLER_SQL_BEGIN'), Log::INFO, 'Update');

        $files = str_replace('.sql', '', $files);
        usort($files, 'version_compare');

        $query = $db->getQuery(true)
            ->select('version_id')
            ->from('#__schemas')
            ->where('extension_id = :extension_id')
            ->bind(':extension_id', $eid, ParameterType::INTEGER);
        $db->setQuery($query);

        $hasVersion = true;

        try {
            $version = $db->loadResult();

            // No version - use initial version.
            if (!$version) {
                $version    = '0.0.0';
                $hasVersion = false;
            }
        } catch (ExecutionFailureException $e) {
            $version = '0.0.0';
        }

        Log::add(Text::sprintf('JLIB_INSTALLER_SQL_BEGIN_SCHEMA', $version), Log::INFO, 'Update');

        foreach ($files as $file) {
            // Skip over files earlier or equal to the latest schema version recorded for this extension.
            if (version_compare($file, $version) <= 0) {
                continue;
            }

            $buffer = file_get_contents(\sprintf("%s/%s/%s.sql", $this->getPath('extension_root'), $schemapath, $file));

            // Graceful exit and rollback if read not successful
            if ($buffer === false) {
                Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_READBUFFER'), Log::WARNING, 'jerror');

                return false;
            }

            // Create an array of queries from the sql file
            $queries = self::splitSql($buffer);

            // Process each query in the $queries array (split out of sql file).
            foreach ($queries as $query) {
                $canFail = \strlen($query) > self::CAN_FAIL_MARKER_LENGTH + 1 &&
                    strtoupper(substr($query, -self::CAN_FAIL_MARKER_LENGTH - 1)) === (self::CAN_FAIL_MARKER . ';');
                $query   = $canFail ? (substr($query, 0, -self::CAN_FAIL_MARKER_LENGTH - 1) . ';') : $query;

                $queryString = (string) $query;
                $queryString = str_replace(["\r", "\n"], ['', ' '], substr($queryString, 0, 80));

                try {
                    $db->setQuery($query)->execute();
                } catch (\RuntimeException $e) {
                    if (!$canFail) {
                        $errorMessage = Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage());

                        // Log the error in the update log file
                        Log::add(Text::sprintf('JLIB_INSTALLER_UPDATE_LOG_QUERY', $file, $queryString), Log::INFO, 'Update');
                        Log::add($errorMessage, Log::INFO, 'Update');
                        Log::add(Text::_('JLIB_INSTALLER_SQL_END_NOT_COMPLETE'), Log::INFO, 'Update');

                        // Show the error message to the user
                        Log::add($errorMessage, Log::WARNING, 'jerror');

                        return false;
                    }
                }

                Log::add(Text::sprintf('JLIB_INSTALLER_UPDATE_LOG_QUERY', $file, $queryString), Log::INFO, 'Update');

                $updateCount++;
            }

            // Update the schema version for this extension
            try {
                $this->updateSchemaTable($eid, $file, $hasVersion);
                $hasVersion = true;
            } catch (ExecutionFailureException $e) {
                Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()), Log::WARNING, 'jerror');

                return false;
            }
        }

        Log::add(Text::_('JLIB_INSTALLER_SQL_END'), Log::INFO, 'Update');

        return $updateCount;
    }

    /**
     * Update the schema table with the latest version
     *
     * @param   int     $eid      Extension ID.
     * @param   string  $version  Latest schema version ID.
     * @param   boolean $update   Should I run an update against an existing record or insert a new one?
     *
     * @return  void
     *
     * @since   4.2.0
     */
    protected function updateSchemaTable(int $eid, string $version, bool $update = false): void
    {
        $db = $this->getDatabase();

        $o = (object) [
            'extension_id' => $eid,
            'version_id'   => $version,
        ];

        try {
            if ($update) {
                $db->updateObject('#__schemas', $o, 'extension_id');
            } else {
                $db->insertObject('#__schemas', $o);
            }
        } catch (ExecutionFailureException $e) {
            /**
             * Safe fallback: delete any existing record and insert afresh.
             *
             * It is possible that the schema version may be populated after we detected it does not
             * exist (or removed after we detected it exists) and before we finish executing the SQL
             * update script. This could happen e.g. if the update SQL script messes with it, or if
             * another process is also tinkering with the #__schemas table.
             *
             * The safe fallback below even runs inside a transaction to prevent interference from
             * another process.
             */
            $db->transactionStart();

            $query = $db->getQuery(true)
                ->delete('#__schemas')
                ->where('extension_id = :extension_id')
                ->bind(':extension_id', $eid, ParameterType::INTEGER);

            $db->setQuery($query)->execute();

            $db->insertObject('#__schemas', $o);

            $db->transactionCommit();
        }
    }

    /**
     * Method to parse through a files element of the installation manifest and take appropriate
     * action.
     *
     * @param   \SimpleXMLElement  $element   The XML node to process
     * @param   integer            $cid       Application ID of application to install to
     * @param   array              $oldFiles  List of old files (SimpleXMLElement's)
     * @param   array              $oldMD5    List of old MD5 sums (indexed by filename with value as MD5)
     *
     * @return  boolean      True on success
     *
     * @since   3.1
     */
    public function parseFiles(\SimpleXMLElement $element, $cid = 0, $oldFiles = null, $oldMD5 = null)
    {
        // Get the array of file nodes to process; we checked whether this had children above.
        if (!$element || !\count($element->children())) {
            // Either the tag does not exist or has no children (hence no files to process) therefore we return zero files processed.
            return 0;
        }

        $copyfiles = [];

        // Get the client info
        $client = ApplicationHelper::getClientInfo($cid);

        /*
         * Here we set the folder we are going to remove the files from.
         */
        if ($client) {
            $pathname    = 'extension_' . $client->name;
            $destination = $this->getPath($pathname);
        } else {
            $pathname    = 'extension_root';
            $destination = $this->getPath($pathname);
        }

        /*
         * Here we set the folder we are going to copy the files from.
         *
         * Does the element have a folder attribute?
         *
         * If so this indicates that the files are in a subdirectory of the source
         * folder and we should append the folder attribute to the source path when
         * copying files.
         */

        $folder = (string) $element->attributes()->folder;

        if ($folder && file_exists($this->getPath('source') . '/' . $folder)) {
            $source = $this->getPath('source') . '/' . $folder;
        } else {
            $source = $this->getPath('source');
        }

        // Work out what files have been deleted
        if ($oldFiles && ($oldFiles instanceof \SimpleXMLElement)) {
            $oldEntries = $oldFiles->children();

            if (\count($oldEntries)) {
                $deletions = $this->findDeletedFiles($oldEntries, $element->children());

                foreach ($deletions['folders'] as $deleted_folder) {
                    $folder = $destination . '/' . $deleted_folder;

                    if (is_dir($folder) && !Folder::delete($folder)) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $folder), Log::WARNING, 'jerror');
                    }
                }

                foreach ($deletions['files'] as $deleted_file) {
                    $file = $destination . '/' . $deleted_file;

                    if (is_file($file) && !File::delete($file)) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $file), Log::WARNING, 'jerror');
                    }
                }
            }
        }

        $path = [];

        // Copy the MD5SUMS file if it exists
        if (file_exists($source . '/MD5SUMS')) {
            $path['src']  = $source . '/MD5SUMS';
            $path['dest'] = $destination . '/MD5SUMS';
            $path['type'] = 'file';
            $copyfiles[]  = $path;
        }

        // Process each file in the $files array (children of $tagName).
        foreach ($element->children() as $file) {
            $path['src']  = $source . '/' . $file;
            $path['dest'] = $destination . '/' . $file;

            // Is this path a file or folder?
            $path['type'] = $file->getName() === 'folder' ? 'folder' : 'file';

            /*
             * Before we can add a file to the copyfiles array we need to ensure
             * that the folder we are copying our file to exists and if it doesn't,
             * we need to create it.
             */

            if (basename($path['dest']) !== $path['dest']) {
                $newdir = \dirname($path['dest']);

                if (!Folder::create($newdir)) {
                    Log::add(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
                            Text::_('JLIB_INSTALLER_INSTALL'),
                            $newdir
                        ),
                        Log::WARNING,
                        'jerror'
                    );

                    return false;
                }
            }

            // Add the file to the copyfiles array
            $copyfiles[] = $path;
        }

        return $this->copyFiles($copyfiles);
    }

    /**
     * Method to parse through a languages element of the installation manifest and take appropriate
     * action.
     *
     * @param   \SimpleXMLElement  $element  The XML node to process
     * @param   integer            $cid      Application ID of application to install to
     *
     * @return  boolean  True on success
     *
     * @since   3.1
     */
    public function parseLanguages(\SimpleXMLElement $element, $cid = 0)
    {
        // @todo: work out why the below line triggers 'node no longer exists' errors with files
        if (!$element || !\count($element->children())) {
            // Either the tag does not exist or has no children therefore we return zero files processed.
            return 0;
        }

        $copyfiles = [];

        // Get the client info
        $client = ApplicationHelper::getClientInfo($cid);

        // Here we set the folder we are going to copy the files to.
        // 'languages' Files are copied to JPATH_BASE/language/ folder

        $destination = $client->path . '/language';

        /*
         * Here we set the folder we are going to copy the files from.
         *
         * Does the element have a folder attribute?
         *
         * If so this indicates that the files are in a subdirectory of the source
         * folder and we should append the folder attribute to the source path when
         * copying files.
         */

        $folder = (string) $element->attributes()->folder;

        if ($folder && file_exists($this->getPath('source') . '/' . $folder)) {
            $source = $this->getPath('source') . '/' . $folder;
        } else {
            $source = $this->getPath('source');
        }

        // Process each file in the $files array (children of $tagName).
        foreach ($element->children() as $file) {
            /*
             * Language files go in a subfolder based on the language code, ie.
             * <language tag="en-US">en-US.mycomponent.ini</language>
             * would go in the en-US subdirectory of the language folder.
             */

            // We will only install language files where a core language pack
            // already exists.

            if ((string) $file->attributes()->tag !== '') {
                $path        = [];
                $path['src'] = $source . '/' . $file;

                if ((string) $file->attributes()->client !== '') {
                    // Override the client
                    $langclient   = ApplicationHelper::getClientInfo((string) $file->attributes()->client, true);
                    $path['dest'] = $langclient->path . '/language/' . $file->attributes()->tag . '/' . basename((string) $file);
                } else {
                    // Use the default client
                    $path['dest'] = $destination . '/' . $file->attributes()->tag . '/' . basename((string) $file);
                }

                // If the language folder is not present, then the core pack hasn't been installed... ignore
                if (!is_dir(Path::clean(\dirname($path['dest'])))) {
                    continue;
                }
            } else {
                $path         = [];
                $path['src']  = $source . '/' . $file;
                $path['dest'] = $destination . '/' . $file;
            }

            /*
             * Before we can add a file to the copyfiles array we need to ensure
             * that the folder we are copying our file to exists and if it doesn't,
             * we need to create it.
             */

            if (basename($path['dest']) !== $path['dest']) {
                $newdir = \dirname($path['dest']);

                if (!Folder::create($newdir)) {
                    Log::add(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
                            Text::_('JLIB_INSTALLER_INSTALL'),
                            $newdir
                        ),
                        Log::WARNING,
                        'jerror'
                    );

                    return false;
                }
            }

            // Add the file to the copyfiles array
            $copyfiles[] = $path;
        }

        return $this->copyFiles($copyfiles);
    }

    /**
     * Method to parse through a media element of the installation manifest and take appropriate
     * action.
     *
     * @param   \SimpleXMLElement  $element  The XML node to process
     * @param   integer            $cid      Application ID of application to install to
     *
     * @return  boolean     True on success
     *
     * @since   3.1
     */
    public function parseMedia(\SimpleXMLElement $element, $cid = 0)
    {
        if (!$element || !\count($element->children())) {
            // Either the tag does not exist or has no children therefore we return zero files processed.
            return 0;
        }

        $copyfiles = [];

        // Here we set the folder we are going to copy the files to.
        // Default 'media' Files are copied to the JPATH_BASE/media folder

        $folder      = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
        $destination = Path::clean(JPATH_ROOT . '/media' . $folder);

        // Here we set the folder we are going to copy the files from.

        /*
         * Does the element have a folder attribute?
         * If so this indicates that the files are in a subdirectory of the source
         * folder and we should append the folder attribute to the source path when
         * copying files.
         */

        $folder = (string) $element->attributes()->folder;

        if ($folder && file_exists($this->getPath('source') . '/' . $folder)) {
            $source = $this->getPath('source') . '/' . $folder;
        } else {
            $source = $this->getPath('source');
        }

        // Process each file in the $files array (children of $tagName).
        foreach ($element->children() as $file) {
            $path         = [];
            $path['src']  = $source . '/' . $file;
            $path['dest'] = $destination . '/' . $file;

            // Is this path a file or folder?
            $path['type'] = $file->getName() === 'folder' ? 'folder' : 'file';

            /*
             * Before we can add a file to the copyfiles array we need to ensure
             * that the folder we are copying our file to exists and if it doesn't,
             * we need to create it.
             */

            if (basename($path['dest']) !== $path['dest']) {
                $newdir = \dirname($path['dest']);

                if (!Folder::create($newdir)) {
                    Log::add(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
                            Text::_('JLIB_INSTALLER_INSTALL'),
                            $newdir
                        ),
                        Log::WARNING,
                        'jerror'
                    );

                    return false;
                }
            }

            // Add the file to the copyfiles array
            $copyfiles[] = $path;
        }

        return $this->copyFiles($copyfiles);
    }

    /**
     * Method to parse the parameters of an extension, build the JSON string for its default parameters, and return the JSON string.
     *
     * @return  string  JSON string of parameter values
     *
     * @since   3.1
     * @note    This method must always return a JSON compliant string
     */
    public function getParams()
    {
        // Validate that we have a fieldset to use
        if (!isset($this->manifest->config->fields->fieldset)) {
            return '{}';
        }

        // Getting the fieldset tags
        $fieldsets = $this->manifest->config->fields->fieldset;

        // Creating the data collection variable:
        $ini = [];

        // Iterating through the fieldsets:
        foreach ($fieldsets as $fieldset) {
            if (!\count($fieldset->children())) {
                // Either the tag does not exist or has no children therefore we return zero files processed.
                return '{}';
            }

            // Iterating through the fields and collecting the name/default values:
            foreach ($fieldset as $field) {
                // Check against the null value since otherwise default values like "0"
                // cause entire parameters to be skipped.

                if (($name = $field->attributes()->name) === null) {
                    continue;
                }

                if (($value = $field->attributes()->default) === null) {
                    continue;
                }

                $ini[(string) $name] = (string) $value;
            }
        }

        return json_encode($ini);
    }

    /**
     * Copyfiles
     *
     * Copy files from source directory to the target directory
     *
     * @param   array    $files      Array with filenames
     * @param   boolean  $overwrite  True if existing files can be replaced
     *
     * @return  boolean  True on success
     *
     * @since   3.1
     */
    public function copyFiles($files, $overwrite = null)
    {
        /*
         * To allow for manual override on the overwriting flag, we check to see if
         * the $overwrite flag was set and is a boolean value.  If not, use the object
         * allowOverwrite flag.
         */

        if ($overwrite === null || !\is_bool($overwrite)) {
            $overwrite = $this->overwrite;
        }

        /*
         * $files must be an array of filenames.  Verify that it is an array with
         * at least one file to copy.
         */
        if (\is_array($files) && \count($files) > 0) {
            foreach ($files as $file) {
                // Get the source and destination paths
                $filesource = Path::clean($file['src']);
                $filedest   = Path::clean($file['dest']);
                $filetype   = \array_key_exists('type', $file) ? $file['type'] : 'file';

                if (!file_exists($filesource)) {
                    /*
                     * The source file does not exist.  Nothing to copy so set an error
                     * and return false.
                     */
                    Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_NO_FILE', $filesource), Log::WARNING, 'jerror');

                    return false;
                }

                if (($exists = file_exists($filedest)) && !$overwrite) {
                    // It's okay if the manifest already exists
                    if ($this->getPath('manifest') === $filesource) {
                        continue;
                    }

                    // The destination file already exists and the overwrite flag is false.
                    // Set an error and return false.
                    Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_EXISTS', $filedest), Log::WARNING, 'jerror');

                    return false;
                }

                // Copy the folder or file to the new location.
                if ($filetype === 'folder') {
                    if (!Folder::copy($filesource, $filedest, null, $overwrite)) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FOLDER', $filesource, $filedest), Log::WARNING, 'jerror');

                        return false;
                    }

                    $step = ['type' => 'folder', 'path' => $filedest];
                } else {
                    if (!File::copy($filesource, $filedest, null)) {
                        Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FILE', $filesource, $filedest), Log::WARNING, 'jerror');

                        // In 3.2, TinyMCE language handling changed.  Display a special notice in case an older language pack is installed.
                        if (strpos($filedest, 'media/editors/tinymce/jscripts/tiny_mce/langs')) {
                            Log::add(Text::_('JLIB_INSTALLER_NOT_ERROR'), Log::WARNING, 'jerror');
                        }

                        return false;
                    }

                    $step = ['type' => 'file', 'path' => $filedest];
                }

                /*
                 * Since we copied a file/folder, we want to add it to the installation step stack so that
                 * in case we have to roll back the installation we can remove the files copied.
                 */
                if (!$exists) {
                    $this->stepStack[] = $step;
                }
            }
        } else {
            // The $files variable was either not an array or an empty array
            return false;
        }

        return \count($files);
    }

    /**
     * Method to parse through a files element of the installation manifest and remove
     * the files that were installed
     *
     * @param   object   $element  The XML node to process
     * @param   integer  $cid      Application ID of application to remove from
     *
     * @return  boolean  True on success
     *
     * @since   3.1
     */
    public function removeFiles($element, $cid = 0)
    {
        if (!$element || !\count($element->children())) {
            // Either the tag does not exist or has no children therefore we return zero files processed.
            return true;
        }

        $retval = true;

        // Get the client info if we're using a specific client
        if ($cid > -1) {
            $client = ApplicationHelper::getClientInfo($cid);
        } else {
            $client = null;
        }

        // Get the array of file nodes to process
        $files = $element->children();

        if (\count($files) === 0) {
            // No files to process
            return true;
        }

        $folder = '';

        /*
         * Here we set the folder we are going to remove the files from.  There are a few
         * special cases that need to be considered for certain reserved tags.
         */
        switch ($element->getName()) {
            case 'media':
                if ((string) $element->attributes()->destination) {
                    $folder = (string) $element->attributes()->destination;
                } else {
                    $folder = '';
                }

                $source = $client->path . '/media/' . $folder;

                break;

            case 'languages':
                $lang_client = (string) $element->attributes()->client;

                if ($lang_client) {
                    $client = ApplicationHelper::getClientInfo($lang_client, true);
                    $source = $client->path . '/language';
                } else {
                    if ($client) {
                        $source = $client->path . '/language';
                    } else {
                        $source = '';
                    }
                }

                break;

            default:
                if ($client) {
                    $pathname = 'extension_' . $client->name;
                    $source   = $this->getPath($pathname);
                } else {
                    $pathname = 'extension_root';
                    $source   = $this->getPath($pathname);
                }

                break;
        }

        // Process each file in the $files array (children of $tagName).
        foreach ($files as $file) {
            /*
             * If the file is a language, we must handle it differently.  Language files
             * go in a subdirectory based on the language code, ie.
             * <language tag="en_US">en_US.mycomponent.ini</language>
             * would go in the en_US subdirectory of the languages directory.
             */

            if ($file->getName() === 'language' && (string) $file->attributes()->tag !== '') {
                if ($source) {
                    $path = $source . '/' . $file->attributes()->tag . '/' . basename((string) $file);
                } else {
                    $target_client = ApplicationHelper::getClientInfo((string) $file->attributes()->client, true);
                    $path          = $target_client->path . '/language/' . $file->attributes()->tag . '/' . basename((string) $file);
                }

                // If the language folder is not present, then the core pack hasn't been installed... ignore
                if (!is_dir(Path::clean(\dirname($path)))) {
                    continue;
                }
            } else {
                $path = $source . '/' . $file;
            }

            // Actually delete the files/folders

            if (is_dir($path)) {
                $val = Folder::delete($path);
            } else {
                $val = File::delete($path);
            }

            if ($val === false) {
                Log::add('Failed to delete ' . $path, Log::WARNING, 'jerror');
                $retval = false;
            }
        }

        if (!empty($folder)) {
            Folder::delete($source);
        }

        return $retval;
    }

    /**
     * Copies the installation manifest file to the extension folder in the given client
     *
     * @param   integer  $cid  Where to copy the installfile [optional: defaults to 1 (admin)]
     *
     * @return  boolean  True on success, False on error
     *
     * @since   3.1
     */
    public function copyManifest($cid = 1)
    {
        // Get the client info
        $client = ApplicationHelper::getClientInfo($cid);

        $path = ['src' => $this->getPath('manifest')];

        if ($client) {
            $pathname     = 'extension_' . $client->name;
            $path['dest'] = $this->getPath($pathname) . '/' . basename($this->getPath('manifest'));
        } else {
            $pathname     = 'extension_root';
            $path['dest'] = $this->getPath($pathname) . '/' . basename($this->getPath('manifest'));
        }

        return $this->copyFiles([$path], true);
    }

    /**
     * Tries to find the package manifest file
     *
     * @return  boolean  True on success, False on error
     *
     * @since   3.1
     */
    public function findManifest()
    {
        // Do nothing if folder does not exist for some reason
        if (!is_dir(Path::clean($this->getPath('source')))) {
            return false;
        }

        // Main folder manifests (higher priority)
        $parentXmlfiles = Folder::files($this->getPath('source'), '.xml$', false, true);

        // Search for children manifests (lower priority)
        $allXmlFiles    = Folder::files($this->getPath('source'), '.xml$', 1, true);

        // Create an unique array of files ordered by priority
        $xmlfiles = array_unique(array_merge($parentXmlfiles, $allXmlFiles));

        // If at least one XML file exists
        if (!empty($xmlfiles)) {
            foreach ($xmlfiles as $file) {
                // Is it a valid Joomla installation manifest file?
                $manifest = $this->isManifest($file);

                if ($manifest !== null) {
                    // If the root method attribute is set to upgrade, allow file overwrite
                    if ((string) $manifest->attributes()->method === 'upgrade') {
                        $this->upgrade   = true;
                        $this->overwrite = true;
                    }

                    // If the overwrite option is set, allow file overwriting
                    if ((string) $manifest->attributes()->overwrite === 'true') {
                        $this->overwrite = true;
                    }

                    // Set the manifest object and path
                    $this->manifest = $manifest;
                    $this->setPath('manifest', $file);

                    // Set the installation source path to that of the manifest file
                    $this->setPath('source', \dirname($file));

                    return true;
                }
            }

            // None of the XML files found were valid install files
            Log::add(Text::_('JLIB_INSTALLER_ERROR_NOTFINDJOOMLAXMLSETUPFILE'), Log::WARNING, 'jerror');

            return false;
        }

        // No XML files were found in the install folder
        Log::add(Text::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'), Log::WARNING, 'jerror');

        return false;
    }

    /**
     * Is the XML file a valid Joomla installation manifest file.
     *
     * @param   string  $file  An xmlfile path to check
     *
     * @return  \SimpleXMLElement|null  A \SimpleXMLElement, or null if the file failed to parse
     *
     * @since   3.1
     */
    public function isManifest($file)
    {
        $xml = simplexml_load_file($file);

        // If we cannot load the XML file return null
        if (!$xml) {
            return;
        }

        // Check for a valid XML root tag.
        if ($xml->getName() !== 'extension') {
            return;
        }

        // Valid manifest file return the object
        return $xml;
    }

    /**
     * Generates a manifest cache
     *
     * @return string serialised manifest data
     *
     * @since   3.1
     */
    public function generateManifestCache()
    {
        return json_encode(self::parseXMLInstallFile($this->getPath('manifest')));
    }

    /**
     * Cleans up discovered extensions if they're being installed some other way
     *
     * @param   string   $type     The type of extension (component, etc)
     * @param   string   $element  Unique element identifier (e.g. com_content)
     * @param   string   $folder   The folder of the extension (plugins; e.g. system)
     * @param   integer  $client   The client application (administrator or site)
     *
     * @return  object    Result of query
     *
     * @since   3.1
     */
    public function cleanDiscoveredExtension($type, $element, $folder = '', $client = 0)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__extensions'))
            ->where('type = :type')
            ->where('element = :element')
            ->where('folder = :folder')
            ->where('client_id = :client_id')
            ->where('state = -1')
            ->bind(':type', $type)
            ->bind(':element', $element)
            ->bind(':folder', $folder)
            ->bind(':client_id', $client, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Compares two "files" entries to find deleted files/folders
     *
     * @param   array  $oldFiles  An array of \SimpleXMLElement objects that are the old files
     * @param   array  $newFiles  An array of \SimpleXMLElement objects that are the new files
     *
     * @return  array  An array with the delete files and folders in findDeletedFiles[files] and findDeletedFiles[folders] respectively
     *
     * @since   3.1
     */
    public function findDeletedFiles($oldFiles, $newFiles)
    {
        // The magic find deleted files function!
        // The files that are new
        $files = [];

        // The folders that are new
        $folders = [];

        // The folders of the files that are new
        $containers = [];

        // A list of files to delete
        $files_deleted = [];

        // A list of folders to delete
        $folders_deleted = [];

        foreach ($newFiles as $file) {
            switch ($file->getName()) {
                case 'folder':
                    // Add any folders to the list
                    $folders[] = (string) $file;
                    break;

                case 'file':
                default:
                    // Add any files to the list
                    $files[] = (string) $file;

                    // Now handle the folder part of the file to ensure we get any containers
                    // Break up the parts of the directory
                    $container_parts = explode('/', \dirname((string) $file));

                    // Make sure this is clean and empty
                    $container = '';

                    foreach ($container_parts as $part) {
                        // Iterate through each part
                        // Add a slash if its not empty
                        if (!empty($container)) {
                            $container .= '/';
                        }

                        // Append the folder part
                        $container .= $part;

                        if (!\in_array($container, $containers)) {
                            // Add the container if it doesn't already exist
                            $containers[] = $container;
                        }
                    }
                    break;
            }
        }

        foreach ($oldFiles as $file) {
            switch ($file->getName()) {
                case 'folder':
                    if (!\in_array((string) $file, $folders)) {
                        // See whether the folder exists in the new list
                        if (!\in_array((string) $file, $containers)) {
                            // Check if the folder exists as a container in the new list
                            // If it's not in the new list or a container then delete it
                            $folders_deleted[] = (string) $file;
                        }
                    }
                    break;

                case 'file':
                default:
                    if (!\in_array((string) $file, $files)) {
                        // Look if the file exists in the new list
                        if (!\in_array(\dirname((string) $file), $folders)) {
                            // Look if the file is now potentially in a folder
                            $files_deleted[] = (string) $file;
                        }
                    }
                    break;
            }
        }

        return ['files' => $files_deleted, 'folders' => $folders_deleted];
    }

    /**
     * Loads an MD5SUMS file into an associative array
     *
     * @param   string  $filename  Filename to load
     *
     * @return  array  Associative array with filenames as the index and the MD5 as the value
     *
     * @since   3.1
     */
    public function loadMD5Sum($filename)
    {
        if (!file_exists($filename)) {
            // Bail if the file doesn't exist
            return false;
        }

        $data   = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $retval = [];

        foreach ($data as $row) {
            // Split up the data
            $results = explode('  ', $row);

            // Cull any potential prefix
            $results[1] = str_replace('./', '', $results[1]);

            // Throw into the array
            $retval[$results[1]] = $results[0];
        }

        return $retval;
    }

    /**
     * Parse a XML install manifest file.
     *
     * XML Root tag should be 'install' except for languages which use meta file.
     *
     * @param   string  $path  Full path to XML file.
     *
     * @return  array  XML metadata.
     *
     * @since   3.0.0
     */
    public static function parseXMLInstallFile($path)
    {
        // Check if xml file exists.
        if (!file_exists($path)) {
            return false;
        }

        // Read the file to see if it's a valid component XML file
        $xml = simplexml_load_file($path);

        if (!$xml) {
            return false;
        }

        // Check for a valid XML root tag.

        // Extensions use 'extension' as the root tag.  Languages use 'metafile' instead

        $name = $xml->getName();

        if ($name !== 'extension' && $name !== 'metafile') {
            unset($xml);

            return false;
        }

        $data = [];

        $data['name'] = (string) $xml->name;

        // Check if we're a language. If so use metafile.
        $data['type'] = $xml->getName() === 'metafile' ? 'language' : (string) $xml->attributes()->type;

        $data['creationDate'] = ((string) $xml->creationDate) ?: Text::_('JLIB_UNKNOWN');
        $data['author']       = ((string) $xml->author) ?: Text::_('JLIB_UNKNOWN');

        $data['copyright']   = (string) $xml->copyright;
        $data['authorEmail'] = (string) $xml->authorEmail;
        $data['authorUrl']   = (string) $xml->authorUrl;
        $data['version']     = (string) $xml->version;
        $data['description'] = (string) $xml->description;
        $data['group']       = (string) $xml->group;

        // Child template specific fields.
        if (isset($xml->inheritable)) {
            $data['inheritable'] = (string) $xml->inheritable === '0' ? false : true;
        }

        // Child template specific fields.
        if (isset($xml->namespace) && (string) $xml->namespace !== '') {
            $data['namespace'] = (string) $xml->namespace;
        }

        if (isset($xml->parent) && (string) $xml->parent !== '') {
            $data['parent'] = (string) $xml->parent;
        }

        if ($xml->files && \count($xml->files->children())) {
            $filename         = basename($path);
            $data['filename'] = File::stripExt($filename);

            foreach ($xml->files->children() as $oneFile) {
                if ((string) $oneFile->attributes()->plugin) {
                    $data['filename'] = (string) $oneFile->attributes()->plugin;
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Gets a list of available install adapters.
     *
     * @param   array  $options  An array of options to inject into the adapter
     * @param   array  $custom   Array of custom install adapters
     *
     * @return  string[]  An array of the class names of available install adapters.
     *
     * @since   3.4
     */
    public function getAdapters($options = [], array $custom = [])
    {
        $files    = new \DirectoryIterator($this->_basepath . '/' . $this->_adapterfolder);
        $adapters = [];

        // Process the core adapters
        foreach ($files as $file) {
            $fileName = $file->getFilename();

            // Only load for php files.
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            // Derive the class name from the filename.
            $name  = str_ireplace('.php', '', trim($fileName));
            $name  = str_ireplace('adapter', '', trim($name));
            $class = rtrim($this->_classprefix, '\\') . '\\' . ucfirst($name) . 'Adapter';

            if (!class_exists($class)) {
                // Not namespaced
                $class = $this->_classprefix . ucfirst($name);
            }

            // Core adapters should autoload based on classname, keep this fallback just in case
            if (!class_exists($class)) {
                // Try to load the adapter object
                \JLoader::register($class, $this->_basepath . '/' . $this->_adapterfolder . '/' . $fileName);

                if (!class_exists($class)) {
                    // Skip to next one
                    continue;
                }
            }

            $adapters[] = $name;
        }

        // Add any custom adapters if specified
        if (\count($custom) >= 1) {
            foreach ($custom as $adapter) {
                // Setup the class name
                // @todo - Can we abstract this to not depend on the Joomla class namespace without PHP namespaces?
                $class = $this->_classprefix . ucfirst(trim($adapter));

                // If the class doesn't exist we have nothing left to do but look at the next type. We did our best.
                if (!class_exists($class)) {
                    continue;
                }

                $adapters[] = str_ireplace('.php', '', $fileName);
            }
        }

        return $adapters;
    }

    /**
     * Method to load an adapter instance
     *
     * @param   string  $adapter  Adapter name
     * @param   array   $options  Adapter options
     *
     * @return  InstallerAdapter
     *
     * @since   3.4
     * @throws  \InvalidArgumentException
     */
    public function loadAdapter($adapter, $options = [])
    {
        $class = rtrim($this->_classprefix, '\\') . '\\' . ucfirst($adapter) . 'Adapter';

        if (!class_exists($class)) {
            // Not namespaced
            $class = $this->_classprefix . ucfirst($adapter);
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(\sprintf('The %s install adapter does not exist.', $adapter));
        }

        // Ensure the adapter type is part of the options array
        $options['type'] = $adapter;

        // Check for a possible service from the container otherwise manually instantiate the class
        if (Factory::getContainer()->has($class)) {
            return Factory::getContainer()->get($class);
        }

        $adapter = new $class($this, $this->getDatabase(), $options);

        if ($adapter instanceof ContainerAwareInterface) {
            $adapter->setContainer(Factory::getContainer());
        }

        return $adapter;
    }
}
