<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Transport;

use Composer\CaBundle\CaBundle;
use Joomla\Http\AbstractTransport;
use Joomla\Http\Exception\InvalidResponseCodeException;
use Joomla\Http\Response;
use Joomla\Uri\Uri;
use Joomla\Uri\UriInterface;
use Zend\Diactoros\Stream as StreamResponse;

/**
 * HTTP transport class for using PHP streams.
 *
 * @since  1.0
 */
class Stream extends AbstractTransport
{
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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
	{
		// Create the stream context options array with the required method offset.
		$options = ['method' => strtoupper($method)];

		// If data exists let's encode it and make sure our Content-Type header is set.
		if (isset($data))
		{
			// If the data is a scalar value simply add it to the stream context options.
			if (is_scalar($data))
			{
				$options['content'] = $data;
			}
			else
			// Otherwise we need to encode the value first.
			{
				$options['content'] = http_build_query($data);
			}

			if (!isset($headers['Content-Type']))
			{
				$headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
			}

			// Add the relevant headers.
			$headers['Content-Length'] = strlen($options['content']);
		}

		// If an explicit timeout is given user it.
		if (isset($timeout))
		{
			$options['timeout'] = (int) $timeout;
		}

		// If an explicit user agent is given use it.
		if (isset($userAgent))
		{
			$options['user_agent'] = $userAgent;
		}

		// Ignore HTTP errors so that we can capture them.
		$options['ignore_errors'] = 1;

		// Follow redirects.
		$options['follow_location'] = (int) $this->getOption('follow_location', 1);

		// Add the proxy configuration if enabled
		if ($this->getOption('proxy.enabled', false))
		{
			$options['request_fulluri'] = true;

			if ($this->getOption('proxy.host') && $this->getOption('proxy.port'))
			{
				$options['proxy'] = $this->getOption('proxy.host') . ':' . (int) $this->getOption('proxy.port');
			}

			// If authentication details are provided, add those as well
			if ($this->getOption('proxy.port') && $this->getOption('proxy.password'))
			{
				$headers['Proxy-Authorization'] = 'Basic ' . base64_encode($this->getOption('proxy.user') . ':' . $this->getOption('proxy.password'));
			}
		}

		// Build the headers string for the request.
		if (!empty($headers))
		{
			$headerString = '';

			foreach ($headers as $key => $value)
			{
				if (is_array($value))
				{
					foreach ($value as $header)
					{
						$headerString .= "$key: $header\r\n";
					}
				}
				else
				{
					$headerString .= "$key: $value\r\n";
				}
			}

			// Add the headers string into the stream context options array.
			$options['header'] = trim($headerString, "\r\n");
		}

		// Authentication, if needed
		if ($uri instanceof Uri && $this->getOption('userauth') && $this->getOption('passwordauth'))
		{
			$uri->setUser($this->getOption('userauth'));
			$uri->setPass($this->getOption('passwordauth'));
		}

		// Set any custom transport options
		foreach ($this->getOption('transport.stream', []) as $key => $value)
		{
			$options[$key] = $value;
		}

		// Get the current context options.
		$contextOptions = stream_context_get_options(stream_context_get_default());

		// Add our options to the currently defined options, if any.
		$contextOptions['http'] = isset($contextOptions['http']) ? array_merge($contextOptions['http'], $options) : $options;

		// Create the stream context for the request.
		$streamOptions = [
			'http' => $options,
			'ssl'  => [
				'verify_peer'      => true,
				'verify_depth'     => 5,
				'verify_peer_name' => true,
			],
		];

		// The cacert may be a file or path
		$certpath = $this->getOption('stream.certpath', CaBundle::getSystemCaRootBundlePath());

		if (is_dir($certpath))
		{
			$streamOptions['ssl']['capath'] = $certpath;
		}
		else
		{
			$streamOptions['ssl']['cafile'] = $certpath;
		}

		$context = stream_context_create($streamOptions);

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Open the stream for reading.
		$stream = @fopen((string) $uri, 'r', false, $context);

		if (!$stream)
		{
			if (!$php_errormsg)
			{
				// Error but nothing from php? Create our own
				// @todo $err and $errno are undefined variables.
				$php_errormsg = sprintf('Could not connect to resource: %s', $uri, $err, $errno);
			}

			// Restore error tracking to give control to the exception handler
			ini_set('track_errors', $track_errors);

			throw new \RuntimeException($php_errormsg);
		}

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		// Get the metadata for the stream, including response headers.
		$metadata = stream_get_meta_data($stream);

		// Get the contents from the stream.
		$content = stream_get_contents($stream);

		// Close the stream.
		fclose($stream);

		$headers = [];

		if (isset($metadata['wrapper_data']['headers']))
		{
			$headers = $metadata['wrapper_data']['headers'];
		}
		elseif (isset($metadata['wrapper_data']))
		{
			$headers = $metadata['wrapper_data'];
		}

		return $this->getResponse($headers, $content);
	}

	/**
	 * Method to get a response object from a server response.
	 *
	 * @param   array   $headers  The response headers as an array.
	 * @param   string  $body     The response body as a string.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 * @throws  InvalidResponseCodeException
	 */
	protected function getResponse(array $headers, $body)
	{
		// Get the response code from the first offset of the response headers.
		preg_match('/[0-9]{3}/', array_shift($headers), $matches);
		$code = $matches[0];

		if (!is_numeric($code))
		{
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
	 * Method to check if http transport stream available for use
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return function_exists('fopen') && is_callable('fopen') && ini_get('allow_url_fopen');
	}
}
