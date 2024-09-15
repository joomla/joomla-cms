<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Joomla! update controller for the Update view
 *
 * @since  2.5.4
 */
class UpdateController extends BaseController
{
    /**
     * Performs the download of the update package chunked through ajax
     *
     * @return  void
     *
     * @since   2.5.4
     */
    public function download()
    {
        $this->checkToken();

        $user  = $this->app->getIdentity();

        // Make sure logging is working before continue
        try {
            Log::add('Test logging', Log::INFO, 'Update');
        } catch (\Throwable $e) {
            $message = Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOGGING_TEST_FAIL', $e->getMessage());
            $this->setRedirect('index.php?option=com_joomlaupdate', $message, 'error');
            return;
        }

        Log::add(Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_START', $user->id, $user->name, \JVERSION), Log::INFO, 'Update');

        $view = $this->input->get('view');
        if ($view == 'joomlaupdate') { //if we are not using chunked download then directly do the download
            $this->downloadsimple();
        } else {
            $this->input->set('layout', 'download');
            $this->display();
        }
    }

    /**
     * Performs the download of the update package
     *
     * @return  void
     *
     * @since   2.5.4
     */
    public function downloadsimple()
    {
        $this->checkToken();

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');
        $user  = $this->app->getIdentity();

        // Make sure logging is working before continue
        try {
            Log::add('Test logging', Log::INFO, 'Update');
        } catch (\Throwable $e) {
            $message = Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOGGING_TEST_FAIL', $e->getMessage());
            $this->setRedirect('index.php?option=com_joomlaupdate', $message, 'error');
            return;
        }

        Log::add(Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_START', $user->id, $user->name, \JVERSION), Log::INFO, 'Update');

        $result = $model->download(); // normal download
        $file   = $result['basename'];

        $message     = null;
        $messageType = null;

        // The versions mismatch (Use \JVERSION as target version when not set in case of reinstall core files)
        if ($result['version'] !== $this->input->get('targetVersion', \JVERSION, 'string')) {
            $message     = Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_VERSION_WRONG');
            $messageType = 'error';
            $url         = 'index.php?option=com_joomlaupdate';

            $this->app->setUserState('com_joomlaupdate.file', null);
            $this->setRedirect($url, $message, $messageType);

            Log::add($message, Log::ERROR, 'Update');

            return;
        }

        // The validation was not successful so stop.
        if ($result['check'] === false) {
            $message     = Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_CHECKSUM_WRONG');
            $messageType = 'error';
            $url         = 'index.php?option=com_joomlaupdate';

            $this->app->setUserState('com_joomlaupdate.file', null);
            $this->setRedirect($url, $message, $messageType);

            Log::add($message, Log::ERROR, 'Update');

            return;
        }

        if ($file) {
            $this->app->setUserState('com_joomlaupdate.file', $file);
            $url = 'index.php?option=com_joomlaupdate&task=update.install&' . $this->app->getSession()->getFormToken() . '=1';

            Log::add(Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_FILE', $file), Log::INFO, 'Update');
        } else {
            $this->app->setUserState('com_joomlaupdate.file', null);
            $url         = 'index.php?option=com_joomlaupdate';
            $message     = Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_DOWNLOADFAILED');
            $messageType = 'error';
        }

        $this->setRedirect($url, $message, $messageType);
    }

    /**
     * Step through the download of the update package
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function stepdownload()
    {
        // Check the anti-CSRF token
        if (!$this->checkToken('get', false)) {
            $ret = [
                'error'   => true,
                'message' => Text::_('JINVALID_TOKEN_NOTICE'),
            ];
            @ob_end_clean();
            echo json_encode($ret);

            $this->app->close();
        }

        // Try to download the next chunk
        /** @var UpdateModel $model */
        $model   = $this->getModel('Update', 'Administrator');
        $frag    = $this->input->get->getInt('frag', -1);
        $result  = $model->downloadChunked($frag);
        $message = '';

        // Are we done yet?
        if (\is_array($result) && $result['done']) {
            $this->app->setUserState('com_joomlaupdate.file', basename($result['localFile']));

            // If the checksum failed we will return with an error
            if (!$result['valid']) {
                $message = Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_CHECKSUM_WRONG');
            }
        }

        // If the download failed we will return with an error
        if (empty($result)) {
            $message = Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_DOWNLOADFAILED');
        }

        if (!\is_array($result)) {
            $result = [];
        }

        $result['error']   = !empty($message);
        $result['message'] = $message;

        // Return JSON to the browser
        @ob_end_clean();
        echo json_encode($result);

        $this->app->close();
    }


    /**
     * Start the installation of the new Joomla! version
     *
     * @return  void
     *
     * @since   2.5.4
     */
    public function install(): void
    {
        $this->checkToken('get');
        $this->app->setUserState('com_joomlaupdate.oldversion', JVERSION);

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_INSTALL'), Log::INFO, 'Update');

        $file = $this->app->getUserState('com_joomlaupdate.file', null);
        $model->createUpdateFile($file);

        $this->display();
    }

    /**
     * Finalise the upgrade by running the necessary scripts
     *
     * @return  void
     *
     * @since   2.5.4
     */
    public function finalise()
    {
        /*
         * Finalize with login page. Used for pre-token check versions
         * to allow updates without problems but with a maximum of security.
         */
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php?option=com_joomlaupdate&view=update&layout=finaliseconfirm');

            return;
        }

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        try {
            $model->finaliseUpgrade();
        } catch (\Throwable $e) {
            $model->collectError('finaliseUpgrade', $e);
        }

        // Reset update source from "Joomla Next" to "Default"
        $this->app->setUserState('com_joomlaupdate.update_channel_reset', $model->resetUpdateSource());

        // Check for update errors
        if ($model->getErrors()) {
            // The errors already should be logged at this point
            // Collect a messages to show them later in the complete page
            $errors = [];
            foreach ($model->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            $this->app->setUserState('com_joomlaupdate.update_finished_with_error', true);
            $this->app->setUserState('com_joomlaupdate.update_errors', $errors);
        }

        // Check for captured output messages in the installer
        $msg = Installer::getInstance()->get('extension_message');
        if ($msg) {
            $this->app->setUserState('com_joomlaupdate.installer_message', $msg);
        }

        $url = 'index.php?option=com_joomlaupdate&task=update.cleanup&' . Session::getFormToken() . '=1';
        $this->setRedirect($url);
    }

    /**
     * Clean up after ourselves
     *
     * @return  void
     *
     * @since   2.5.4
     */
    public function cleanup()
    {
        /*
         * Cleanup with login page. Used for pre-token check versions to be able to update
         * from =< 3.2.7 to allow updates without problems but with a maximum of security.
         */
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php?option=com_joomlaupdate&view=update&layout=finaliseconfirm');

            return;
        }

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        try {
            $model->cleanUp();
        } catch (\Throwable $e) {
            $model->collectError('cleanUp', $e);
        }

        // Check for update errors
        if ($model->getErrors()) {
            // The errors already should be logged at this point
            // Collect a messages to show them later in the complete page
            $errors = $this->app->getUserState('com_joomlaupdate.update_errors', []);
            foreach ($model->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            $this->app->setUserState('com_joomlaupdate.update_finished_with_error', true);
            $this->app->setUserState('com_joomlaupdate.update_errors', $errors);
        }

        $url = 'index.php?option=com_joomlaupdate&view=joomlaupdate&layout=complete';

        // In case for errored update, redirect to component view
        if ($this->app->getUserState('com_joomlaupdate.update_finished_with_error')) {
            $url .= '&tmpl=component';
        }

        $this->setRedirect($url);
    }

    /**
     * Purges updates.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function purge()
    {
        // Check for request forgeries
        $this->checkToken('request');

        // Purge updates
        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');
        $model->purge();

        $url = 'index.php?option=com_joomlaupdate';
        $this->setRedirect($url, $model->_message);
    }

    /**
     * Uploads an update package to the temporary directory, under a random name
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function upload()
    {
        // Check for request forgeries
        $this->checkToken();

        // Did a non Super User tried to upload something (a.k.a. pathetic hacking attempt)?
        $this->app->getIdentity()->authorise('core.admin') or jexit(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        // Make sure logging is working before continue
        try {
            Log::add('Test logging', Log::INFO, 'Update');
        } catch (\Throwable $e) {
            $message = Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOGGING_TEST_FAIL', $e->getMessage());
            $this->setRedirect('index.php?option=com_joomlaupdate', $message, 'error');
            return;
        }

        Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_UPLOAD'), Log::INFO, 'Update');

        try {
            $model->upload();
        } catch (\RuntimeException $e) {
            $url = 'index.php?option=com_joomlaupdate';
            $this->setRedirect($url, $e->getMessage(), 'error');

            return;
        }

        $token = Session::getFormToken();
        $url   = 'index.php?option=com_joomlaupdate&task=update.captive&' . $token . '=1';
        $this->setRedirect($url);
    }

    /**
     * Checks there is a valid update package and redirects to the captive view for super admin authentication.
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function captive()
    {
        // Check for request forgeries
        $this->checkToken('get');

        // Did a non Super User tried to upload something (a.k.a. pathetic hacking attempt)?
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
        }

        // Do I really have an update package?
        $tempFile = $this->app->getUserState('com_joomlaupdate.temp_file', null);

        if (empty($tempFile) || !is_file($tempFile)) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
        }

        $this->input->set('view', 'upload');
        $this->input->set('layout', 'captive');

        $this->display();
    }

    /**
     * Checks the admin has super administrator privileges and then proceeds with the update.
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function confirm()
    {
        // Check for request forgeries
        $this->checkToken();

        // Did a non Super User tried to upload something (a.k.a. pathetic hacking attempt)?
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
        }

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        // Get the captive file before the session resets
        $tempFile = $this->app->getUserState('com_joomlaupdate.temp_file', null);

        // Do I really have an update package?
        if (!$model->captiveFileExists()) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
        }

        // Try to log in
        $credentials = [
            'username'  => $this->input->post->get('username', '', 'username'),
            'password'  => $this->input->post->get('passwd', '', 'raw'),
            'secretkey' => $this->input->post->get('secretkey', '', 'raw'),
        ];

        $result = $model->captiveLogin($credentials);

        if (!$result) {
            $model->removePackageFiles();

            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
        }

        // Set the update source in the session
        $this->app->setUserState('com_joomlaupdate.file', basename($tempFile));

        try {
            Log::add(Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_FILE', $tempFile), Log::INFO, 'Update');
        } catch (\RuntimeException $exception) {
            // Informational log only
        }

        // Redirect to the actual update page
        $url = 'index.php?option=com_joomlaupdate&task=update.install&' . Session::getFormToken() . '=1';
        $this->setRedirect($url);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  static  This object to support chaining.
     *
     * @since   2.5.4
     */
    public function display($cachable = false, $urlparams = [])
    {
        // Get the document object.
        $document = $this->app->getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', 'update');
        $vFormat = $document->getType();
        $lName   = $this->input->get('layout', 'default', 'string');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Get the model for the view.
            /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
            $model = $this->getModel('Update');

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;
            $view->display();
        }

        return $this;
    }

    /**
     * Checks the admin has super administrator privileges and then proceeds with the final & cleanup steps.
     *
     * @return  void
     *
     * @since   3.6.3
     */
    public function finaliseconfirm()
    {
        // Check for request forgeries
        $this->checkToken();

        // Did a non Super User try do this?
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
        }

        // Get the model
        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        // Try to log in
        $credentials = [
            'username'  => $this->input->post->get('username', '', 'username'),
            'password'  => $this->input->post->get('passwd', '', 'raw'),
            'secretkey' => $this->input->post->get('secretkey', '', 'raw'),
        ];

        $result = $model->captiveLogin($credentials);

        // The login fails?
        if (!$result) {
            $this->setMessage(Text::_('JGLOBAL_AUTH_INVALID_PASS'), 'warning');
            $this->setRedirect('index.php?option=com_joomlaupdate&view=update&layout=finaliseconfirm');

            return;
        }

        // Redirect back to the actual finalise page
        $this->setRedirect('index.php?option=com_joomlaupdate&task=update.finalise&' . Session::getFormToken() . '=1');
    }

    /**
     * Fetch Extension update XML proxy. Used to prevent Access-Control-Allow-Origin errors.
     * Prints a JSON string.
     * Called from JS.
     *
     * @since       3.10.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use batchextensioncompatibility instead.
     *              Example: $updateController->batchextensioncompatibility();
     *
     * @return void
     */
    public function fetchExtensionCompatibility()
    {
        $extensionID          = $this->input->get('extension-id', '', 'DEFAULT');
        $joomlaTargetVersion  = $this->input->get('joomla-target-version', '', 'DEFAULT');
        $joomlaCurrentVersion = $this->input->get('joomla-current-version', '', JVERSION);
        $extensionVersion     = $this->input->get('extension-version', '', 'DEFAULT');

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model                      = $this->getModel('Update');
        $upgradeCompatibilityStatus = $model->fetchCompatibility($extensionID, $joomlaTargetVersion);
        $currentCompatibilityStatus = $model->fetchCompatibility($extensionID, $joomlaCurrentVersion);
        $upgradeUpdateVersion       = false;
        $currentUpdateVersion       = false;

        $upgradeWarning = 0;

        if ($upgradeCompatibilityStatus->state == 1 && !empty($upgradeCompatibilityStatus->compatibleVersions)) {
            $upgradeUpdateVersion = end($upgradeCompatibilityStatus->compatibleVersions);
        }

        if ($currentCompatibilityStatus->state == 1 && !empty($currentCompatibilityStatus->compatibleVersions)) {
            $currentUpdateVersion = end($currentCompatibilityStatus->compatibleVersions);
        }

        if ($upgradeUpdateVersion !== false) {
            $upgradeOldestVersion = $upgradeCompatibilityStatus->compatibleVersions[0];

            if ($currentUpdateVersion !== false) {
                // If there are updates compatible with both CMS versions use these
                $bothCompatibleVersions = array_values(
                    array_intersect($upgradeCompatibilityStatus->compatibleVersions, $currentCompatibilityStatus->compatibleVersions)
                );

                if (!empty($bothCompatibleVersions)) {
                    $upgradeOldestVersion = $bothCompatibleVersions[0];
                    $upgradeUpdateVersion = end($bothCompatibleVersions);
                }
            }

            if (version_compare($upgradeOldestVersion, $extensionVersion, '>')) {
                // Installed version is empty or older than the oldest compatible update: Update required
                $resultGroup = 2;
            } else {
                // Current version is compatible
                $resultGroup = 3;
            }

            if ($currentUpdateVersion !== false && version_compare($upgradeUpdateVersion, $currentUpdateVersion, '<')) {
                // Special case warning when version compatible with target is lower than current
                $upgradeWarning = 2;
            }
        } elseif ($currentUpdateVersion !== false) {
            // No compatible version for target version but there is a compatible version for current version
            $resultGroup = 1;
        } else {
            // No update server available
            $resultGroup = 1;
        }

        // Do we need to capture
        $combinedCompatibilityStatus = [
            'upgradeCompatibilityStatus' => (object) [
                'state'             => $upgradeCompatibilityStatus->state,
                'compatibleVersion' => $upgradeUpdateVersion,
            ],
            'currentCompatibilityStatus' => (object) [
                'state'             => $currentCompatibilityStatus->state,
                'compatibleVersion' => $currentUpdateVersion,
            ],
            'resultGroup'    => $resultGroup,
            'upgradeWarning' => $upgradeWarning,
        ];

        $this->app           = Factory::getApplication();
        $this->app->mimeType = 'application/json';
        $this->app->charSet  = 'utf-8';
        $this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
        $this->app->sendHeaders();

        try {
            echo new JsonResponse($combinedCompatibilityStatus);
        } catch (\Exception $e) {
            echo $e;
        }

        $this->app->close();
    }

    /**
     * Determines the compatibility information for a number of extensions.
     *
     * Called by the Joomla Update JavaScript (PreUpdateChecker.checkNextChunk).
     *
     * @return  void
     * @since   4.2.0
     *
     */
    public function batchextensioncompatibility()
    {
        $joomlaTargetVersion  = $this->input->post->get('joomla-target-version', '', 'DEFAULT');
        $joomlaCurrentVersion = $this->input->post->get('joomla-current-version', JVERSION);
        $extensionInformation = $this->input->post->get('extensions', []);

        /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
        $model = $this->getModel('Update');

        $extensionResults = [];
        $leftover         = [];
        $startTime        = microtime(true);

        foreach ($extensionInformation as $information) {
            // Only process an extension if we have spent less than 5 seconds already
            $currentTime = microtime(true);

            if ($currentTime - $startTime > 5.0) {
                $leftover[] = $information;

                continue;
            }

            // Get the extension information and fetch its compatibility information
            $extensionID                = $information['eid'] ?: '';
            $extensionVersion           = $information['version'] ?: '';
            $upgradeCompatibilityStatus = $model->fetchCompatibility($extensionID, $joomlaTargetVersion);
            $currentCompatibilityStatus = $model->fetchCompatibility($extensionID, $joomlaCurrentVersion);
            $upgradeUpdateVersion       = false;
            $currentUpdateVersion       = false;
            $upgradeWarning             = 0;

            if ($upgradeCompatibilityStatus->state == 1 && !empty($upgradeCompatibilityStatus->compatibleVersions)) {
                $upgradeUpdateVersion = end($upgradeCompatibilityStatus->compatibleVersions);
            }

            if ($currentCompatibilityStatus->state == 1 && !empty($currentCompatibilityStatus->compatibleVersions)) {
                $currentUpdateVersion = end($currentCompatibilityStatus->compatibleVersions);
            }

            if ($upgradeUpdateVersion !== false) {
                $upgradeOldestVersion = $upgradeCompatibilityStatus->compatibleVersions[0];

                if ($currentUpdateVersion !== false) {
                    // If there are updates compatible with both CMS versions use these
                    $bothCompatibleVersions = array_values(
                        array_intersect($upgradeCompatibilityStatus->compatibleVersions, $currentCompatibilityStatus->compatibleVersions)
                    );

                    if (!empty($bothCompatibleVersions)) {
                        $upgradeOldestVersion = $bothCompatibleVersions[0];
                        $upgradeUpdateVersion = end($bothCompatibleVersions);
                    }
                }

                if (version_compare($upgradeOldestVersion, $extensionVersion, '>')) {
                    // Installed version is empty or older than the oldest compatible update: Update required
                    $resultGroup = 2;
                } else {
                    // Current version is compatible
                    $resultGroup = 3;
                }

                if ($currentUpdateVersion !== false && version_compare($upgradeUpdateVersion, $currentUpdateVersion, '<')) {
                    // Special case warning when version compatible with target is lower than current
                    $upgradeWarning = 2;
                }
            } elseif ($currentUpdateVersion !== false) {
                // No compatible version for target version but there is a compatible version for current version
                $resultGroup = 1;
            } else {
                // No update server available
                $resultGroup = 1;
            }

            // Do we need to capture
            $extensionResults[] = [
                'id'                         => $extensionID,
                'upgradeCompatibilityStatus' => (object) [
                    'state'             => $upgradeCompatibilityStatus->state,
                    'compatibleVersion' => $upgradeUpdateVersion,
                ],
                'currentCompatibilityStatus' => (object) [
                    'state'             => $currentCompatibilityStatus->state,
                    'compatibleVersion' => $currentUpdateVersion,
                ],
                'resultGroup'    => $resultGroup,
                'upgradeWarning' => $upgradeWarning,
            ];
        }

        $this->app->mimeType = 'application/json';
        $this->app->charSet  = 'utf-8';
        $this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
        $this->app->sendHeaders();

        try {
            $return = [
                'compatibility' => $extensionResults,
                'extensions'    => $leftover,
            ];

            echo new JsonResponse($return);
        } catch (\Exception $e) {
            echo $e;
        }

        $this->app->close();
    }

    /**
     * Fetch and report updates in \JSON format, for AJAX requests
     *
     * @return  void
     *
     * @since   3.10.10
     */
    public function ajax()
    {
        if (!Session::checkToken('get')) {
            $this->app->setHeader('status', 403, true);
            $this->app->sendHeaders();
            echo Text::_('JINVALID_TOKEN_NOTICE');
            $this->app->close();
        }

        /** @var UpdateModel $model */
        $model      = $this->getModel('Update');
        $updateInfo = $model->getUpdateInformation();

        $update   = [];
        $update[] = ['version' => $updateInfo['latest']];

        echo json_encode($update);

        $this->app->close();
    }
}
