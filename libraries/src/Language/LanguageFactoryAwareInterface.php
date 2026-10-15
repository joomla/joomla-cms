<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a language factory.
 *
 * @since  6.2.0
 */
interface LanguageFactoryAwareInterface
{
    /**
     * Set the language factory to use.
     *
     * @param   ?LanguageFactoryInterface  $languageFactory  The language factory to use.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function setLanguageFactory(?LanguageFactoryInterface $languageFactory = null): void;
}
