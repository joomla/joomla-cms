<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory to load helper classes.
 *
 * @since  4.0.0
 */
interface HelperFactoryInterface
{
    /**
     * Returns a helper instance for the given name.
     *
     * @param   string  $name    The name
     * @param   array   $config  The config
     *
     * @return  \stdClass
     *
     * @since   4.0.0
     */
    public function getHelper(string $name, array $config = []);
}
