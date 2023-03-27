<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface class defining the necessary methods for an authentication plugin to be provider aware
 * Please note: might be deprecated with Joomla 4.2
 *
 * @since  3.10.7
 */
interface ProviderAwareAuthenticationPluginInterface
{
    /**
     * Return if plugin acts as primary provider
     *
     * @return  true
     *
     * @since  3.10.7
     */
    public static function isPrimaryProvider();

    /**
     * Return provider name
     *
     * @return string
     *
     * @since  3.10.7
     */
    public static function getProviderName();
}
