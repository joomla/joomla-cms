<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for an HttpFactoryInterface aware class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait HttpFactoryAwareTrait
{
    /**
     * HttpFactoryInterface
     *
     * @var    HttpFactoryInterface
     * @since  __DEPLOY_VERSION__
     */
    private $httpFactory;

    /**
     * Get the HttpFactoryInterface.
     *
     * @return  HttpFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \UnexpectedValueException May be thrown if the HttpFactory has not been set.
     */
    protected function getHttpFactory(): HttpFactoryInterface
    {
        if ($this->httpFactory) {
            return $this->httpFactory;
        }

        throw new \UnexpectedValueException('HttpFactory not set in ' . __CLASS__);
    }

    /**
     * Set the HTTP client factory to use.
     *
     * @param   ?HttpFactoryInterface  $httpFactory  The HTTP client factory to use.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setHttpFactory(?HttpFactoryInterface $httpFactory = null): void
    {
        $this->httpFactory = $httpFactory;
    }
}
