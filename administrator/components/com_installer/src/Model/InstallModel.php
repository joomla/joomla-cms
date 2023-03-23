<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Install Model
 *
 * @since  1.5
 */
class InstallModel extends BaseDatabaseModel
{
    /**
     * @var \Joomla\CMS\Table\Table Table object
     */
    protected $_table = null;

    /**
     * @var string URL
     */
    protected $_url = null;

    /**
     * Model context string.
     *
     * @var     string
     */
    protected $_context = 'com_installer.install';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        $this->setState('message', $app->getUserState('com_installer.message'));
        $this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
        $app->setUserState('com_installer.message', '');
        $app->setUserState('com_installer.extension_message', '');

        parent::populateState();
    }

    /**
     * Install an extension from either folder, URL or upload.
     *
     * @return  boolean
     *
     * @since   1.5
     */
    public function install()
    {
        $this->setState('action', 'install');

        $app = Factory::getApplication();

        // Load installer plugins for assistance if required:
        PluginHelper::importPlugin('installer');

        $package = null;

        // This event allows an input pre-treatment, a custom pre-packing or custom installation.
        // (e.g. from a \JSON description).
        $results = $app->triggerEvent('onInstallerBeforeInstallation', [$this, &$package]);

        if (in_array(true, $results, true)) {
            return true;
        }

        if (in_array(false, $results, true)) {
            return false;
        }

        $installType = $app->input->getWord('installtype');
        $installLang = $app->input->getWord('package');

        if ($package === null) {
            switch ($installType) {
                case 'folder':
                    // Remember the 'Install from Directory' path.
                    $app->getUserStateFromRequest($this->_context . '.install_directory', 'install_directory');
                    $package = $this->_getPackageFromFolder();
                    break;

                case 'upload':
                    $package = $this->_getPackageFromUpload();
                    break;

                case 'url':
                    $package = $this->_getPackageFromUrl();
                    break;

                default:
                    $app->setUserState('com_installer.message', Text::_('COM_INSTALLER_NO_INSTALL_TYPE_FOUND'));

                    return false;
            }
        }

        // This event allows a custom installation of the package or a customization of the package:
        $results = $app->triggerEvent('onInstallerBeforeInstaller', [$this, &$package]);

        if (in_array(true, $results, true)) {
            return true;
        }

        if (in_array(false, $results, true)) {
            if (in_array($installType, ['upload', 'url'])) {
                InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
            }

            return false;
        }

        // Check if package was uploaded successfully.
        if (!\is_array($package)) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');

            return false;
        }

        // Get an installer instance.
        $installer = Installer::getInstance();

        /*
         * Check for a Joomla core package.
         * To do this we need to set the source path to find the manifest (the same first step as Installer::install())
         *
         * This must be done before the unpacked check because InstallerHelper::detectType() returns a boolean false since the manifest
         * can't be found in the expected location.
         */
        if (isset($package['dir']) && is_dir($package['dir'])) {
            $installer->setPath('source', $package['dir']);

            if (!$installer->findManifest()) {
                // If a manifest isn't found at the source, this may be a Joomla package; check the package directory for the Joomla manifest
                if (file_exists($package['dir'] . '/administrator/manifests/files/joomla.xml')) {
                    // We have a Joomla package
                    if (in_array($installType, ['upload', 'url'])) {
                        InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
                    }

                    $app->enqueueMessage(
                        Text::sprintf('COM_INSTALLER_UNABLE_TO_INSTALL_JOOMLA_PACKAGE', Route::_('index.php?option=com_joomlaupdate')),
                        'warning'
                    );

                    return false;
                }
            }
        }

        // Was the package unpacked?
        if (empty($package['type'])) {
            if (in_array($installType, ['upload', 'url'])) {
                InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
            }

            $app->enqueueMessage(Text::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'), 'error');

            return false;
        }

        // Install the package.
        if (!$installer->install($package['dir'])) {
            // There was an error installing the package.
            $msg = Text::sprintf('COM_INSTALLER_INSTALL_ERROR', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
            $result = false;
            $msgType = 'error';
        } else {
            // Package installed successfully.
            $msg = Text::sprintf('COM_INSTALLER_INSTALL_SUCCESS', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($installLang . $package['type'])));
            $result = true;
            $msgType = 'message';
        }

        // This event allows a custom a post-flight:
        $app->triggerEvent('onInstallerAfterInstaller', [$this, &$package, $installer, &$result, &$msg]);

        // Set some model state values.
        $app->enqueueMessage($msg, $msgType);
        $this->setState('name', $installer->get('name'));
        $this->setState('result', $result);
        $app->setUserState('com_installer.message', $installer->message);
        $app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
        $app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

        // Cleanup the install files.
        if (!is_file($package['packagefile'])) {
            $package['packagefile'] = $app->get('tmp_path') . '/' . $package['packagefile'];
        }

        InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        // Clear the cached extension data and menu cache
        $this->cleanCache('_system');
        $this->cleanCache('com_modules');
        $this->cleanCache('com_plugins');
        $this->cleanCache('mod_menu');

        return $result;
    }

    /**
     * Works out an installation package from a HTTP upload.
     *
     * @return  mixed   Package definition or false on failure.
     */
    protected function _getPackageFromUpload()
    {
        // Get the uploaded file information.
        $input    = Factory::getApplication()->input;

        // Do not change the filter type 'raw'. We need this to let files containing PHP code to upload. See \JInputFiles::get.
        $userfile = $input->files->get('install_package', null, 'raw');

        // Make sure that file uploads are enabled in php.
        if (!(bool) ini_get('file_uploads')) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'), 'error');

            return false;
        }

        // Make sure that zlib is loaded so that the package can be unpacked.
        if (!extension_loaded('zlib')) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'), 'error');

            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'), 'error');

            return false;
        }

        // Is the PHP tmp directory missing?
        if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR)) {
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br>' . Text::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'),
                'error'
            );

            return false;
        }

        // Is the max upload size too small in php.ini?
        if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE)) {
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br>' . Text::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'),
                'error'
            );

            return false;
        }

        // Check if there was a different problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'), 'error');

            return false;
        }

        // Build the appropriate paths.
        $config   = Factory::getApplication()->getConfig();
        $tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
        $tmp_src  = $userfile['tmp_name'];

        // Move uploaded file.
        File::upload($tmp_src, $tmp_dest, false, true);

        // Unpack the downloaded package file.
        $package = InstallerHelper::unpack($tmp_dest, true);

        return $package;
    }

    /**
     * Install an extension from a directory
     *
     * @return  array  Package details or false on failure
     *
     * @since   1.5
     */
    protected function _getPackageFromFolder()
    {
        $input = Factory::getApplication()->input;

        // Get the path to the package to install.
        $p_dir = $input->getString('install_directory');
        $p_dir = Path::clean($p_dir);

        // Did you give us a valid directory?
        if (!is_dir($p_dir)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'), 'error');

            return false;
        }

        // Detect the package type
        $type = InstallerHelper::detectType($p_dir);

        // Did you give us a valid package?
        if (!$type) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'), 'error');
        }

        $package['packagefile'] = null;
        $package['extractdir'] = null;
        $package['dir'] = $p_dir;
        $package['type'] = $type;

        return $package;
    }

    /**
     * Install an extension from a URL.
     *
     * @return  bool|array  Package details or false on failure.
     *
     * @since   1.5
     */
    protected function _getPackageFromUrl()
    {
        $input = Factory::getApplication()->input;

        // Get the URL of the package to install.
        $url = $input->getString('install_url');

        // Did you give us a URL?
        if (!$url) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), 'error');

            return false;
        }

        // We only allow http & https here
        $uri = new Uri($url);

        if (!in_array($uri->getScheme(), ['http', 'https'])) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL_SCHEME'), 'error');

            return false;
        }

        // Handle updater XML file case:
        if (preg_match('/\.xml\s*$/', $url)) {
            $update = new Update();
            $update->loadFromXml($url);
            $package_url = trim($update->get('downloadurl', false)->_data);

            if ($package_url) {
                $url = $package_url;
            }

            unset($update);
        }

        // Download the package at the URL given.
        $p_file = InstallerHelper::downloadPackage($url);

        // Was the package downloaded?
        if (!$p_file) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $tmp_dest = Factory::getApplication()->get('tmp_path');

        // Unpack the downloaded package file.
        $package = InstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

        return $package;
    }
}
