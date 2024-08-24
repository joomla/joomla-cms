<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a CacheControllerFactoryInterface Aware Class.
 *
 * @since  4.2.0
 */
trait CacheControllerFactoryAwareTrait
{
    /**
     * CacheControllerFactoryInterface
     *
     * @var    CacheControllerFactoryInterface
     *
     * @since  4.2.0
     */
    private $cacheControllerFactory;

    /**
     * Get the CacheControllerFactoryInterface.
     *
     * @return  CacheControllerFactoryInterface
     *
     * @since   4.2.0
     */
    protected function getCacheControllerFactory(): CacheControllerFactoryInterface
    {
        if ($this->cacheControllerFactory) {
            return $this->cacheControllerFactory;
        }

        @trigger_error(
            sprintf('A cache controller is needed in %s. An UnexpectedValueException will be thrown in 5.0.', __CLASS__),
            E_USER_DEPRECATED
        );

        return Factory::getContainer()->get(CacheControllerFactoryInterface::class);
    }

    /**
     * Set the cache controller factory to use.
     *
     * @param   ?CacheControllerFactoryInterface  $cacheControllerFactory  The cache controller factory to use.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function setCacheControllerFactory(?CacheControllerFactoryInterface $cacheControllerFactory = null): void
    {
        $this->cacheControllerFactory = $cacheControllerFactory;
    }
}
