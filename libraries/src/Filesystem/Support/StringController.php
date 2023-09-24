<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem\Support;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * String Controller
 *
 * @since  1.7.0
 * @deprecated  4.4 will be removed in 6.0
 *              Use Joomla\Filesystem\Support\StringController instead.
 */
class StringController
{
    /**
     * Defines a variable as an array
     *
     * @return  array
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Support\StringController::getArray() instead.
     */
    public function _getArray()
    {
        static $strings = [];

        return $strings;
    }

    /**
     * Create a reference
     *
     * @param   string  $reference  The key
     * @param   string  $string     The value
     *
     * @return  void
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Support\StringController::createRef() instead.
     */
    public function createRef($reference, &$string)
    {
        $ref             =& self::_getArray();
        $ref[$reference] =& $string;
    }

    /**
     * Get reference
     *
     * @param   string  $reference  The key for the reference.
     *
     * @return  mixed  False if not set, reference if it exists
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Support\StringController::getRef() instead.
     */
    public function getRef($reference)
    {
        $ref =& self::_getArray();

        return $ref[$reference] ?? false;
    }
}
