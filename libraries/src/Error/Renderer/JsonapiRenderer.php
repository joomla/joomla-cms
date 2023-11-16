<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Error\Renderer;

use Joomla\Application\WebApplicationInterface;
use Joomla\CMS\Error\JsonApi\AuthenticationFailedExceptionHandler;
use Joomla\CMS\Error\JsonApi\CheckinCheckoutExceptionHandler;
use Joomla\CMS\Error\JsonApi\InvalidParameterExceptionHandler;
use Joomla\CMS\Error\JsonApi\InvalidRouteExceptionHandler;
use Joomla\CMS\Error\JsonApi\NotAcceptableExceptionHandler;
use Joomla\CMS\Error\JsonApi\NotAllowedExceptionHandler;
use Joomla\CMS\Error\JsonApi\ResourceNotFoundExceptionHandler;
use Joomla\CMS\Error\JsonApi\SaveExceptionHandler;
use Joomla\CMS\Error\JsonApi\SendEmailExceptionHandler;
use Joomla\CMS\Factory;
use Tobscure\JsonApi\ErrorHandler;
use Tobscure\JsonApi\Exception\Handler\FallbackExceptionHandler;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JSON error page renderer
 *
 * @since  4.0.0
 */
class JsonapiRenderer extends JsonRenderer
{
    /**
     * The format (type) of the error page
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'jsonapi';

    /**
     * Render the error page for the given object
     *
     * @param   \Throwable  $error  The error object to be rendered
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function render(\Throwable $error): string
    {
        if ($error instanceof \Exception) {
            $errors = new ErrorHandler();

            $errors->registerHandler(new InvalidRouteExceptionHandler());
            $errors->registerHandler(new AuthenticationFailedExceptionHandler());
            $errors->registerHandler(new NotAcceptableExceptionHandler());
            $errors->registerHandler(new NotAllowedExceptionHandler());
            $errors->registerHandler(new InvalidParameterExceptionHandler());
            $errors->registerHandler(new ResourceNotFoundExceptionHandler());
            $errors->registerHandler(new SaveExceptionHandler());
            $errors->registerHandler(new CheckinCheckoutExceptionHandler());
            $errors->registerHandler(new SendEmailExceptionHandler());
            $errors->registerHandler(new FallbackExceptionHandler(JDEBUG));

            $response = $errors->handle($error);
        } else {
            $code      = 500;
            $errorInfo = ['code' => $code, 'title' => 'Internal server error'];

            if (JDEBUG) {
                $errorInfo['detail'] = (string) $error;
            }

            $response = new ResponseBag($code, $errorInfo);
        }

        $this->getDocument()->setErrors($response->getErrors());
        $app = Factory::getApplication();

        if ($app instanceof WebApplicationInterface) {
            $app->setHeader('status', $response->getStatus());
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $this->getDocument()->render();
    }
}
