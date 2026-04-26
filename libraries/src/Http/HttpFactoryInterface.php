<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

use Joomla\Http\Http;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface defining a factory which can create Http client objects.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HttpFactoryInterface
{
    /**
     * Method to get an instance of a Http client.
     *
     * @param   array|\ArrayAccess  $options   Client options array.
     * @param   array|string|null  $adapters  Adapter (string) or queue of adapters (array) to use for communication.
     *
     * @return  Http
     *
     * @since   __DEPLOY_VERSION__
     */
    public function createHttp(array|\ArrayAccess $options = [], array|string|null $adapters = null): Http;
}
