<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Joomla\CMS\Changelog\Changelog;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Extension;
use Joomla\Component\Templates\Administrator\Table\StyleTable;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer Manage Model
 *
 * @since  1.5
 */
class ManageModel extends InstallerModel
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\ListModel
     * @since   1.6
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'status',
                'name',
                'client_id',
                'client', 'client_translated',
                'type', 'type_translated',
                'folder', 'folder_translated',
                'package_id',
                'extension_id',
                'creationDate',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'name', $direction = 'asc')
    {
        $app = Factory::getApplication();

        // Load the filter state.
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
        $this->setState('filter.package_id', $this->getUserStateFromRequest($this->context . '.filter.package_id', 'filter_package_id', null, 'int'));
        $this->setState('filter.status', $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string'));
        $this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
        $this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));
        $this->setState('filter.core', $this->getUserStateFromRequest($this->context . '.filter.core', 'filter_core', '', 'string'));

        $this->setState('message', $app->getUserState('com_installer.message'));
        $this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
        $app->setUserState('com_installer.message', '');
        $app->setUserState('com_installer.extension_message', '');

        parent::populateState($ordering, $direction);
    }

    /**
     * Enable/Disable an extension.
     *
     * @param   array  $eid    Extension ids to un/publish
     * @param   int    $value  Publish value
     *
     * @return  boolean  True on success
     *
     * @throws  \Exception
     *
     * @since   1.5
     */
    public function publish(&$eid = [], $value = 1)
    {
        if (!$this->getCurrentUser()->authorise('core.edit.state', 'com_installer')) {
            Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');

            return false;
        }

        $result = true;

        /*
         * Ensure eid is an array of extension ids
         * @todo: If it isn't an array do we want to set an error and fail?
         */
        if (!is_array($eid)) {
            $eid = [$eid];
        }

        // Get a table object for the extension type
        $table = new Extension($this->getDatabase());

        // Enable the extension in the table and store it in the database
        foreach ($eid as $i => $id) {
            $table->load($id);

            if ($table->type == 'template') {
                $style = new StyleTable($this->getDatabase());

                if ($style->load(['template' => $table->element, 'client_id' => $table->client_id, 'home' => 1])) {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_ERROR_DISABLE_DEFAULT_TEMPLATE_NOT_PERMITTED'), 'notice');
                    unset($eid[$i]);
                    continue;
                }

                // Parent template cannot be disabled if there are children
                if ($style->load(['parent' => $table->element, 'client_id' => $table->client_id])) {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_ERROR_DISABLE_PARENT_TEMPLATE_NOT_PERMITTED'), 'notice');
                    unset($eid[$i]);
                    continue;
                }
            }

            if ($table->protected == 1) {
                $result = false;
                Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
            } else {
                $table->enabled = $value;
            }

            $context = $this->option . '.' . $this->name;

            PluginHelper::importPlugin('extension');
            Factory::getApplication()->triggerEvent('onExtensionChangeState', [$context, $eid, $value]);

            if (!$table->store()) {
                $this->setError($table->getError());
                $result = false;
            }
        }

        // Clear the cached extension data and menu cache
        $this->cleanCache('_system');
        $this->cleanCache('com_modules');
        $this->cleanCache('mod_menu');

        return $result;
    }

    /**
     * Refreshes the cached manifest information for an extension.
     *
     * @param   int|int[]  $eid  extension identifier (key in #__extensions)
     *
     * @return  boolean  result of refresh
     *
     * @since   1.6
     */
    public function refresh($eid)
    {
        if (!is_array($eid)) {
            $eid = [$eid => 0];
        }

        // Get an installer object for the extension type
        $installer = Installer::getInstance();
        $result    = 0;

        // Uninstall the chosen extensions
        foreach ($eid as $id) {
            $result |= $installer->refreshManifestCache($id);
        }

        return $result;
    }

    /**
     * Remove (uninstall) an extension
     *
     * @param   array  $eid  An array of identifiers
     *
     * @return  boolean  True on success
     *
     * @throws  \Exception
     *
     * @since   1.5
     */
    public function remove($eid = [])
    {
        if (!$this->getCurrentUser()->authorise('core.delete', 'com_installer')) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'error');

            return false;
        }

        /*
         * Ensure eid is an array of extension ids in the form id => client_id
         * @todo: If it isn't an array do we want to set an error and fail?
         */
        if (!is_array($eid)) {
            $eid = [$eid => 0];
        }

        // Get an installer object for the extension type
        $installer = Installer::getInstance();
        $row       = new \Joomla\CMS\Table\Extension($this->getDatabase());

        // Uninstall the chosen extensions
        $msgs   = [];
        $result = false;

        foreach ($eid as $id) {
            $id = trim($id);
            $row->load($id);
            $result = false;

            // Do not allow to uninstall locked extensions.
            if ((int) $row->locked === 1) {
                $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_ERROR_LOCKED_EXTENSION', $row->name, $id);

                continue;
            }

            $langstring = 'COM_INSTALLER_TYPE_TYPE_' . strtoupper($row->type);
            $rowtype    = Text::_($langstring);

            if (strpos($rowtype, $langstring) !== false) {
                $rowtype = $row->type;
            }

            if ($row->type) {
                $result = $installer->uninstall($row->type, $id);

                // Build an array of extensions that failed to uninstall
                if ($result === false) {
                    // There was an error in uninstalling the package
                    $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype);

                    continue;
                }

                // Package uninstalled successfully
                $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', $rowtype);
                $result = true;

                continue;
            }

            // There was an error in uninstalling the package
            $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype);
        }

        $msg = implode('<br>', $msgs);
        $app = Factory::getApplication();
        $app->enqueueMessage($msg);
        $this->setState('action', 'remove');
        $this->setState('name', $installer->get('name'));
        $app->setUserState('com_installer.message', $installer->message);
        $app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

        // Clear the cached extension data and menu cache
        $this->cleanCache('_system');
        $this->cleanCache('com_modules');
        $this->cleanCache('com_plugins');
        $this->cleanCache('mod_menu');

        return $result;
    }

    /**
     * Method to get the database query
     *
     * @return  DatabaseQuery  The database query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->select('2*protected+(1-protected)*enabled AS status')
            ->from('#__extensions')
            ->where('state = 0');

        // Process select filters.
        $status    = $this->getState('filter.status', '');
        $type      = $this->getState('filter.type');
        $clientId  = $this->getState('filter.client_id', '');
        $folder    = $this->getState('filter.folder');
        $core      = $this->getState('filter.core', '');
        $packageId = $this->getState('filter.package_id', '');

        if ($status !== '') {
            if ($status === '2') {
                $query->where('protected = 1');
            } elseif ($status === '3') {
                $query->where('protected = 0');
            } else {
                $status = (int) $status;
                $query->where($db->quoteName('protected') . ' = 0')
                    ->where($db->quoteName('enabled') . ' = :status')
                    ->bind(':status', $status, ParameterType::INTEGER);
            }
        }

        if ($type) {
            $query->where($db->quoteName('type') . ' = :type')
                ->bind(':type', $type);
        }

        if ($clientId !== '') {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('client_id') . ' = :clientid')
                ->bind(':clientid', $clientId, ParameterType::INTEGER);
        }

        if ($packageId !== '') {
            $packageId = (int) $packageId;
            $query->where(
                '((' . $db->quoteName('package_id') . ' = :packageId1) OR '
                . '(' . $db->quoteName('extension_id') . ' = :packageId2))'
            )
                ->bind([':packageId1',':packageId2'], $packageId, ParameterType::INTEGER);
        }

        if ($folder) {
            $folder = $folder === '*' ? '' : $folder;
            $query->where($db->quoteName('folder') . ' = :folder')
                ->bind(':folder', $folder);
        }

        // Filter by core extensions.
        if ($core === '1' || $core === '0') {
            $coreExtensionIds = ExtensionHelper::getCoreExtensionIds();
            $method           = $core === '1' ? 'whereIn' : 'whereNotIn';
            $query->$method($db->quoteName('extension_id'), $coreExtensionIds);
        }

        // Process search filter (extension id).
        $search = $this->getState('filter.search');

        if (!empty($search) && stripos($search, 'id:') === 0) {
            $ids = (int) substr($search, 3);
            $query->where($db->quoteName('extension_id') . ' = :eid')
                ->bind(':eid', $ids, ParameterType::INTEGER);
        }

        // Note: The search for name, ordering and pagination are processed by the parent InstallerModel class (in extension.php).

        return $query;
    }

    /**
     * Load the changelog details for a given extension.
     *
     * @param   integer  $eid     The extension ID
     * @param   string   $source  The view the changelog is for, this is used to determine which version number to show
     *
     * @return  string  The output to show in the modal.
     *
     * @since   4.0.0
     */
    public function loadChangelog($eid, $source)
    {
        // Get the changelog URL
        $eid   = (int) $eid;
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                        'extensions.element',
                        'extensions.type',
                        'extensions.folder',
                        'extensions.changelogurl',
                        'extensions.manifest_cache',
                        'extensions.client_id',
                    ]
                )
            )
            ->select($db->quoteName('updates.version', 'updateVersion'))
            ->from($db->quoteName('#__extensions', 'extensions'))
            ->join(
                'LEFT',
                $db->quoteName('#__updates', 'updates'),
                $db->quoteName('updates.extension_id') . ' = ' . $db->quoteName('extensions.extension_id')
            )
            ->where($db->quoteName('extensions.extension_id') . ' = :eid')
            ->bind(':eid', $eid, ParameterType::INTEGER);
        $db->setQuery($query);

        $extensions = $db->loadObjectList();
        $this->translate($extensions);
        $extension = array_shift($extensions);

        if (!$extension->changelogurl) {
            return '';
        }

        $changelog = new Changelog();
        $changelog->setVersion($source === 'manage' ? $extension->version : $extension->updateVersion);
        $changelog->loadFromXml($extension->changelogurl);

        // Read all the entries
        $entries = [
            'security' => [],
            'fix'      => [],
            'addition' => [],
            'change'   => [],
            'remove'   => [],
            'language' => [],
            'note'     => [],
        ];

        array_walk(
            $entries,
            function (&$value, $name) use ($changelog) {
                if ($field = $changelog->get($name)) {
                    $value = $changelog->get($name)->data;
                }
            }
        );

        $layout = new FileLayout('joomla.installer.changelog');
        $output = $layout->render($entries);

        return $output;
    }
}
