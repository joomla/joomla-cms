<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer Discover Model
 *
 * @since  1.6
 */
class DiscoverModel extends InstallerModel
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
                'name',
                'client_id',
                'client', 'client_translated',
                'type', 'type_translated',
                'folder', 'folder_translated',
                'extension_id',
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
     * @since   3.1
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
     * Method to get the database query.
     *
     * @return  QueryInterface  The database query
     *
     * @since   3.1
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('state') . ' = -1');

        // Process select filters.
        $type     = $this->getState('filter.type');
        $clientId = $this->getState('filter.client_id');
        $folder   = $this->getState('filter.folder');

        if ($type) {
            $query->where($db->quoteName('type') . ' = :type')
                ->bind(':type', $type);
        }

        if ($clientId != '') {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('client_id') . ' = :clientid')
                ->bind(':clientid', $clientId, ParameterType::INTEGER);
        }

        if ($folder != '' && \in_array($type, ['plugin', 'library', ''])) {
            $folder = $folder === '*' ? '' : $folder;
            $query->where($db->quoteName('folder') . ' = :folder')
                ->bind(':folder', $folder);
        }

        // Process search filter.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('extension_id') . ' = :eid')
                    ->bind(':eid', $ids, ParameterType::INTEGER);
            }
        }

        // Note: The search for name, ordering and pagination are processed by the parent InstallerModel class (in extension.php).

        return $query;
    }

    /**
     * Discover extensions.
     *
     * Finds uninstalled extensions
     *
     * @return  int  The count of discovered extensions
     *
     * @since   1.6
     */
    public function discover()
    {
        // Purge the list of discovered extensions and fetch them again.
        $this->purge();
        $results = Installer::getInstance()->discover();

        // Get all templates, including discovered ones
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['extension_id', 'element', 'folder', 'client_id', 'type']))
            ->from($db->quoteName('#__extensions'));
        $db->setQuery($query);
        $installedtmp = $db->loadObjectList();

        $extensions = [];

        foreach ($installedtmp as $install) {
            $key = implode(
                ':',
                [
                    $install->type,
                    str_replace('\\', '/', $install->element),
                    $install->folder,
                    $install->client_id,
                ]
            );
            $extensions[$key] = $install;
        }

        $count = 0;

        foreach ($results as $result) {
            // Check if we have a match on the element
            $key = implode(
                ':',
                [
                    $result->type,
                    str_replace('\\', '/', $result->element),
                    $result->folder,
                    $result->client_id,
                ]
            );

            if (!\array_key_exists($key, $extensions)) {
                // Put it into the table
                $result->check();
                $result->store();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Installs a discovered extension.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function discover_install()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $eid   = $input->get('cid', 0, 'array');

        if (\is_array($eid) || $eid) {
            if (!\is_array($eid)) {
                $eid = [$eid];
            }

            $eid    = ArrayHelper::toInteger($eid);
            $failed = false;

            foreach ($eid as $id) {
                $installer = new Installer();
                $installer->setDatabase($this->getDatabase());

                $result = $installer->discover_install($id);

                if (!$result) {
                    $failed = true;
                    $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DISCOVER_INSTALLFAILED') . ': ' . $id);
                }
            }

            // @todo - We are only receiving the message for the last Installer instance
            $this->setState('action', 'remove');
            $this->setState('name', $installer->get('name'));
            $app->setUserState('com_installer.message', $installer->message);
            $app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

            if (!$failed) {
                $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DISCOVER_INSTALLSUCCESSFUL'), 'success');
            }
        } else {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DISCOVER_NOEXTENSIONSELECTED'));
        }
    }

    /**
     * Cleans out the list of discovered extensions.
     *
     * @return  boolean  True on success
     *
     * @since   1.6
     */
    public function purge()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__extensions'))
            ->where($db->quoteName('state') . ' = -1');
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException) {
            $this->_message = Text::_('COM_INSTALLER_MSG_DISCOVER_FAILEDTOPURGEEXTENSIONS');

            return false;
        }

        $this->_message = Text::_('COM_INSTALLER_MSG_DISCOVER_PURGEDDISCOVEREDEXTENSIONS');

        return true;
    }

    /**
     * Manipulate the query to be used to evaluate if this is an Empty State to provide specific conditions for this extension.
     *
     * @return QueryInterface
     *
     * @since 4.0.0
     */
    protected function getEmptyStateQuery()
    {
        $query = parent::getEmptyStateQuery();

        $query->where($this->getDatabase()->quoteName('state') . ' = -1');

        return $query;
    }

    /**
     * Checks for not installed extensions in extensions table.
     *
     * @return  boolean  True if there are discovered extensions in the database.
     *
     * @since   4.2.0
     */
    public function checkExtensions()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('state') . ' = -1');
        $db->setQuery($query);
        $discoveredExtensions = $db->loadObjectList();

        return \count($discoveredExtensions) > 0;
    }
}
