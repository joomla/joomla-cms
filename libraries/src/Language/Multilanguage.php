<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for multilang
 *
 * @since  2.5.4
 */
class Multilanguage
{
    /**
     * Flag indicating multilanguage functionality is enabled.
     *
     * @var    boolean
     * @since  4.0.0
     */
    public static $enabled = false;

    /**
     * Method to determine if the language filter plugin is enabled.
     * This works for both site and administrator.
     *
     * @param   ?CMSApplication     $app  The application
     * @param   ?DatabaseInterface  $db   The database
     *
     * @return  boolean  True if site is supporting multiple languages; false otherwise.
     *
     * @since   2.5.4
     */
    public static function isEnabled(CMSApplication $app = null, DatabaseInterface $db = null)
    {
        // Flag to avoid doing multiple database queries.
        static $tested = false;

        // Do not proceed with testing if the flag is true
        if (static::$enabled) {
            return true;
        }

        // Get application object.
        $app = $app ?: Factory::getApplication();

        // If being called from the frontend, we can avoid the database query.
        if ($app->isClient('site')) {
            static::$enabled = $app->getLanguageFilter();

            return static::$enabled;
        }

        // If already tested, don't test again.
        if (!$tested) {
            // Determine status of language filter plugin.
            $db    = $db ?: Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('enabled'))
                ->from($db->quoteName('#__extensions'))
                ->where(
                    [
                        $db->quoteName('type') . ' = ' . $db->quote('plugin'),
                        $db->quoteName('folder') . ' = ' . $db->quote('system'),
                        $db->quoteName('element') . ' = ' . $db->quote('languagefilter'),
                    ]
                );
            $db->setQuery($query);

            static::$enabled = (bool) $db->loadResult();
            $tested          = true;
        }

        return static::$enabled;
    }

    /**
     * Method to return a list of language home page menu items.
     *
     * @param   ?DatabaseInterface  $db  The database
     *
     * @return  array of menu objects.
     *
     * @since   3.5
     */
    public static function getSiteHomePages(DatabaseInterface $db = null)
    {
        // To avoid doing duplicate database queries.
        static $multilangSiteHomePages = null;

        if (!isset($multilangSiteHomePages)) {
            // Check for Home pages languages.
            $db    = $db ?: Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('language'),
                        $db->quoteName('id'),
                    ]
                )
                ->from($db->quoteName('#__menu'))
                ->where(
                    [
                        $db->quoteName('home') . ' = ' . $db->quote('1'),
                        $db->quoteName('published') . ' = 1',
                        $db->quoteName('client_id') . ' = 0',
                    ]
                );
            $db->setQuery($query);

            $multilangSiteHomePages = $db->loadObjectList('language');
        }

        return $multilangSiteHomePages;
    }
}
