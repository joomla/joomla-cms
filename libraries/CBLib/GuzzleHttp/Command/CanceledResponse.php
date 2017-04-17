<?php
namespace GuzzleHttp\Command;

use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\StreamInterface;

/**
 * Represented a canceled response that is used when a request's error event
 * is intercepted before a response is received.
 *
 * Other than the setEffectiveUrl() method, this object is immutable.
 */
class CanceledResponse implements ResponseInterface
{
    const MESSAGE = 'The response was canceled';

    private $effectiveUrl;

    public function __toString()
    {
        return '';
    }

    public function getProtocolVersion()
    {
        return null;
    }

    public function getBody()
    {
        return null;
    }

    public function getHeaders()
    {
        return array();
    }

    public function getHeader($header, $asArray = false)
    {
        return $asArray ? array() : '';
    }

    public function hasHeader($header)
    {
        return false;
    }

    public function getStatusCode()
    {
        return '000';
    }

    public function getReasonPhrase()
    {
        return 'CANCELED';
    }

    public function getEffectiveUrl()
    {
        return $this->effectiveUrl;
    }

    public function setEffectiveUrl($url)
    {
        $this->effectiveUrl = $url;
    }

    public function setBody(StreamInterface $body = null)
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function removeHeader($header)
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function addHeader($header, $value)
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function addHeaders(array $headers)
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function setHeader($header, $value)
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function setHeaders(array $headers)
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function json(array $config = array())
    {
        throw new \RuntimeException(self::MESSAGE);
    }

    public function xml(array $config = array())
    {
        throw new \RuntimeException(self::MESSAGE);
    }
}
