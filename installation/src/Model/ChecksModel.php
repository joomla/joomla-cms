<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks model for the Joomla Core Installer.
 *
 * @since  4.0.0
 */
class ChecksModel extends BaseInstallationModel
{
    /**
     * Checks the availability of the parse_ini_file and parse_ini_string functions.
     *
     * @return  boolean  True if the method exists.
     *
     * @since   3.1
     */
    public function getIniParserAvailability()
    {
        $disabled_functions = ini_get('disable_functions');

        if (!empty($disabled_functions)) {
            // Attempt to detect them in the PHP INI disable_functions variable.
            $disabled_functions = explode(',', trim($disabled_functions));
            $number_of_disabled_functions = count($disabled_functions);

            for ($i = 0, $l = $number_of_disabled_functions; $i < $l; $i++) {
                $disabled_functions[$i] = trim($disabled_functions[$i]);
            }

            $result = !in_array('parse_ini_string', $disabled_functions);
        } else {
            // Attempt to detect their existence; even pure PHP implementation of them will trigger a positive response, though.
            $result = function_exists('parse_ini_string');
        }

        return $result;
    }

    /**
     * Gets PHP options.
     *
     * @return  array  Array of PHP config options
     *
     * @since   3.1
     */
    public function getPhpOptions()
    {
        $options = [];

        // Check for zlib support.
        $option = new \stdClass();
        $option->label  = Text::_('INSTL_ZLIB_COMPRESSION_SUPPORT');
        $option->state  = extension_loaded('zlib');
        $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_ZLIB_COMPRESSION_SUPPORT');
        $options[] = $option;

        // Check for XML support.
        $option = new \stdClass();
        $option->label  = Text::_('INSTL_XML_SUPPORT');
        $option->state  = extension_loaded('xml');
        $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_XML_SUPPORT');
        $options[] = $option;

        // Check for database support.
        // We are satisfied if there is at least one database driver available.
        $available = DatabaseDriver::getConnectors();
        $option = new \stdClass();
        $option->label  = Text::_('INSTL_DATABASE_SUPPORT');
        $option->label .= '<br>(' . implode(', ', $available) . ')';
        $option->state  = count($available);
        $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_DATABASE_SUPPORT');
        $options[] = $option;

        // Check for mbstring options.
        if (extension_loaded('mbstring')) {
            // Check for default MB language.
            $option = new \stdClass();
            $option->label  = Text::_('INSTL_MB_LANGUAGE_IS_DEFAULT');
            $option->state  = (strtolower(ini_get('mbstring.language')) == 'neutral');
            $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_MBLANG_NOTDEFAULT');
            $options[] = $option;

            // Check for MB function overload.
            $option = new \stdClass();
            $option->label  = Text::_('INSTL_MB_STRING_OVERLOAD_OFF');
            $option->state  = (ini_get('mbstring.func_overload') == 0);
            $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_MBSTRING_OVERLOAD_OFF');
            $options[] = $option;
        }

        // Check for a missing native parse_ini_file implementation.
        $option = new \stdClass();
        $option->label  = Text::_('INSTL_PARSE_INI_FILE_AVAILABLE');
        $option->state  = $this->getIniParserAvailability();
        $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_PARSE_INI_FILE_AVAILABLE');
        $options[] = $option;

        // Check for missing native json_encode / json_decode support.
        $option = new \stdClass();
        $option->label  = Text::_('INSTL_JSON_SUPPORT_AVAILABLE');
        $option->state  = function_exists('json_encode') && function_exists('json_decode');
        $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_JSON_SUPPORT_AVAILABLE');
        $options[] = $option;

        // Check for configuration file writable.
        $writable = (is_writable(JPATH_CONFIGURATION . '/configuration.php')
            || (!file_exists(JPATH_CONFIGURATION . '/configuration.php') && is_writable(JPATH_ROOT)));

        $option = new \stdClass();
        $option->label  = Text::sprintf('INSTL_WRITABLE', 'configuration.php');
        $option->state  = $writable;
        $option->notice = $option->state ? null : Text::_('INSTL_NOTICE_NEEDSTOBEWRITABLE');
        $options[] = $option;

        return $options;
    }

    /**
     * Checks if all of the mandatory PHP options are met.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function getPhpOptionsSufficient()
    {
        $options = $this->getPhpOptions();

        foreach ($options as $option) {
            if ($option->state === false) {
                $result = $option->state;
            }
        }

        return isset($result) ? false : true;
    }

    /**
     * Gets PHP Settings.
     *
     * @return  array
     *
     * @since   3.1
     */
    public function getPhpSettings()
    {
        $settings = [];

        // Check for display errors.
        $setting = new \stdClass();
        $setting->label = Text::_('INSTL_DISPLAY_ERRORS');
        $setting->state = (bool) ini_get('display_errors');
        $setting->recommended = false;
        $settings[] = $setting;

        // Check for file uploads.
        $setting = new \stdClass();
        $setting->label = Text::_('INSTL_FILE_UPLOADS');
        $setting->state = (bool) ini_get('file_uploads');
        $setting->recommended = true;
        $settings[] = $setting;

        // Check for output buffering.
        $setting = new \stdClass();
        $setting->label = Text::_('INSTL_OUTPUT_BUFFERING');
        $setting->state = (int) ini_get('output_buffering') !== 0;
        $setting->recommended = false;
        $settings[] = $setting;

        // Check for session auto-start.
        $setting = new \stdClass();
        $setting->label = Text::_('INSTL_SESSION_AUTO_START');
        $setting->state = (bool) ini_get('session.auto_start');
        $setting->recommended = false;
        $settings[] = $setting;

        // Check for native ZIP support.
        $setting = new \stdClass();
        $setting->label = Text::_('INSTL_ZIP_SUPPORT_AVAILABLE');
        $setting->state = function_exists('zip_open') && function_exists('zip_read');
        $setting->recommended = true;
        $settings[] = $setting;

        // Check for GD support
        $setting = new \stdClass();
        $setting->label = Text::sprintf('INSTL_EXTENSION_AVAILABLE', 'GD');
        $setting->state = extension_loaded('gd');
        $setting->recommended = true;
        $settings[] = $setting;

        // Check for iconv support
        $setting = new \stdClass();
        $setting->label = Text::sprintf('INSTL_EXTENSION_AVAILABLE', 'iconv');
        $setting->state = function_exists('iconv');
        $setting->recommended = true;
        $settings[] = $setting;

        // Check for intl support
        $setting = new \stdClass();
        $setting->label = Text::sprintf('INSTL_EXTENSION_AVAILABLE', 'intl');
        $setting->state = function_exists('transliterator_transliterate');
        $setting->recommended = true;
        $settings[] = $setting;

        return $settings;
    }

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
     * Method to get the form.
     *
     * @param   string|null  $view  The view being processed.
     *
     * @return  Form|boolean  Form object on success, false on failure.
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
}
