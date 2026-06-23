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
 * @since  __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function setLanguageFactory(?LanguageFactoryInterface $languageFactory = null): void;
}
