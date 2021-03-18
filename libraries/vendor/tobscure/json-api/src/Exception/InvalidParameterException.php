<?php

/*
 * This file is part of JSON-API.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tobscure\JsonApi\Exception;

use Exception;

class InvalidParameterException extends Exception
{
    /**
     * @var string The parameter that caused this exception.
     */
    private $invalidParameter;

    /**
     * {@inheritdoc}
     *
     * @param string $invalidParameter The parameter that caused this exception.
     */
    public function __construct($message = '', $code = 0, $previous = null, $invalidParameter = '')
    {
        parent::__construct($message, $code, $previous);

        $this->invalidParameter = $invalidParameter;
    }

    /**
     * @return string The parameter that caused this exception.
     */
    public function getInvalidParameter()
    {
        return $this->invalidParameter;
    }
}
