<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Helper;

use Generator;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;
use Joomla\String\StringHelper;

/**
 * Actionlogs component helper.
 *
 * @since  3.9.0
 */
class ActionlogsHelper
{
    /**
     * Array of characters starting a formula
     *
     * @var    array
     *
     * @since  3.9.7
     */
    private static $characters = array('=', '+', '-', '@');

    /**
     * Method to convert logs objects array to an iterable type for use with a CSV export
     *
     * @param   array|\Traversable  $data  The logs data objects to be exported
     *
     * @return  Generator
     *
     * @since   3.9.0
     *
     * @throws  \InvalidArgumentException
     */
    public static function getCsvData($data): Generator
    {
        if (!is_iterable($data)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s() requires an array or object implementing the Traversable interface, a %s was given.',
                    __METHOD__,
                    \gettype($data) === 'object' ? \get_class($data) : \gettype($data)
                )
            );
        }

        $disabledText = Text::_('COM_ACTIONLOGS_DISABLED');

        // Header row
        yield ['Id', 'Action', 'Extension', 'Date', 'Name', 'IP Address'];

        foreach ($data as $log) {
            $extension = strtok($log->extension, '.');

            static::loadTranslationFiles($extension);

            yield array(
                'id'         => $log->id,
                'message'    => self::escapeCsvFormula(strip_tags(static::getHumanReadableLogMessage($log, false))),
                'extension'  => self::escapeCsvFormula(Text::_($extension)),
                'date'       => (new Date($log->log_date, new \DateTimeZone('UTC')))->format('Y-m-d H:i:s T'),
                'name'       => self::escapeCsvFormula($log->name),
                'ip_address' => self::escapeCsvFormula($log->ip_address === 'COM_ACTIONLOGS_DISABLED' ? $disabledText : $log->ip_address)
            );
        }
    }

    /**
     * Load the translation files for an extension
     *
     * @param   string  $extension  Extension name
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public static function loadTranslationFiles($extension)
    {
        static $cache = array();
        $extension = strtolower($extension);

        if (isset($cache[$extension])) {
            return;
        }

        $lang   = Factory::getLanguage();
        $source = '';

        switch (substr($extension, 0, 3)) {
            case 'com':
            default:
                $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
                break;

            case 'lib':
                $source = JPATH_LIBRARIES . '/' . substr($extension, 4);
                break;

            case 'mod':
                $source = JPATH_SITE . '/modules/' . $extension;
                break;

            case 'plg':
                $parts = explode('_', $extension, 3);

                if (\count($parts) > 2) {
                    $source = JPATH_PLUGINS . '/' . $parts[1] . '/' . $parts[2];
                }
                break;

            case 'pkg':
                $source = JPATH_SITE;
                break;

            case 'tpl':
                $source = JPATH_BASE . '/templates/' . substr($extension, 4);
                break;
        }

        $lang->load($extension, JPATH_ADMINISTRATOR)
            || $lang->load($extension, $source);

        if (!$lang->hasKey(strtoupper($extension))) {
            $lang->load($extension . '.sys', JPATH_ADMINISTRATOR)
                || $lang->load($extension . '.sys', $source);
        }

        $cache[$extension] = true;
    }

    /**
     * Get parameters to be
     *
     * @param   string  $context  The context of the content
     *
     * @return  mixed  An object contains content type parameters, or null if not found
     *
     * @since   3.9.0
     *
     * @deprecated  5.0 Use the action log config model instead
     */
    public static function getLogContentTypeParams($context)
    {
        return Factory::getApplication()->bootComponent('actionlogs')->getMVCFactory()
            ->createModel('ActionlogConfig', 'Administrator')->getLogContentTypeParams($context);
    }

    /**
     * Get human readable log message for a User Action Log
     *
     * @param   \stdClass  $log            A User Action log message record
     * @param   boolean    $generateLinks  Flag to disable link generation when creating a message
     *
     * @return  string
     *
     * @since   3.9.0
     */
    public static function getHumanReadableLogMessage($log, $generateLinks = true)
    {
        static $links = array();

        $message     = Text::_($log->message_language_key);
        $messageData = json_decode($log->message, true);

        // Special handling for translation extension name
        if (isset($messageData['extension_name'])) {
            static::loadTranslationFiles($messageData['extension_name']);
            $messageData['extension_name'] = Text::_($messageData['extension_name']);
        }

        // Translating application
        if (isset($messageData['app'])) {
            $messageData['app'] = Text::_($messageData['app']);
        }

        // Translating type
        if (isset($messageData['type'])) {
            $messageData['type'] = Text::_($messageData['type']);
        }

        $linkMode = Factory::getApplication()->get('force_ssl', 0) >= 1 ? Route::TLS_FORCE : Route::TLS_IGNORE;

        foreach ($messageData as $key => $value) {
            // Escape any markup in the values to prevent XSS attacks
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

            // Convert relative url to absolute url so that it is clickable in action logs notification email
            if ($generateLinks && StringHelper::strpos($value, 'index.php?') === 0) {
                if (!isset($links[$value])) {
                    $links[$value] = Route::link('administrator', $value, false, $linkMode, true);
                }

                $value = $links[$value];
            }

            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Get link to an item of given content type
     *
     * @param   string     $component
     * @param   string     $contentType
     * @param   integer    $id
     * @param   string     $urlVar
     * @param   CMSObject  $object
     *
     * @return  string  Link to the content item
     *
     * @since   3.9.0
     */
    public static function getContentTypeLink($component, $contentType, $id, $urlVar = 'id', $object = null)
    {
        // Try to find the component helper.
        $eName = str_replace('com_', '', $component);
        $file  = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

        if (file_exists($file)) {
            $prefix = ucfirst(str_replace('com_', '', $component));
            $cName  = $prefix . 'Helper';

            \JLoader::register($cName, $file);

            if (class_exists($cName) && \is_callable(array($cName, 'getContentTypeLink'))) {
                return $cName::getContentTypeLink($contentType, $id, $object);
            }
        }

        if (empty($urlVar)) {
            $urlVar = 'id';
        }

        // Return default link to avoid having to implement getContentTypeLink in most of our components
        return 'index.php?option=' . $component . '&task=' . $contentType . '.edit&' . $urlVar . '=' . $id;
    }

    /**
     * Load both enabled and disabled actionlog plugins language file.
     *
     * It is used to make sure actions log is displayed properly instead of only language items displayed when a plugin is disabled.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public static function loadActionLogPluginsLanguage()
    {
        $lang = Factory::getLanguage();
        $db   = Factory::getDbo();

        // Get all (both enabled and disabled) actionlog plugins
        $query = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    array(
                        'folder',
                        'element',
                        'params',
                        'extension_id'
                    ),
                    array(
                        'type',
                        'name',
                        'params',
                        'id'
                    )
                )
            )
            ->from('#__extensions')
            ->where('type = ' . $db->quote('plugin'))
            ->where('folder = ' . $db->quote('actionlog'))
            ->where('state IN (0,1)')
            ->order('ordering');
        $db->setQuery($query);

        try {
            $rows = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $rows = array();
        }

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $name      = $row->name;
            $type      = $row->type;
            $extension = 'Plg_' . $type . '_' . $name;
            $extension = strtolower($extension);

            // If language already loaded, don't load it again.
            if ($lang->getPaths($extension)) {
                continue;
            }

            $lang->load($extension, JPATH_ADMINISTRATOR)
            || $lang->load($extension, JPATH_PLUGINS . '/' . $type . '/' . $name);
        }

        // Load plg_system_actionlogs too
        $lang->load('plg_system_actionlogs', JPATH_ADMINISTRATOR);

        // Load com_privacy too.
        $lang->load('com_privacy', JPATH_ADMINISTRATOR);
    }

    /**
     * Escapes potential characters that start a formula in a CSV value to prevent injection attacks
     *
     * @param   mixed  $value  csv field value
     *
     * @return  mixed
     *
     * @since   3.9.7
     */
    protected static function escapeCsvFormula($value)
    {
        if ($value == '') {
            return $value;
        }

        if (\in_array($value[0], self::$characters, true)) {
            $value = ' ' . $value;
        }

        return $value;
    }
}
