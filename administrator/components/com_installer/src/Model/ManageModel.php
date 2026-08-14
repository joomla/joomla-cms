<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Exception;
use Joomla\CMS\Changelog\Changelog;
use Joomla\CMS\Event\Model\BeforeChangeStateEvent;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Menu;
use Joomla\Component\Templates\Administrator\Table\StyleTable;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use RuntimeException;
use stdClass;

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
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\ListModel
     * @since   1.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
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
                'core',
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
        if (!\is_array($eid)) {
            $eid = [$eid];
        }

        // Get a table object for the extension type
        $table      = new Extension($this->getDatabase());
        $context    = $this->option . '.' . $this->name;
        $dispatcher = $this->getDispatcher();

        PluginHelper::importPlugin('extension', null, true, $dispatcher);

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

            // Trigger the before change state event.
            $dispatcher->dispatch('onExtensionChangeState', new BeforeChangeStateEvent('onExtensionChangeState', [
                'context' => $context,
                'subject' => $eid,
                'value'   => $value,
            ]));

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
        if (!\is_array($eid)) {
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
	 * Rebuilds the admin menu from the extension's manifest file.
	 *
	 * @param   integer  $eid  The extension ID
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
     * @since   __DEPLOY_VERSION__
     */
	public function recreateMenu(int $eid): void
	{
		$db = $this->getDatabase();
		$query = $db->createQuery()
			->select($db->quoteName(['extension_id', 'element', 'type', 'client_id', 'manifest_cache']))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('extension_id') . ' = :eid')
			->bind(':eid', $eid, ParameterType::INTEGER);
		$db->setQuery($query);

		$extension = $db->loadObject();

		if (!$extension) {
			throw new \RuntimeException(Text::sprintf('COM_INSTALLER_ERROR_EXTENSION_NOT_FOUND', $eid), 500);
		}

		if ($extension->type !== 'component' || $extension->client_id != 1) {
			throw new \RuntimeException(Text::sprintf('COM_INSTALLER_ERROR_NOT_ADMIN_COMPONENT', $eid), 500);
		}

		$manifest = json_decode($extension->manifest_cache, true);

		if (!$manifest) {
			throw new RuntimeException(Text::sprintf('COM_INSTALLER_ERROR_MANIFEST_INVALID', $eid), 500);
		}

		$this->rebuildMenuFromManifest($extension);
		$this->cleanCache('mod_menu');
	}

    /**
     * Rebuilds menu entries from the manifest data.
     *
     * @param   stdClass  $extension  The extension object
     *
     * @return  void
     *
     * @throws  Exception
     * @since   __DEPLOY_VERSION__
     */
	private function rebuildMenuFromManifest(stdClass $extension): void
	{
		$component = $extension->element;

		if ($extension->type !== 'component' || $extension->client_id !== 1) {
            throw new \RuntimeException(Text::sprintf('COM_INSTALLER_ERROR_NOT_ADMIN_COMPONENT', $extension->id), 500);
        }

        $menuPath = JPATH_ADMINISTRATOR . '/components/' . $component . '/' . $component . '.xml';

        if (!file_exists($menuPath)) {
            $altComponent = preg_replace('/^com_/', '', $component);
            $altPath = JPATH_ADMINISTRATOR . '/components/' . $component . '/' . $altComponent . '.xml';
            if (file_exists($altPath)) {
                $menuPath = $altPath;
            }
        }

        if (!file_exists($menuPath)) {
            throw new RuntimeException(Text::sprintf('COM_INSTALLER_ERROR_MANIFEST_FILE_NOT_FOUND', $component), 500);
        }

        $xml = simplexml_load_file($menuPath);

        if ($xml === false) {
            throw new RuntimeException(Text::sprintf('COM_INSTALLER_ERROR_MANIFEST_XML_INVALID', $component), 500);
        }

        $mainMenuTitle  = '';
        $mainMenuView   = '';
        $submenuEntries = [];

        if (isset($xml->administration->menu)) {
            $mainMenu = $xml->administration->menu;
            $mainMenuTitle = Text::_((string) $mainMenu);
            $mainMenuView = (string) $mainMenu['view'];
        }

        if (isset($xml->administration->submenu) && isset($xml->administration->submenu->menu)) {
            foreach ($xml->administration->submenu->menu as $submenu) {
                $submenuEntries[] = [
                    'link' => 'index.php?option=' . $component . '&' . ltrim((string) $submenu['link'], '&'),
                    'title' => Text::_((string) $submenu)
                ];
            }
        }

        $this->deleteComponentMenuItems($component);

        $mainMenuId = 0;

        if (!empty($mainMenuTitle)) {
            $mainLink = 'index.php?option=' . $component;
            if (!empty($mainMenuView)) {
                $mainLink .= '&view=' . $mainMenuView;
            }
            $mainMenuId = $this->createMenuItem($component, $mainMenuTitle, 'component', 1, $mainLink);
        }

        if (!empty($submenuEntries) && $mainMenuId > 0) {
            foreach ($submenuEntries as $submenu) {
                $this->createMenuItem($component, $submenu['title'], 'component', $mainMenuId, $submenu['link']);
            }
        }
	}

	/**
	 * Deletes existing menu items for a component.
	 *
	 * @param   string  $component  The component name (e.g., com_sso)
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function deleteComponentMenuItems(string $component): void
	{
        $db       = $this->getDatabase();
        $link     = '%option=' . $component . '%';
        $menuType = 'main';
        $clientId = 1;

		$query = $db->createQuery()
			->select($db->quoteName('id'))
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('link') . ' LIKE :link')
			->where($db->quoteName('menutype') . ' = :menutype')
			->where($db->quoteName('client_id') . ' = :client_id')
			->bind(':link', $link, ParameterType::STRING)
			->bind(':menutype', $menuType, ParameterType::STRING)
			->bind(':client_id', $clientId, ParameterType::INTEGER);
		$db->setQuery($query);
		$menuIds = $db->loadColumn();

		if (!empty($menuIds)) {
			$menuTable = new Menu($db);
			foreach ($menuIds as $id) {
				if ($menuTable->load($id)) {
					$menuTable->delete($id, true);
				}
			}
		}
	}

    /**
     * Creates a menu item with proper nested set values.
     *
     * @param   string       $component  The component name
     * @param   string       $title      The menu item title
     * @param   string       $type       The menu item type
     * @param   integer      $parentId   The parent menu item ID (use 1 for root-level admin menu items)
     * @param   string|null  $link       The link
     *
     * @return  integer  The new menu item ID, or 0 on failure
     *
     * @throws  Exception
     * @since   __DEPLOY_VERSION__
     */
	private function createMenuItem(string $component, string $title, string $type = 'component', int $parentId = 1, ?string $link = null): int
	{
		if ($link === null) {
			$link = 'index.php?option=' . $component;
		}

		$title = trim($title);
		$alias = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $title));
		$alias = trim($alias, '-');

		if (empty($alias)) {
			$alias = strtolower($component) . '-menu';
		}

		$menuTable = new Menu($this->getDatabase());

		$menuTable->bind([
			'menutype'    => 'main',
			'title'       => $title,
			'alias'       => $alias,
			'link'        => $link,
			'type'        => $type,
			'published'   => 1,
			'parent_id'   => $parentId,
			'component_id' => 0,
			'access'      => 1,
			'client_id'   => 1,
			'language'    => '*',
			'browserNav'  => 0,
			'home'        => 0,
			'params'      => '',
			'path'        => $link,
			'img'         => '',
		]);

		if (!$menuTable->check()) {
			throw new RuntimeException($menuTable->getError(), 500);
		}

		$menuTable->setLocation($parentId, 'last-child');

		if (!$menuTable->store()) {
			throw new RuntimeException($menuTable->getError(), 500);
		}

		return (int) $menuTable->id;
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
        if (!\is_array($eid)) {
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

            if (str_contains($rowtype, $langstring)) {
                $rowtype = $row->type;
            }

            if ($row->type) {
                $result = $installer->uninstall($row->type, $id);

                // Build an array of extensions that failed to uninstall
                if ($result === false) {
                    // There was an error in uninstalling the package
                    $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype, $row->name);

                    continue;
                }

                // Package uninstalled successfully
                $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', $rowtype, $row->name);
                $result = true;

                continue;
            }

            // There was an error in uninstalling the package
            $msgs[] = Text::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype, $row->name);
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
     * @return  QueryInterface  The database query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
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
        $query = $db->createQuery()
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
            ->select($db->quoteName(
                [
                    'updates.version',
                    'updates.changelogurl',
                ],
                [
                    'updateVersion',
                    'updateChangelogUrl',
                ]
            ))
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

        $changelogurl = $source === 'manage' ? $extension->changelogurl : $extension->updateChangelogUrl;

        if (!$changelogurl) {
            return '';
        }

        $changelog = new Changelog();
        $changelog->setVersion($source === 'manage' ? $extension->version : $extension->updateVersion);
        $changelog->loadFromXml($changelogurl);

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

        foreach (array_keys($entries) as $name) {
            $field = $changelog->get($name);
            if ($field) {
                $entries[$name] = $changelog->get($name)->data;
            }
        }

        $layout = new FileLayout('joomla.installer.changelog');
        $output = $layout->render($entries);

        return $output;
    }
}
