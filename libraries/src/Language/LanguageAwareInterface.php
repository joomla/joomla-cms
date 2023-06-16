<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a language.
 *
 * @since  __DEPLOY_VERSION__
 */
interface LanguageAwareInterface
{
    /**
     * Set the language to use.
     *
     * @param   Language  $language  The language to use
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setLanguage(Language $language): void;
}
