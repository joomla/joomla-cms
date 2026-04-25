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
 * Interface to be implemented by classes depending on an HTTP client factory.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HttpFactoryAwareInterface
{
    /**
     * Set the HTTP client factory to use.
     *
     * @param   ?HttpFactoryInterface  $httpFactory  The HTTP client factory to use.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setHttpFactory(?HttpFactoryInterface $httpFactory = null): void;
}
