<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default factory for creating language objects
 *
 * @since  4.0.0
 */
class LanguageFactory implements LanguageFactoryInterface
{
    /**
     * Method to get an instance of a language.
     *
     * @param   string   $lang   The language to use
     * @param   boolean  $debug  The debug mode
     *
     * @return  Language
     *
     * @since   4.0.0
     */
    public function createLanguage($lang, $debug = false): Language
    {
        return new Language($lang, $debug);
    }
}
