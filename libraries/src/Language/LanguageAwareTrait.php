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
 * Defines the trait for a language aware class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait LanguageAwareTrait
{
    /**
     * Language
     *
     * @var    Language
     * @since  __DEPLOY_VERSION__
     */
    private $language;

    /**
     * Get the Language.
     *
     * @return  Language
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \UnexpectedValueException May be thrown if the language has not been set.
     */
    protected function getLanguage(): Language
    {
        if ($this->language) {
            return $this->language;
        }

        throw new \UnexpectedValueException('Language not set in ' . __CLASS__);
    }

    /**
     * Set the language to use.
     *
     * @param   Language  $language  The language to use
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }
}
