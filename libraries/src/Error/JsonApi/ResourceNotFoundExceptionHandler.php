<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Error\JsonApi;

use Exception;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use Tobscure\JsonApi\Exception\Handler\ExceptionHandlerInterface;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handler for invalid resource requests that should give a 404
 *
 * @since  4.0.0
 */
class ResourceNotFoundExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * If the exception handler is able to format a response for the provided exception,
     * then the implementation should return true.
     *
     * @param   \Exception  $e  The exception to be handled
     *
     * @return boolean
     *
     * @since  4.0.0
     */
    public function manages(Exception $e)
    {
        return $e instanceof ResourceNotFound;
    }

    /**
     * Handle the provided exception.
     *
     * @param   Exception  $e  The exception being handled
     *
     * @return  \Tobscure\JsonApi\Exception\Handler\ResponseBag
     *
     * @since  4.0.0
     */
    public function handle(Exception $e)
    {
        $status = 404;
        $error  = ['title' => 'Resource not found'];

        $code = $e->getCode();

        if ($code) {
            $error['code'] = $code;
        }

        return new ResponseBag($status, [$error]);
    }
}
