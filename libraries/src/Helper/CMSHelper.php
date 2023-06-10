<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\TableInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base Helper class.
 *
 * @since  3.2
 */
class CMSHelper
{
    /**
     * Gets the current language
     *
     * @param   boolean  $detectBrowser  Flag indicating whether to use the browser language as a fallback.
     *
     * @return  string  The language string
     *
     * @since   3.2
     */
    public function getCurrentLanguage($detectBrowser = true)
    {
        $app      = Factory::getApplication();
        $langCode = null;

        // Get the languagefilter parameters
        if (Multilanguage::isEnabled()) {
            $plugin       = PluginHelper::getPlugin('system', 'languagefilter');
            $pluginParams = new Registry($plugin->params);

            if ((int) $pluginParams->get('lang_cookie', 1) === 1) {
                $langCode = $app->getInput()->cookie->getString(ApplicationHelper::getHash('language'));
            } else {
                $langCode = $app->getSession()->get('plg_system_languagefilter.language');
            }
        }

        // No cookie - let's try to detect browser language or use site default
        if (!$langCode) {
            if ($detectBrowser) {
                $langCode = LanguageHelper::detectLanguage();
            } else {
                $langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
            }
        }

        return $langCode;
    }

    /**
     * Gets the associated language ID
     *
     * @param   string  $langCode  The language code to look up
     *
     * @return  integer  The language ID
     *
     * @since   3.2
     */
    public function getLanguageId($langCode)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('lang_id'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('lang_code') . ' = :language')
            ->bind(':language', $langCode);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Gets a row of data from a table
     *
     * @param   TableInterface  $table  Table instance for a row.
     *
     * @return  array  Associative array of all columns and values for a row in a table.
     *
     * @since   3.2
     */
    public function getRowData(TableInterface $table)
    {
        $fields = $table->getFields();
        $data   = [];

        foreach ($fields as &$field) {
            $columnName        = $field->Field;
            $value             = $table->$columnName;
            $data[$columnName] = $value;
        }

        return $data;
    }

    /**
     * Method to get an object containing all of the table columns and values.
     *
     * @param   TableInterface  $table  Table object.
     *
     * @return  \stdClass  Contains all of the columns and values.
     *
     * @since   3.2
     */
    public function getDataObject(TableInterface $table)
    {
        $fields     = $table->getFields();
        $dataObject = new \stdClass();

        foreach ($fields as $field) {
            $fieldName              = $field->Field;
            $dataObject->$fieldName = $table->$fieldName ?? null;
        }

        return $dataObject;
    }
}
