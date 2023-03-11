<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multilang status helper.
 *
 * @since  1.7.1
 */
abstract class MultilangstatusHelper
{
    /**
     * Method to get the number of published home pages.
     *
     * @return  integer
     */
    public static function getHomes()
    {
        // Check for multiple Home pages.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__menu'))
            ->where(
                [
                    $db->quoteName('home') . ' = 1',
                    $db->quoteName('published') . ' = 1',
                    $db->quoteName('client_id') . ' = 0',
                ]
            );

        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to get the number of published language switcher modules.
     *
     * @return  integer
     */
    public static function getLangswitchers()
    {
        // Check if switcher is published.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__modules'))
            ->where(
                [
                    $db->quoteName('module') . ' = ' . $db->quote('mod_languages'),
                    $db->quoteName('published') . ' = 1',
                    $db->quoteName('client_id') . ' = 0',
                ]
            );

        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to return a list of published content languages.
     *
     * @return  array of language objects.
     */
    public static function getContentlangs()
    {
        // Check for published Content Languages.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('lang_code'),
                    $db->quoteName('published'),
                    $db->quoteName('sef'),
                ]
            )
            ->from($db->quoteName('#__languages'));

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Method to return combined language status.
     *
     * @return  array of language objects.
     */
    public static function getStatus()
    {
        // Check for combined status.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select all fields from the languages table.
        $query->select(
            [
                $db->quoteName('a') . '.*',
                $db->quoteName('a.published'),
                $db->quoteName('a.lang_code'),
                $db->quoteName('e.enabled'),
                $db->quoteName('e.element'),
                $db->quoteName('l.home'),
                $db->quoteName('l.published', 'home_published'),
            ]
        )
            ->from($db->quoteName('#__languages', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__menu', 'l'),
                $db->quoteName('l.language') . ' = ' . $db->quoteName('a.lang_code')
                    . ' AND ' . $db->quoteName('l.home') . ' = 1  AND ' . $db->quoteName('l.language') . ' <> ' . $db->quote('*')
            )
            ->join('LEFT', $db->quoteName('#__extensions', 'e'), $db->quoteName('e.element') . ' = ' . $db->quoteName('a.lang_code'))
            ->where(
                [
                    $db->quoteName('e.client_id') . ' = 0',
                    $db->quoteName('e.enabled') . ' = 1',
                    $db->quoteName('e.state') . ' = 0',
                ]
            );

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Method to return a list of contact objects.
     *
     * @return  array of contact objects.
     */
    public static function getContacts()
    {
        $db        = Factory::getDbo();
        $languages = count(LanguageHelper::getLanguages());

        // Get the number of contact with all as language
        $alang = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__contact_details', 'cd'))
            ->where(
                [
                    $db->quoteName('cd.user_id') . ' = ' . $db->quoteName('u.id'),
                    $db->quoteName('cd.published') . ' = 1',
                    $db->quoteName('cd.language') . ' = ' . $db->quote('*'),
                ]
            );

        // Get the number of languages for the contact
        $slang = $db->getQuery(true)
            ->select('COUNT(DISTINCT ' . $db->quoteName('l.lang_code') . ')')
            ->from($db->quoteName('#__languages', 'l'))
            ->join('LEFT', $db->quoteName('#__contact_details', 'cd'), $db->quoteName('cd.language') . ' = ' . $db->quoteName('l.lang_code'))
            ->where(
                [
                    $db->quoteName('cd.user_id') . ' = ' . $db->quoteName('u.id'),
                    $db->quoteName('cd.published') . ' = 1',
                    $db->quoteName('l.published') . ' = 1',
                ]
            );

        // Get the number of multiple contact/language
        $mlang = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__languages', 'l'))
            ->join('LEFT', $db->quoteName('#__contact_details', 'cd'), $db->quoteName('cd.language') . ' = ' . $db->quoteName('l.lang_code'))
            ->where(
                [
                    $db->quoteName('cd.user_id') . ' = ' . $db->quoteName('u.id'),
                    $db->quoteName('cd.published') . ' = 1',
                    $db->quoteName('l.published') . ' = 1',
                ]
            )
            ->group($db->quoteName('l.lang_code'))
            ->having('COUNT(*) > 1');

        // Get the contacts
        $subQuery = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__content', 'c'))
            ->where($db->quoteName('c.created_by') . ' = ' . $db->quoteName('u.id'));

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('u.name'),
                    '(' . $alang . ') AS ' . $db->quoteName('alang'),
                    '(' . $slang . ') AS ' . $db->quoteName('slang'),
                    '(' . $mlang . ') AS ' . $db->quoteName('mlang'),
                ]
            )
            ->from($db->quoteName('#__users', 'u'))
            ->join('LEFT', $db->quoteName('#__contact_details', 'cd'), $db->quoteName('cd.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where('EXISTS (' . $subQuery . ')')
            ->group(
                [
                    $db->quoteName('u.id'),
                    $db->quoteName('u.name'),
                ]
            );

        $db->setQuery($query);
        $warnings = $db->loadObjectList();

        foreach ($warnings as $index => $warn) {
            if ($warn->alang == 1 && $warn->slang == 0) {
                unset($warnings[$index]);
            }

            if ($warn->alang == 0 && $warn->slang == 0 && empty($warn->mlang)) {
                unset($warnings[$index]);
            }

            if ($warn->alang == 0 && $warn->slang == $languages && empty($warn->mlang)) {
                unset($warnings[$index]);
            }
        }

        return $warnings;
    }

    /**
     * Method to get the status of the module displaying the menutype of the default Home page set to All languages.
     *
     * @return  boolean True if the module is published, false otherwise.
     *
     * @since   3.7.0
     */
    public static function getDefaultHomeModule()
    {
        // Find Default Home menutype.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('menutype'))
            ->from($db->quoteName('#__menu'))
            ->where(
                [
                    $db->quoteName('home') . ' = 1',
                    $db->quoteName('published') . ' = 1',
                    $db->quoteName('client_id') . ' = 0',
                    $db->quoteName('language') . ' = ' . $db->quote('*'),
                ]
            );

        $db->setQuery($query);

        $menutype = $db->loadResult();

        // Get published site menu modules titles.
        $query->clear()
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__modules'))
            ->where(
                [
                    $db->quoteName('module') . ' = ' . $db->quote('mod_menu'),
                    $db->quoteName('published') . ' = 1',
                    $db->quoteName('client_id') . ' = 0',
                ]
            );

        $db->setQuery($query);

        $menutitles = $db->loadColumn();

        // Do we have a published menu module displaying the default Home menu item set to all languages?
        foreach ($menutitles as $menutitle) {
            $module       = self::getModule('mod_menu', $menutitle);
            $moduleParams = new Registry($module->params);
            $param        = $moduleParams->get('menutype', '');

            if ($param && $param != $menutype) {
                continue;
            }

            return true;
        }
    }

    /**
     * Get module by name
     *
     * @param   string  $moduleName     The name of the module
     * @param   string  $instanceTitle  The title of the module, optional
     *
     * @return  \stdClass  The Module object
     *
     * @since   3.7.0
     */
    public static function getModule($moduleName, $instanceTitle = null)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('title'),
                    $db->quoteName('module'),
                    $db->quoteName('position'),
                    $db->quoteName('content'),
                    $db->quoteName('showtitle'),
                    $db->quoteName('params'),
                ]
            )
            ->from($db->quoteName('#__modules'))
            ->where(
                [
                    $db->quoteName('module') . ' = :module',
                    $db->quoteName('published') . ' = 1',
                    $db->quoteName('client_id') . ' = 0',
                ]
            )
            ->bind(':module', $moduleName);

        if ($instanceTitle) {
            $query->where($db->quoteName('title') . ' = :title')
                ->bind(':title', $instanceTitle);
        }

        $db->setQuery($query);

        try {
            $modules = $db->loadObject();
        } catch (\RuntimeException $e) {
            Log::add(Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()), Log::WARNING, 'jerror');
        }

        return $modules;
    }
}
