<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a LanguageFactoryInterface aware class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait LanguageFactoryAwareTrait
{
    /**
     * LanguageFactoryInterface
     *
     * @var    LanguageFactoryInterface
     * @since  __DEPLOY_VERSION__
     */
    private $languageFactory;

    /**
     * Get the LanguageFactoryInterface.
     *
     * @return  LanguageFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \UnexpectedValueException May be thrown if the LanguageFactory has not been set.
     */
    protected function getLanguageFactory(): LanguageFactoryInterface
    {
        if ($this->languageFactory) {
            return $this->languageFactory;
        }

        @trigger_error(
            \sprintf('LanguageFactory must be set in %s. This will not be caught anymore in 8.0', __METHOD__),
            E_USER_DEPRECATED
        );

        return Factory::getContainer()->get(LanguageFactoryInterface::class);
    }

    /**
     * Set the language factory to use.
     *
     * @param   ?LanguageFactoryInterface  $languageFactory  The language factory to use.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setLanguageFactory(?LanguageFactoryInterface $languageFactory = null): void
    {
        $this->languageFactory = $languageFactory;
    }
}
