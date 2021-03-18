<?php

/*
 * This file is part of JSON-API.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tobscure\JsonApi\Exception\Handler;

use Exception;
use Tobscure\JsonApi\Exception\InvalidParameterException;

class InvalidParameterExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function manages(Exception $e)
    {
        return $e instanceof InvalidParameterException;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $e)
    {
        $status = 400;
        $error = [];

        $code = $e->getCode();
        if ($code) {
            $error['code'] = $code;
        }

        $invalidParameter = $e->getInvalidParameter();
        if ($invalidParameter) {
            $error['source'] = ['parameter' => $invalidParameter];
        }

        return new ResponseBag($status, [$error]);
    }
}
