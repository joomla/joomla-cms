<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Update as UpdateTable;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Updater\Updater;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Language Installer model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class LanguagesModel extends BaseInstallationModel implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * @var    object  Client object.
     * @since  3.1
     */
    protected $client;

    /**
     * @var    array  Languages description.
     * @since  3.1
     */
    protected $data;

    /**
     * @var    string  Language path.
     * @since  3.1
     */
    protected $path;

    /**
     * @var    integer  Total number of languages installed.
     * @since  3.1
     */
    protected $langlist;

    /**
     * @var    integer  Admin Id, author of all generated content.
     * @since  3.1
     */
    protected $adminId;

    /**
     * Constructor: Deletes the default installation config file and recreates it with the good config file.
     *
     * @since  3.1
     */
    public function __construct()
    {
        // Overrides application config and set the configuration.php file so tokens and database works.
        if (file_exists(JPATH_BASE . '/configuration.php')) {
            Factory::getApplication()->setConfiguration(new Registry(new \JConfig()));
        }

        parent::__construct();
    }

    /**
     * Generate a list of language choices to install in the Joomla CMS.
     *
     * @return  array
     *
     * @since   3.1
     */
    public function getItems()
    {
        // Get the extension_id of the en-GB package.
        $db        = $this->getDatabase();
        $extQuery  = $db->getQuery(true);

        $extQuery->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('package'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('pkg_en-GB'))
            ->where($db->quoteName('client_id') . ' = 0');

        $db->setQuery($extQuery);

        $extId = (int) $db->loadResult();

        if ($extId) {
            $updater = Updater::getInstance();

            /*
             * The following function call uses the extension_id of the en-GB package.
             * In #__update_sites_extensions you should have this extension_id linked
             * to the Accredited Translations Repo.
             */
            $updater->findUpdates([$extId], 0);

            $query = $db->getQuery(true);

            // Select the required fields from the updates table.
            $query->select($db->quoteName(['update_id', 'name', 'element', 'version']))
                ->from($db->quoteName('#__updates'))
                ->order($db->quoteName('name'));

            $db->setQuery($query);
            $list = $db->loadObjectList();

            if (!$list || $list instanceof \Exception) {
                $list = [];
            }
        } else {
            $list = [];
        }

        return $list;
    }

    /**
     * Method that installs in Joomla! the selected languages in the Languages View of the installer.
     *
     * @param   array  $lids  List of the update_id value of the languages to install.
     *
     * @return  boolean  True if successful
     */
    public function install($lids)
    {
        $app           = Factory::getApplication();
        $installerBase = new Installer();
        $installerBase->setDatabase($this->getDatabase());

        // Loop through every selected language.
        foreach ($lids as $id) {
            $installer = clone $installerBase;

            // Loads the update database object that represents the language.
            $language = new UpdateTable($this->getDatabase());
            $language->load($id);

            // Get the URL to the XML manifest file of the selected language.
            $remote_manifest = $this->getLanguageManifest($id);

            if (!$remote_manifest) {
                // Could not find the url, the information in the update server may be corrupt.
                $message = Text::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_INSTALL_LANGUAGE', $language->name);
                $message .= ' ' . Text::_('INSTL_DEFAULTLANGUAGE_TRY_LATER');

                $app->enqueueMessage($message, 'warning');

                continue;
            }

            // Based on the language XML manifest get the URL of the package to download.
            $package_url = $this->getPackageUrl($remote_manifest);

            if (!$package_url) {
                // Could not find the URL, maybe the URL is wrong in the update server, or there is no internet access.
                $message = Text::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_INSTALL_LANGUAGE', $language->name);
                $message .= ' ' . Text::_('INSTL_DEFAULTLANGUAGE_TRY_LATER');

                $app->enqueueMessage($message, 'warning');

                continue;
            }

            // Download the package to the tmp folder.
            $package = $this->downloadPackage($package_url);

            if (!$package) {
                $app->enqueueMessage(Text::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_DOWNLOAD_PACKAGE', $package_url), 'error');

                continue;
            }

            // Install the package.
            if (!$installer->install($package['dir'])) {
                // There was an error installing the package.
                $message = Text::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_INSTALL_LANGUAGE', $language->name);
                $message .= ' ' . Text::_('INSTL_DEFAULTLANGUAGE_TRY_LATER');

                $app->enqueueMessage($message, 'warning');

                continue;
            }

            // Cleanup the install files in tmp folder.
            if (!is_file($package['packagefile'])) {
                $package['packagefile'] = $app->get('tmp_path') . '/' . $package['packagefile'];
            }

            InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

            // Delete the installed language from the list.
            $language->delete($id);
        }

        return true;
    }

    /**
     * Gets the manifest file of a selected language from a the language list in an update server.
     *
     * @param   integer  $uid  The id of the language in the #__updates table.
     *
     * @return  string
     *
     * @since   3.1
     */
    protected function getLanguageManifest($uid)
    {
        $instance = new UpdateTable($this->getDatabase());
        $instance->load($uid);

        return trim($instance->detailsurl);
    }

    /**
     * Finds the URL of the package to download.
     *
     * @param   string  $remoteManifest  URL to the manifest XML file of the remote package.
     *
     * @return  string|boolean
     *
     * @since   3.1
     */
    protected function getPackageUrl($remoteManifest)
    {
        $update = new Update();
        $update->loadFromXml($remoteManifest);

        // Get the download url from the remote manifest
        $downloadUrl = $update->downloadurl ?? false;

        // Check if the download url exist, otherwise return empty value
        if ($downloadUrl === false) {
            return '';
        }

        return trim($downloadUrl->_data);
    }

    /**
     * Download a language package from a URL and unpack it in the tmp folder.
     *
     * @param   string  $url  URL of the package.
     *
     * @return  array|boolean  Package details or false on failure.
     *
     * @since   3.1
     */
    protected function downloadPackage($url)
    {
        $app = Factory::getApplication();

        // Download the package from the given URL.
        $p_file = InstallerHelper::downloadPackage($url);

        // Was the package downloaded?
        if (!$p_file) {
            $app->enqueueMessage(Text::_('INSTL_ERROR_INVALID_URL'), 'warning');

            return false;
        }

        // Unpack the downloaded package file.
        return InstallerHelper::unpack($app->get('tmp_path') . '/' . $p_file);
    }

    /**
     * Get Languages item data for the Administrator.
     *
     * @return  array
     *
     * @since   3.1
     */
    public function getInstalledlangsAdministrator()
    {
        return $this->getInstalledlangs('administrator');
    }

    /**
     * Get Languages item data for the Frontend.
     *
     * @return  array  List of installed languages in the frontend application.
     *
     * @since   3.1
     */
    public function getInstalledlangsFrontend()
    {
        return $this->getInstalledlangs('site');
    }

    /**
     * Get Languages item data.
     *
     * @param   string  $clientName  Name of the cms client.
     *
     * @return  array
     *
     * @since   3.1
     */
    protected function getInstalledlangs($clientName = 'administrator')
    {
        // Get information.
        $path     = $this->getPath();
        $client   = $this->getClient($clientName);
        $langlist = $this->getLanguageList($client->id);

        // Compute all the languages.
        $data = [];

        foreach ($langlist as $lang) {
            $file = $path . '/' . $lang . '/langmetadata.xml';

            if (!is_file($file)) {
                $file = $path . '/' . $lang . '/' . $lang . '.xml';
            }

            $info          = Installer::parseXMLInstallFile($file);
            $row           = new \stdClass();
            $row->language = $lang;

            if (!\is_array($info)) {
                continue;
            }

            foreach ($info as $key => $value) {
                $row->$key = $value;
            }

            // If current then set published.
            $params = ComponentHelper::getParams('com_languages');

            if ($params->get($client->name, 'en-GB') == $row->language) {
                $row->published = 1;
            } else {
                $row->published = 0;
            }

            $row->checked_out = null;
            $data[]           = $row;
        }

        usort($data, [$this, 'compareLanguages']);

        return $data;
    }

    /**
     * Get installed languages data.
     *
     * @param   integer  $clientId  The client ID to retrieve data for.
     *
     * @return  object  The language data.
     *
     * @since   3.1
     */
    protected function getLanguageList($clientId = 1)
    {
        // Create a new db object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select field element from the extensions table.
        $query->select($db->quoteName(['element', 'name']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('language'))
            ->where($db->quoteName('state') . ' = 0')
            ->where($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('client_id') . ' = ' . (int) $clientId);

        $db->setQuery($query);

        $this->langlist = $db->loadColumn();

        return $this->langlist;
    }

    /**
     * Compare two languages in order to sort them.
     *
     * @param   object  $lang1  The first language.
     * @param   object  $lang2  The second language.
     *
     * @return  integer
     *
     * @since   3.1
     */
    protected function compareLanguages($lang1, $lang2)
    {
        return strcmp($lang1->name, $lang2->name);
    }

    /**
     * Get the languages folder path.
     *
     * @return  string  The path to the languages folders.
     *
     * @since   3.1
     */
    protected function getPath()
    {
        if ($this->path === null) {
            $client     = $this->getClient();
            $this->path = LanguageHelper::getLanguagePath($client->path);
        }

        return $this->path;
    }

    /**
     * Get the client object of Administrator or Frontend.
     *
     * @param   string  $client  Name of the client object.
     *
     * @return  object
     *
     * @since   3.1
     */
    protected function getClient($client = 'administrator')
    {
        $this->client = ApplicationHelper::getClientInfo($client, true);

        return $this->client;
    }

    /**
     * Set the default language.
     *
     * @param   string  $language    The language to be set as default.
     * @param   string  $clientName  The name of the CMS client.
     *
     * @return  boolean
     *
     * @since   3.1
     */
    public function setDefault($language, $clientName = 'administrator')
    {
        $client = $this->getClient($clientName);

        $params = ComponentHelper::getParams('com_languages');
        $params->set($client->name, $language);

        $table = new Extension($this->getDatabase());
        $id    = $table->find(['element' => 'com_languages']);

        // Load
        if (!$table->load($id)) {
            Factory::getApplication()->enqueueMessage($table->getError(), 'warning');

            return false;
        }

        $table->params = (string) $params;

        // Pre-save checks.
        if (!$table->check()) {
            Factory::getApplication()->enqueueMessage($table->getError(), 'warning');

            return false;
        }

        // Save the changes.
        if (!$table->store()) {
            Factory::getApplication()->enqueueMessage($table->getError(), 'warning');

            return false;
        }

        return true;
    }

    /**
     * Get the model form.
     *
     * @param   string|null $view  The view being processed.
     *
     * @return  mixed  JForm object on success, false on failure.
     *
     * @since   3.1
     */
    public function getForm($view = null)
    {
        if (!$view) {
            $view = Factory::getApplication()->getInput()->getWord('view', 'defaultlanguage');
        }

        // Get the form.
        Form::addFormPath(JPATH_COMPONENT . '/forms');
        Form::addFieldPath(JPATH_COMPONENT . '/model/fields');
        Form::addRulePath(JPATH_COMPONENT . '/model/rules');

        try {
            $form = Form::getInstance('jform', $view, ['control' => 'jform']);
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        // Check the session for previously entered form data.
        $data = (array) $this->getOptions();

        // Bind the form data if present.
        if (!empty($data)) {
            $form->bind($data);
        }

        return $form;
    }
}
