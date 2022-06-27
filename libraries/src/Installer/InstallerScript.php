<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\ParameterType;

/**
 * Base install script for use by extensions providing helper methods for common behaviours.
 *
 * @since  3.6
 */
class InstallerScript
{
    /**
     * The version number of the extension.
     *
     * @var    string
     * @since  3.6
     */
    protected $release;

    /**
     * The table the parameters are stored in.
     *
     * @var    string
     * @since  3.6
     */
    protected $paramTable;

    /**
     * The extension name. This should be set in the installer script.
     *
     * @var    string
     * @since  3.6
     */
    protected $extension;

    /**
     * A list of files to be deleted
     *
     * @var    array
     * @since  3.6
     */
    protected $deleteFiles = array();

    /**
     * A list of folders to be deleted
     *
     * @var    array
     * @since  3.6
     */
    protected $deleteFolders = array();

    /**
     * A list of CLI script files to be copied to the cli directory
     *
     * @var    array
     * @since  3.6
     */
    protected $cliScriptFiles = array();

    /**
     * Minimum PHP version required to install the extension
     *
     * @var    string
     * @since  3.6
     */
    protected $minimumPhp;

    /**
     * Minimum Joomla! version required to install the extension
     *
     * @var    string
     * @since  3.6
     */
    protected $minimumJoomla;

    /**
     * Allow downgrades of your extension
     *
     * Use at your own risk as if there is a change in functionality people may wish to downgrade.
     *
     * @var    boolean
     * @since  3.6
     */
    protected $allowDowngrades = false;

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   3.6
     */
    public function preflight($type, $parent)
    {
        // Check for the minimum PHP version before continuing
        if (!empty($this->minimumPhp) && version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPhp), Log::WARNING, 'jerror');

            return false;
        }

        // Check for the minimum Joomla version before continuing
        if (!empty($this->minimumJoomla) && version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomla), Log::WARNING, 'jerror');

            return false;
        }

        // Extension manifest file version
        $this->extension = $parent->getName();
        $this->release   = $parent->getManifest()->version;
        $extensionType   = substr($this->extension, 0, 3);

        // Modules parameters are located in the module table - else in the extension table
        if ($extensionType === 'mod') {
            $this->paramTable = '#__modules';
        } else {
            $this->paramTable = '#__extensions';
        }

        // Abort if the extension being installed is not newer than the currently installed version
        if (!$this->allowDowngrades && strtolower($type) === 'update') {
            $manifest = $this->getItemArray('manifest_cache', '#__extensions', 'element', $this->extension);

            // Check whether we have an old release installed and skip this check when this here is the initial install.
            if (!isset($manifest['version'])) {
                return true;
            }

            $oldRelease = $manifest['version'];

            if (version_compare($this->release, $oldRelease, '<')) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_INSTALLER_INCORRECT_SEQUENCE', $oldRelease, $this->release), 'error');

                return false;
            }
        }

        return true;
    }

    /**
     * Gets each instance of a module in the #__modules table
     *
     * @param   boolean  $isModule  True if the extension is a module as this can have multiple instances
     *
     * @return  array  An array of ID's of the extension
     *
     * @since   3.6
     */
    public function getInstances($isModule)
    {
        $extension = $this->extension;

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select the item(s) and retrieve the id
        $query->select($db->quoteName('id'));

        if ($isModule) {
            $query->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = :extension');
        } else {
            $query->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = :extension');
        }

        $query->bind(':extension', $extension);

        // Set the query and obtain an array of id's
        return $db->setQuery($query)->loadColumn();
    }

    /**
     * Gets parameter value in the extensions row of the extension table
     *
     * @param   string   $name  The name of the parameter to be retrieved
     * @param   integer  $id    The id of the item in the Param Table
     *
     * @return  string  The parameter desired
     *
     * @since   3.6
     */
    public function getParam($name, $id = 0)
    {
        if (!\is_int($id) || $id == 0) {
            // Return false if there is no item given
            return false;
        }

        $params = $this->getItemArray('params', $this->paramTable, 'id', $id);

        return $params[$name];
    }

    /**
     * Sets parameter values in the extensions row of the extension table. Note that the
     * this must be called separately for deleting and editing. Note if edit is called as a
     * type then if the param doesn't exist it will be created
     *
     * @param   array    $paramArray  The array of parameters to be added/edited/removed
     * @param   string   $type        The type of change to be made to the param (edit/remove)
     * @param   integer  $id          The id of the item in the relevant table
     *
     * @return  boolean  True on success
     *
     * @since   3.6
     */
    public function setParams($paramArray = null, $type = 'edit', $id = 0)
    {
        if (!\is_int($id) || $id == 0) {
            // Return false if there is no valid item given
            return false;
        }

        $params = $this->getItemArray('params', $this->paramTable, 'id', $id);

        if ($paramArray) {
            foreach ($paramArray as $name => $value) {
                if ($type === 'edit') {
                    // Add or edit the new variable(s) to the existing params
                    if (\is_array($value)) {
                        // Convert an array into a json encoded string
                        $params[(string) $name] = array_values($value);
                    } else {
                        $params[(string) $name] = (string) $value;
                    }
                } elseif ($type === 'remove') {
                    // Unset the parameter from the array
                    unset($params[(string) $name]);
                }
            }
        }

        // Store the combined new and existing values back as a JSON string
        $paramsString = json_encode($params);

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName($this->paramTable))
            ->set('params = :params')
            ->where('id = :id')
            ->bind(':params', $paramsString)
            ->bind(':id', $id, ParameterType::INTEGER);

        // Update table
        $db->setQuery($query)->execute();

        return true;
    }

    /**
     * Builds a standard select query to produce better DRY code in this script.
     * This should produce a single unique cell which is json encoded - it will then
     * return an associated array with this data in.
     *
     * @param   string  $element     The element to get from the query
     * @param   string  $table       The table to search for the data in
     * @param   string  $column      The column of the database to search from
     * @param   mixed   $identifier  The integer id or the string
     *
     * @return  array  Associated array containing data from the cell
     *
     * @since   3.6
     */
    public function getItemArray($element, $table, $column, $identifier)
    {
        // Get the DB and query objects
        $db = Factory::getDbo();

        $paramType = is_numeric($identifier) ? ParameterType::INTEGER : ParameterType::STRING;

        // Build the query
        $query = $db->getQuery(true)
            ->select($db->quoteName($element))
            ->from($db->quoteName($table))
            ->where($db->quoteName($column) . ' = :id')
            ->bind(':id', $identifier, $paramType);
        $db->setQuery($query);

        // Load the single cell and json_decode data
        return json_decode($db->loadResult(), true);
    }

    /**
     * Remove the files and folders in the given array from
     *
     * @return  void
     *
     * @since   3.6
     */
    public function removeFiles()
    {
        if (!empty($this->deleteFiles)) {
            foreach ($this->deleteFiles as $file) {
                if (file_exists(JPATH_ROOT . $file) && !File::delete(JPATH_ROOT . $file)) {
                    echo Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $file) . '<br>';
                }
            }
        }

        if (!empty($this->deleteFolders)) {
            foreach ($this->deleteFolders as $folder) {
                if (Folder::exists(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder)) {
                    echo Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $folder) . '<br>';
                }
            }
        }
    }

    /**
     * Moves the CLI scripts into the CLI folder in the CMS
     *
     * @return  void
     *
     * @since   3.6
     */
    public function moveCliFiles()
    {
        if (!empty($this->cliScriptFiles)) {
            foreach ($this->cliScriptFiles as $file) {
                $name = basename($file);

                if (file_exists(JPATH_ROOT . $file) && !File::move(JPATH_ROOT . $file, JPATH_ROOT . '/cli/' . $name)) {
                    echo Text::sprintf('JLIB_INSTALLER_FILE_ERROR_MOVE', $name);
                }
            }
        }
    }

    /**
     * Creates the dashboard menu module
     *
     * @param string $dashboard The name of the dashboard
     * @param string $preset    The name of the menu preset
     *
     * @return  void
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function addDashboardMenu(string $dashboard, string $preset)
    {
        $model  = Factory::getApplication()->bootComponent('com_modules')->getMVCFactory()->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $module = array(
            'id'         => 0,
            'asset_id'   => 0,
            'language'   => '*',
            'note'       => '',
            'published'  => 1,
            'assignment' => 0,
            'client_id'  => 1,
            'showtitle'  => 0,
            'content'    => '',
            'module'     => 'mod_submenu',
            'position'   => 'cpanel-' . $dashboard,
        );

        // Try to get a translated module title, otherwise fall back to a fixed string.
        $titleKey         = strtoupper('COM_' . $this->extension . '_DASHBOARD_' . $dashboard . '_TITLE');
        $title            = Text::_($titleKey);
        $module['title']  = ($title === $titleKey) ? ucfirst($dashboard) . ' Dashboard' : $title;

        $module['access'] = (int) Factory::getApplication()->get('access', 1);
        $module['params'] = array(
            'menutype' => '*',
            'preset'   => $preset,
            'style'    => 'System-none',
        );

        if (!$model->save($module)) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_FAILED_TO_CREATE_DASHBOARD', $model->getError()));
        }
    }
}
