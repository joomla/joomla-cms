<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Setup model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class SetupModel extends BaseInstallationModel
{
    /**
     * Get the current setup options from the session.
     *
     * @return  array  An array of options from the session.
     *
     * @since   3.1
     */
    public function getOptions()
    {
        if (!empty(Factory::getSession()->get('setup.options', []))) {
            return Factory::getSession()->get('setup.options', []);
        }
    }

    /**
     * Store the current setup options in the session.
     *
     * @param   array  $options  The installation options.
     *
     * @return  array  An array of options from the session.
     *
     * @since   3.1
     */
    public function storeOptions($options)
    {
        // Get the current setup options from the session.
        $old = (array) $this->getOptions();

        // Ensure that we have language
        if (!isset($options['language']) || empty($options['language'])) {
            $options['language'] = Factory::getLanguage()->getTag();
        }

        // Store passwords as a separate key that is not used in the forms
        foreach (['admin_password', 'db_pass'] as $passwordField) {
            if (isset($options[$passwordField])) {
                $plainTextKey = $passwordField . '_plain';

                $options[$plainTextKey] = $options[$passwordField];

                unset($options[$passwordField]);
            }
        }

        // Get the session
        $session = Factory::getSession();
        $options['helpurl'] = $session->get('setup.helpurl', null);

        // Merge the new setup options into the current ones and store in the session.
        $options = array_merge($old, (array) $options);
        $session->set('setup.options', $options);

        return $options;
    }

    /**
     * Method to get the form.
     *
     * @param   string|null  $view  The view being processed.
     *
     * @return  Form|boolean  JForm object on success, false on failure.
     *
     * @since   3.1
     */
    public function getForm($view = null)
    {
        if (!$view) {
            $view = Factory::getApplication()->input->getWord('view', 'setup');
        }

        // Get the form.
        Form::addFormPath(JPATH_COMPONENT . '/forms');

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

    /**
     * Method to check the form data.
     *
     * @param   string  $page  The view being checked.
     *
     * @return  array|boolean  Array with the validated form data or boolean false on a validation failure.
     *
     * @since   3.1
     */
    public function checkForm($page = 'setup')
    {
        // Get the posted values from the request and validate them.
        $data   = Factory::getApplication()->input->post->get('jform', [], 'array');
        $return = $this->validate($data, $page);

        // Attempt to save the data before validation.
        $form = $this->getForm();
        $data = $form->filter($data);

        $this->storeOptions($data);

        // Check for validation errors.
        if ($return === false) {
            return false;
        }

        // Store the options in the session.
        return $this->storeOptions($return);
    }

    /**
     * Generate a panel of language choices for the user to select their language.
     *
     * @return  array
     *
     * @since   3.1
     */
    public function getLanguages()
    {
        // Detect the native language.
        $native = LanguageHelper::detectLanguage();

        if (empty($native)) {
            $native = 'en-GB';
        }

        // Get a forced language if it exists.
        $forced = Factory::getApplication()->getLocalise();

        if (!empty($forced['language'])) {
            $native = $forced['language'];
        }

        // Get the list of available languages.
        $list = LanguageHelper::createLanguageList($native);

        if (!$list || $list instanceof \Exception) {
            $list = [];
        }

        return $list;
    }

    /**
     * Method to validate the form data.
     *
     * @param   array        $data  The form data.
     * @param   string|null  $view  The view.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @since   3.1
     */
    public function validate($data, $view = null)
    {
        // Get the form.
        $form = $this->getForm($view);

        // Check for an error.
        if ($form === false) {
            return false;
        }

        // Filter and validate the form data.
        $data   = $form->filter($data);
        $return = $form->validate($data);

        // Check for an error.
        if ($return instanceof \Exception) {
            Factory::getApplication()->enqueueMessage($return->getMessage(), 'warning');

            return false;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            $messages = array_reverse($form->getErrors());

            foreach ($messages as $message) {
                if ($message instanceof \Exception) {
                    Factory::getApplication()->enqueueMessage($message->getMessage(), 'warning');
                } else {
                    Factory::getApplication()->enqueueMessage($message, 'warning');
                }
            }

            return false;
        }

        return $data;
    }

    /**
     * Method to validate the db connection properties.
     *
     * @return  boolean
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function validateDbConnection()
    {
        $options = $this->getOptions();

        // Get the options as an object for easier handling.
        $options = ArrayHelper::toObject($options);

        // Load the backend language files so that the DB error messages work.
        $lang = Factory::getLanguage();
        $currentLang = $lang->getTag();

        // Load the selected language
        if (LanguageHelper::exists($currentLang, JPATH_ADMINISTRATOR)) {
            $lang->load('joomla', JPATH_ADMINISTRATOR, $currentLang, true);
        } else {
            // Pre-load en-GB in case the chosen language files do not exist.
            $lang->load('joomla', JPATH_ADMINISTRATOR, 'en-GB', true);
        }

        // Validate and clean up connection parameters
        $paramsCheck = DatabaseHelper::validateConnectionParameters($options);

        if ($paramsCheck) {
            // Validation error: Enqueue the error message
            Factory::getApplication()->enqueueMessage($paramsCheck, 'error');

            return false;
        }

        // Security check for remote db hosts
        if (!DatabaseHelper::checkRemoteDbHost($options)) {
            // Messages have been enqueued in the called function.
            return false;
        }

        // Get a database object.
        try {
            $db = DatabaseHelper::getDbo(
                $options->db_type,
                $options->db_host,
                $options->db_user,
                $options->db_pass_plain,
                $options->db_name,
                $options->db_prefix,
                false,
                DatabaseHelper::getEncryptionSettings($options)
            );

            $db->connect();
        } catch (\RuntimeException $e) {
            if (
                $options->db_type === 'mysql' && strpos($e->getMessage(), '[1049] Unknown database') === 42
                || $options->db_type === 'pgsql' && strpos($e->getMessage(), 'database "' . $options->db_name . '" does not exist')
            ) {
                // Database doesn't exist: Skip the below checks, they will be done later at database creation
                return true;
            }

            Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 'error');

            return false;
        }

        // Check database server parameters
        $dbServerCheck = DatabaseHelper::checkDbServerParameters($db, $options);

        if ($dbServerCheck) {
            // Some server parameter is not ok: Enqueue the error message
            Factory::getApplication()->enqueueMessage($dbServerCheck, 'error');

            return false;
        }

        return true;
    }
}
