<?php
/**
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * OAuth Message class for the Joomla Platform.
 *
 * @property  string  $callback         OAuth callback URL
 * @property  string  $consumerKey      OAuth consumer key
 * @property  string  $nonce            OAuth nonce
 * @property  string  $signature        OAuth signature
 * @property  string  $signatureMethod  OAuth signature method
 * @property  string  $timestamp        OAuth timestamp
 * @property  string  $token            OAuth token
 * @property  string  $tokenSecret      OAuth token secret
 * @property  string  $verifier         OAuth verfier
 * @property  string  $version          OAuth version
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1Message
{
	/**
	 * @var    array  List of possible OAuth 1.0 parameters.
	 * @since  12.1
	 */
	protected static $reserved = array(
		'oauth_callback',
		'oauth_consumer_key',
		'oauth_nonce',
		'oauth_signature',
		'oauth_signature_method',
		'oauth_timestamp',
		'oauth_token',
		'oauth_token_secret',
		'oauth_verifier',
		'oauth_version'
	);

	/**
	 * @var    array  Associative array of parameters for the OAuth 1.0 message.
	 * @since  12.3
	 */
	private $_nonce;

	/**
	 * @var    array  Associative array of parameters for the OAuth 1.0 message.
	 * @since  12.3
	 */
	private $_parameters = array();

	/**
	 * Get the list of reserved OAuth 1.0 parameters.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public static function getReservedParameters()
	{
		return self::$reserved;
	}

	/**
	 * Object constructor.  If passed in, this will only set valid OAuth message parameters.  If non-valid
	 * parameters are in the parameters array they will be ignored.
	 *
	 * Note: It is assumed that the parameters will already be decoded.
	 *
	 * @param   array         $parameters  The optional OAuth parameters to set.
	 * @param   JOAuth1Nonce  $nonce       The nonce object.
	 *
	 * @see     bind()
	 * @since   12.1
	 */
	public function __construct(array $parameters = null, JOAuth1Nonce $nonce = null)
	{
		if (!empty($parameters))
		{
			$this->bind($parameters);
		}

		$this->_nonce = $nonce ? $nonce : new JOAuth1Nonce;
	}

	/**
	 * Get an OAuth 1.0 property value.
	 *
	 * @param   string  $p  The name of the property for which to return the value.
	 *
	 * @return  mixed  The property value for the given property name.
	 *
	 * @since   12.1
	 */
	public function __get($p)
	{
		// Convert camelcase string to underscore delimited oauth_ property name.
		$p = strtolower('oauth_' . preg_replace('#_+#', '_', trim(preg_replace('#([A-Z])#', '_$1', $p))));

		if (isset($this->_parameters[$p]))
		{
			return $this->_parameters[$p];
		}
	}

	/**
	 * Set a value for an OAuth 1.0 property.
	 *
	 * Note: It is assumed that the property will already be decoded.
	 *
	 * @param   string  $p  The name of the property for which to set the value.
	 * @param   mixed   $v  The property value to set.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function __set($p, $v)
	{
		// Convert camelcase string to underscore delimited oauth_ property name.
		$p = strtolower('oauth_' . preg_replace('#_+#', '_', trim(preg_replace('#([A-Z])#', '_$1', $p))));

		// Only set the value if it exists in the reserved property list.
		if (in_array($p, self::$reserved))
		{
			$this->_parameters[$p] = $v;
		}
	}

	/**
	 * Set the OAuth message parameters.  This will only set valid OAuth message parameters.  If non-valid
	 * parameters are in the parameters array they will be ignored.
	 *
	 * Note: It is assumed that the parameters will already be decoded.
	 *
	 * @param   array  $parameters  The OAuth message parameters to set.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function bind(array $parameters)
	{
		// Ensure that only valid OAuth parameters are set if they exist.
		if (!empty($parameters))
		{
			foreach ($parameters as $k => $v)
			{
				if (in_array($k, self::$reserved))
				{
					// Perform url decoding so that any use of '+' as the encoding of the space character is correctly handled.
					$this->_parameters[$k] = $v;
				}
			}
		}
	}

	/**
	 * Method to determine whether or not the message signature is valid.
	 *
	 * @param   string  $requestUrl        The message's request URL.
	 * @param   string  $requestMethod     The message's request method.
	 * @param   string  $clientSecret      The OAuth client's secret.
	 * @param   string  $credentialSecret  The OAuth credentials' secret.
	 *
	 * @return  boolean  True if the message is properly signed.
	 *
	 * @since   12.1
	 */
	public function isValid($requestUrl, $requestMethod, $clientSecret, $credentialSecret = null)
	{
		$signature = $this->sign($requestUrl, $requestMethod, $clientSecret, $credentialSecret);

		if ($this->signatureType != 'PLAINTEXT' && !$this->_nonce->validate($this->nonce, $this->consumerKey, $this->timestamp, $this->token))
		{
			// The nonce was invalid (either the timestamp was too old or it has already been used).
			return false;
		}

		return ($this->signature && ($signature == str_replace(' ', '+', $this->signature)));
	}

	/**
	 * Get the message string complete and signed.
	 *
	 * @param   string  $requestUrl        The message's request URL.
	 * @param   string  $requestMethod     The message's request method.
	 * @param   string  $clientSecret      The OAuth client's secret.
	 * @param   string  $credentialSecret  The OAuth credentials' secret.
	 *
	 * @return  string  The OAuth message signature.
	 *
	 * @since   12.1
	 * @throws  InvalidArgumentException
	 */
	public function sign($requestUrl, $requestMethod, $clientSecret, $credentialSecret = null)
	{
		// Get a message signer object.
		$signer = $this->_fetchSigner();

		// Get the base string for signing.
		$baseString = $this->_fetchStringForSigning($requestUrl, $requestMethod);

		return $signer->sign($baseString, rawurlencode($clientSecret), rawurlencode($credentialSecret));
	}

	/**
	 * Method to get a message signer object based on the message's oauth_signature_method parameter.
	 *
	 * @return  JOAuth1MessageSigner  The OAuth message signer object for the message.
	 *
	 * @since   12.1
	 * @throws  InvalidArgumentException
	 */
	private function _fetchSigner()
	{
		switch ($this->signatureMethod)
		{
			case 'HMAC-SHA1':
				$signer = new JOAuth1MessageSignerHMAC;
				break;
			case 'RSA-SHA1':
				// @TODO We don't support RSA because we don't yet have a way to inject the private key.
				throw new InvalidArgumentException('RSA signatures are not supported');
				$signer = new JOAuth1MessageSignerRSA;
				break;
			case 'PLAINTEXT':
				$signer = new JOAuth1MessageSignerPlaintext;
				break;
			default:
				throw new InvalidArgumentException('No valid signature method was found.');
				break;
		}

		return $signer;
	}

	/**
	 * Method to get the OAuth message string for signing.
	 *
	 * Note: As of PHP 5.3 the rawurlencode() function is RFC 3986 compliant therefore this requires PHP 5.3+
	 *
	 * @param   string  $requestUrl     The message's request URL.
	 * @param   string  $requestMethod  The message's request method.
	 *
	 * @return  string  The unsigned OAuth message string.
	 *
	 * @link    http://www.faqs.org/rfcs/rfc3986
	 * @see     rawurlencode()
	 * @since   12.3
	 */
	private function _fetchStringForSigning($requestUrl, $requestMethod)
	{
		// Get a JURI instance for the request URL.
		$uri = new JURI($requestUrl);

		// Initialise base array.
		$base = array();

		// Get the found parameters.
		$params = $this->_parameters;

		// Add the variables from the URI query string.
		foreach ($uri->getQuery(true) as $k => $v)
		{
			if (strpos($k, 'oauth_') !== 0)
			{
				$params[$k] = $v;
			}
		}

		// Make sure that any found oauth_signature is not included.
		unset($params['oauth_signature']);

		// Ensure the parameters are in order by key.
		ksort($params);

		// Iterate over the keys to add properties to the base.
		foreach ($params as $key => $value)
		{
			// If we have multiples for the parameter let's loop over them.
			if (is_array($value))
			{
				// Don't want to do this more than once in the inner loop.
				$key = rawurlencode($key);

				// Sort the value array and add each one.
				sort($value, SORT_STRING);

				foreach ($value as $v)
				{
					$base[] = $key . '=' . rawurlencode($v);
				}
			}
			else
			// The common case is that there is one entry per property.
			{
				$base[] = rawurlencode($key) . '=' . rawurlencode($value);
			}
		}

		// Start off building the base string by adding the request method and URI.
		$base = array(
			rawurlencode(strtoupper($requestMethod)),
			rawurlencode(strtolower($uri->toString(array('scheme', 'user', 'pass', 'host', 'port'))) . $uri->getPath()),
			rawurlencode(implode('&', $base))
		);

		return implode('&', $base);
	}
}
