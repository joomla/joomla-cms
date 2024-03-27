<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http\Transport;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Http\TransportInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Http\AbstractTransport;
use Joomla\Http\Exception\InvalidResponseCodeException;
use Joomla\Uri\UriInterface;
use Laminas\Diactoros\Stream as StreamResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTTP transport class for using sockets directly.
 *
 * @since  1.7.3
 */
class SocketTransport extends AbstractTransport implements TransportInterface
{
    /**
     * @var    array  Reusable socket connections.
     * @since  1.7.3
     */
    protected $connections;

    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param   string        $method     The HTTP method for sending the request.
     * @param   UriInterface  $uri        The URI to the resource to request.
     * @param   mixed         $data       Either an associative array or a string to be sent with the request.
     * @param   array         $headers    An array of request headers to send with the request.
     * @param   integer       $timeout    Read timeout in seconds.
     * @param   string        $userAgent  The optional user agent string to send with the request.
     *
     * @return  Response
     *
     * @since   1.7.3
     * @throws  \RuntimeException
     */
    public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
    {
        $connection = $this->connect($uri, $timeout);

        // Make sure the connection is alive and valid.
        if (\is_resource($connection)) {
            // Make sure the connection has not timed out.
            $meta = stream_get_meta_data($connection);

            if ($meta['timed_out']) {
                throw new \RuntimeException('Server connection timed out.');
            }
        } else {
            throw new \RuntimeException('Not connected to server.');
        }

        // Get the request path from the URI object.
        $path = $uri->toString(['path', 'query']);

        // If we have data to send make sure our request is setup for it.
        if (!empty($data)) {
            // If the data is not a scalar value encode it to be sent with the request.
            if (!is_scalar($data)) {
                $data = http_build_query($data);
            }

            if (!isset($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
            }

            // Add the relevant headers.
            $headers['Content-Length'] = \strlen($data);
        }

        // Build the request payload.
        $request   = [];
        $request[] = strtoupper($method) . ' ' . ((empty($path)) ? '/' : $path) . ' HTTP/1.0';
        $request[] = 'Host: ' . $uri->getHost();

        // If an explicit user agent is given use it.
        if (isset($userAgent)) {
            $headers['User-Agent'] = $userAgent;
        }

        // If there are custom headers to send add them to the request payload.
        if (\is_array($headers)) {
            foreach ($headers as $k => $v) {
                $request[] = $k . ': ' . $v;
            }
        }

        // Set any custom transport options
        foreach ($this->getOption('transport.socket', []) as $value) {
            $request[] = $value;
        }

        // If we have data to send add it to the request payload.
        if (!empty($data)) {
            $request[] = null;
            $request[] = $data;
        }

        // Authentication, if needed
        if ($this->getOption('userauth') && $this->getOption('passwordauth')) {
            $request[] = 'Authorization: Basic ' . base64_encode($this->getOption('userauth') . ':' . $this->getOption('passwordauth'));
        }

        // Send the request to the server.
        fwrite($connection, implode("\r\n", $request) . "\r\n\r\n");

        // Get the response data from the server.
        $content = '';

        while (!feof($connection)) {
            $content .= fgets($connection, 4096);
        }

        $content = $this->getResponse($content);

        // Follow Http redirects
        if ($content->code >= 301 && $content->code < 400 && isset($content->headers['Location'])) {
            return $this->request($method, new Uri($content->headers['Location']), $data, $headers, $timeout, $userAgent);
        }

        return $content;
    }

    /**
     * Method to get a response object from a server response.
     *
     * @param   string  $content  The complete server response, including headers.
     *
     * @return  Response
     *
     * @since   1.7.3
     * @throws  InvalidResponseCodeException
     */
    protected function getResponse($content)
    {
        if (empty($content)) {
            throw new \UnexpectedValueException('No content in response.');
        }

        // Split the response into headers and body.
        $response = explode("\r\n\r\n", $content, 2);

        // Get the response headers as an array.
        $headers = explode("\r\n", $response[0]);

        // Set the body for the response.
        $body = empty($response[1]) ? '' : $response[1];

        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);
        $code = $matches[0];

        if (!is_numeric($code)) {
            // No valid response code was detected.
            throw new InvalidResponseCodeException('No HTTP response code found.');
        }

        $statusCode      = (int) $code;
        $verifiedHeaders = $this->processHeaders($headers);

        $streamInterface = new StreamResponse('php://memory', 'rw');
        $streamInterface->write($body);

        return new Response($streamInterface, $statusCode, $verifiedHeaders);
    }

    /**
     * Method to connect to a server and get the resource.
     *
     * @param   UriInterface  $uri      The URI to connect with.
     * @param   integer       $timeout  Read timeout in seconds.
     *
     * @return  resource  Socket connection resource.
     *
     * @since   1.7.3
     * @throws  \RuntimeException
     */
    protected function connect(UriInterface $uri, $timeout = null)
    {
        $errno = null;
        $err   = null;

        // Get the host from the uri.
        $host = ($uri->isSsl()) ? 'ssl://' . $uri->getHost() : $uri->getHost();

        // If the port is not explicitly set in the URI detect it.
        if (!$uri->getPort()) {
            $port = ($uri->getScheme() === 'https') ? 443 : 80;
        } else {
            // Use the set port.
            $port = $uri->getPort();
        }

        // Build the connection key for resource memory caching.
        $key = md5($host . $port);

        // If the connection already exists, use it.
        if (!empty($this->connections[$key]) && \is_resource($this->connections[$key])) {
            // Connection reached EOF, cannot be used anymore
            $meta = stream_get_meta_data($this->connections[$key]);

            if ($meta['eof']) {
                if (!fclose($this->connections[$key])) {
                    throw new \RuntimeException('Cannot close connection');
                }
            } elseif (!$meta['timed_out']) {
                // Make sure the connection has not timed out.
                return $this->connections[$key];
            }
        }

        if (!is_numeric($timeout)) {
            $timeout = ini_get('default_socket_timeout');
        }

        // Capture PHP errors
        $php_errormsg = '';
        $track_errors = ini_get('track_errors');
        ini_set('track_errors', true);

        // PHP sends a warning if the uri does not exists; we silence it and throw an exception instead.
        // Attempt to connect to the server
        $connection = @fsockopen($host, $port, $errno, $err, $timeout);

        if (!$connection) {
            if (!$php_errormsg) {
                // Error but nothing from php? Create our own
                $php_errormsg = sprintf('Could not connect to resource %s: %s (error code %d)', $uri, $err, $errno);
            }

            // Restore error tracking to give control to the exception handler
            ini_set('track_errors', $track_errors);

            throw new \RuntimeException($php_errormsg);
        }

        // Restore error tracking to what it was before.
        ini_set('track_errors', $track_errors);

        // Since the connection was successful let's store it in case we need to use it later.
        $this->connections[$key] = $connection;

        // If an explicit timeout is set, set it.
        if (isset($timeout)) {
            stream_set_timeout($this->connections[$key], (int) $timeout);
        }

        return $this->connections[$key];
    }

    /**
     * Method to check if http transport socket available for use
     *
     * @return  boolean   True if available else false
     *
     * @since   3.0.0
     */
    public static function isSupported()
    {
        return \function_exists('fsockopen') && \is_callable('fsockopen') && !Factory::getApplication()->get('proxy_enable');
    }
}
