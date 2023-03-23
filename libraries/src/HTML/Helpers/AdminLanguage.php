<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class working with administrator language select lists
 *
 * @since  3.8.0
 */
abstract class AdminLanguage
{
    /**
     * Cached array of the administrator language items.
     *
     * @var    array
     * @since  3.8.0
     */
    protected static $items = null;

    /**
     * Get a list of the available administrator language items.
     *
     * @param   boolean  $all        True to include All (*)
     * @param   boolean  $translate  True to translate All
     *
     * @return  array
     *
     * @since   3.8.0
     */
    public static function existing($all = false, $translate = false)
    {
        if (empty(static::$items)) {
            $languages       = [];
            $admin_languages = LanguageHelper::getKnownLanguages(JPATH_ADMINISTRATOR);

            foreach ($admin_languages as $tag => $language) {
                $languages[$tag] = $language['nativeName'];
            }

            ksort($languages);

            static::$items = $languages;
        }

        if ($all) {
            $all_option = [new CMSObject(['value' => '*', 'text' => $translate ? Text::alt('JALL', 'language') : 'JALL_LANGUAGE'])];

            return array_merge($all_option, static::$items);
        } else {
            return static::$items;
        }
    }
}
