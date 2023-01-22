<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Modules component helper.
 *
 * @since  1.6
 */
abstract class ModulesHelper
{
    /**
     * Get a list of filter options for the state of a module.
     *
     * @return  array  An array of \JHtmlOption elements.
     */
    public static function getStateOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JPUBLISHED'));
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JUNPUBLISHED'));
        $options[] = HTMLHelper::_('select.option', '-2', Text::_('JTRASHED'));
        $options[] = HTMLHelper::_('select.option', '*', Text::_('JALL'));

        return $options;
    }

    /**
     * Get a list of filter options for the application clients.
     *
     * @return  array  An array of \JHtmlOption elements.
     */
    public static function getClientOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JSITE'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JADMINISTRATOR'));

        return $options;
    }

    /**
     * Get a list of modules positions
     *
     * @param   integer  $clientId       Client ID
     * @param   boolean  $editPositions  Allow to edit the positions
     *
     * @return  array  A list of positions
     */
    public static function getPositions($clientId, $editPositions = false)
    {
        $db       = Factory::getDbo();
        $clientId = (int) $clientId;
        $query    = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('position'))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('client_id') . ' = :clientid')
            ->order($db->quoteName('position'))
            ->bind(':clientid', $clientId, ParameterType::INTEGER);

        $db->setQuery($query);

        try {
            $positions = $db->loadColumn();
            $positions = is_array($positions) ? $positions : [];
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        // Build the list
        $options = [];

        foreach ($positions as $position) {
            if (!$position && !$editPositions) {
                $options[] = HTMLHelper::_('select.option', 'none', Text::_('COM_MODULES_NONE'));
            } elseif (!$position) {
                $options[] = HTMLHelper::_('select.option', '', Text::_('COM_MODULES_NONE'));
            } else {
                $options[] = HTMLHelper::_('select.option', $position, $position);
            }
        }

        return $options;
    }

    /**
     * Return a list of templates
     *
     * @param   integer  $clientId  Client ID
     * @param   string   $state     State
     * @param   string   $template  Template name
     *
     * @return  array  List of templates
     */
    public static function getTemplates($clientId = 0, $state = '', $template = '')
    {
        $db       = Factory::getDbo();
        $clientId = (int) $clientId;

        // Get the database object and a new query object.
        $query = $db->getQuery(true);

        // Build the query.
        $query->select($db->quoteName(['element', 'name', 'enabled']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('client_id') . ' = :clientid')
            ->where($db->quoteName('type') . ' = ' . $db->quote('template'));

        if ($state != '') {
            $query->where($db->quoteName('enabled') . ' = :state')
                ->bind(':state', $state);
        }

        if ($template != '') {
            $query->where($db->quoteName('element') . ' = :element')
                ->bind(':element', $template);
        }

        $query->bind(':clientid', $clientId, ParameterType::INTEGER);

        // Set the query and load the templates.
        $db->setQuery($query);
        $templates = $db->loadObjectList('element');

        return $templates;
    }

    /**
     * Get a list of the unique modules installed in the client application.
     *
     * @param   int  $clientId  The client id.
     *
     * @return  array  Array of unique modules
     */
    public static function getModules($clientId)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('element AS value, name AS text')
            ->from('#__extensions as e')
            ->where('e.client_id = ' . (int) $clientId)
            ->where('type = ' . $db->quote('module'))
            ->join('LEFT', '#__modules as m ON m.module=e.element AND m.client_id=e.client_id')
            ->where('m.module IS NOT NULL')
            ->group('element,name');

        $db->setQuery($query);
        $modules = $db->loadObjectList();
        $lang = Factory::getLanguage();

        foreach ($modules as $i => $module) {
            $extension = $module->value;
            $path = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
            $source = $path . "/modules/$extension";
                $lang->load("$extension.sys", $path)
            ||  $lang->load("$extension.sys", $source);
            $modules[$i]->text = Text::_($module->text);
        }

        $modules = ArrayHelper::sortObjects($modules, 'text', 1, true, true);

        return $modules;
    }

    /**
     * Get a list of the assignment options for modules to menus.
     *
     * @param   int  $clientId  The client id.
     *
     * @return  array
     */
    public static function getAssignmentOptions($clientId)
    {
        $options = [];
        $options[] = HTMLHelper::_('select.option', '0', 'COM_MODULES_OPTION_MENU_ALL');
        $options[] = HTMLHelper::_('select.option', '-', 'COM_MODULES_OPTION_MENU_NONE');

        if ($clientId == 0) {
            $options[] = HTMLHelper::_('select.option', '1', 'COM_MODULES_OPTION_MENU_INCLUDE');
            $options[] = HTMLHelper::_('select.option', '-1', 'COM_MODULES_OPTION_MENU_EXCLUDE');
        }

        return $options;
    }

    /**
     * Return a translated module position name
     *
     * @param   integer  $clientId  Application client id 0: site | 1: admin
     * @param   string   $template  Template name
     * @param   string   $position  Position name
     *
     * @return  string  Return a translated position name
     *
     * @since   3.0
     */
    public static function getTranslatedModulePosition($clientId, $template, $position)
    {
        // Template translation
        $lang = Factory::getLanguage();
        $path = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;

        $loaded = $lang->getPaths('tpl_' . $template . '.sys');

        // Only load the template's language file if it hasn't been already
        if (!$loaded) {
            $lang->load('tpl_' . $template . '.sys', $path, null, false, false)
            ||  $lang->load('tpl_' . $template . '.sys', $path . '/templates/' . $template, null, false, false)
            ||  $lang->load('tpl_' . $template . '.sys', $path, $lang->getDefault(), false, false)
            ||  $lang->load('tpl_' . $template . '.sys', $path . '/templates/' . $template, $lang->getDefault(), false, false);
        }

        $langKey = strtoupper('TPL_' . $template . '_POSITION_' . $position);
        $text = Text::_($langKey);

        // Avoid untranslated strings
        if (!self::isTranslatedText($langKey, $text)) {
            // Modules component translation
            $langKey = strtoupper('COM_MODULES_POSITION_' . $position);
            $text = Text::_($langKey);

            // Avoid untranslated strings
            if (!self::isTranslatedText($langKey, $text)) {
                // Try to humanize the position name
                $text = ucfirst(preg_replace('/^' . $template . '\-/', '', $position));
                $text = ucwords(str_replace(['-', '_'], ' ', $text));
            }
        }

        return $text;
    }

    /**
     * Check if the string was translated
     *
     * @param   string  $langKey  Language file text key
     * @param   string  $text     The "translated" text to be checked
     *
     * @return  boolean  Return true for translated text
     *
     * @since   3.0
     */
    public static function isTranslatedText($langKey, $text)
    {
        return $text !== $langKey;
    }

    /**
     * Create and return a new Option
     *
     * @param   string  $value  The option value [optional]
     * @param   string  $text   The option text [optional]
     *
     * @return  object  The option as an object (\stdClass instance)
     *
     * @since   3.0
     */
    public static function createOption($value = '', $text = '')
    {
        if (empty($text)) {
            $text = $value;
        }

        $option = new \stdClass();
        $option->value = $value;
        $option->text  = $text;

        return $option;
    }

    /**
     * Create and return a new Option Group
     *
     * @param   string  $label    Value and label for group [optional]
     * @param   array   $options  Array of options to insert into group [optional]
     *
     * @return  array  Return the new group as an array
     *
     * @since   3.0
     */
    public static function createOptionGroup($label = '', $options = [])
    {
        $group = [];
        $group['value'] = $label;
        $group['text']  = $label;
        $group['items'] = $options;

        return $group;
    }
}
