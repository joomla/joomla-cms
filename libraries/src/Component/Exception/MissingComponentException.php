<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Exception;

use Joomla\CMS\Router\Exception\RouteNotFoundException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining an error for a missing component
 *
 * @since  3.7.0
 */
class MissingComponentException extends RouteNotFoundException
{
    /**
     * Constructor
     *
     * @param   string      $message   The Exception message to throw.
     * @param   integer     $code      The Exception code.
     * @param   \Exception  $previous  The previous exception used for the exception chaining.
     *
     * @since   3.7.0
     */
    public function __construct($message = '', $code = 404, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
