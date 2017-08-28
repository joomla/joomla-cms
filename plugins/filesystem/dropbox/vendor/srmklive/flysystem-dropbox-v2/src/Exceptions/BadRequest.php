<?php

namespace Srmklive\Dropbox\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

class BadRequest extends Exception
{
    public function __construct(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);
        parent::__construct($body['error_summary']);
    }
}
