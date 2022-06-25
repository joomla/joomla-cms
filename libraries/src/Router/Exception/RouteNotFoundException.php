<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router\Exception;

/**
 * Exception class defining an error for a missing route
 *
 * @since  3.8.0
 */
class RouteNotFoundException extends \InvalidArgumentException
{
    /**
     * Constructor
     *
     * @param   string      $message   The Exception message to throw.
     * @param   integer     $code      The Exception code.
     * @param   \Exception  $previous  The previous exception used for the exception chaining.
     *
     * @since   3.8.0
     */
    public function __construct($message = '', $code = 404, \Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'URL was not found';
        }

        parent::__construct($message, $code, $previous);
    }
}
