<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\TransparentAuthentication;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Encrypt\Aes;
use FOF30\Encrypt\Totp;

/**
 * Retrieves the values for transparent authentication from the request
 */
class TransparentAuthentication
{
	/** Use HTTP Basic Authentication with time-based one time passwords */
	public const Auth_HTTPBasicAuth_TOTP = 1;

	/** Use Query String Parameter authentication with time-based one time passwords */
	public const Auth_QueryString_TOTP = 2;

	/** Use HTTP Basic Authentication with plain text username and password */
	public const Auth_HTTPBasicAuth_Plaintext = 3;

	/** Use single query string parameter authentication with JSON-encoded, plain text username and password */
	public const Auth_QueryString_Plaintext = 4;

	/** Use two query string parameters for plain text username and password authentication */
	public const Auth_SplitQueryString_Plaintext = 5;

	/** @var int The time step for TOTP authentication */
	protected $timeStep = 6;

	/** @var string The TOTP secret key */
	protected $totpKey = '';
	/** @var array Enabled authentication methods, see the class constants */
	protected $authenticationMethods = [3, 4, 5];
	/** @var string The username required for the Auth_HTTPBasicAuth_TOTP method */
	protected $basicAuthUsername = '_fof_auth';
	/** @var string The query parameter for the Auth_QueryString_Plaintext method */
	protected $queryParam = '_fofauthentication';
	/** @var string The query parameter for the username in the Auth_SplitQueryString_Plaintext method */
	protected $queryParamUsername = '_fofusername';
	/** @var string The query parameter for the password in the Auth_SplitQueryString_Plaintext method */
	protected $queryParamPassword = '_fofpassword';
	/** @var  bool  Should I log out the user after the dispatcher exits? */
	protected $logoutOnExit = true;
	/** @var Container The container we are attached to */
	protected $container = null;
	/** @var string Internal variable */
	private $cryptoKey = '';

	/**
	 * Public constructor.
	 *
	 * The optional $config array can contain the following values (corresponding to the same-named properties of this
	 * class): timeStep, totpKey, cryptoKey, basicAuthUsername, queryParam, queryParamUsername, queryParamPassword,
	 * logoutOnExit. See the property descriptions for more information.
	 *
	 * @param   Container  $container
	 * @param   array      $config
	 */
	function __construct(Container $container, array $config = [])
	{
		$this->container = $container;

		// Initialise from the $config array
		$knownKeys = [
			'timeStep', 'totpKey', 'cryptoKey', 'basicAuthUsername', 'queryParam', 'queryParamUsername',
			'queryParamPassword', 'logoutOnExit',
		];

		foreach ($knownKeys as $key)
		{
			if (isset($config[$key]))
			{
				$this->$key = $config[$key];
			}
		}

		if (isset($config['authenticationMethods']))
		{
			$this->authenticationMethods = $this->parseAuthenticationMethods($config['authenticationMethods']);
		}
	}

	/**
	 * Get the enabled authentication methods
	 *
	 * @return   array
	 *
	 * @codeCoverageIgnore
	 */
	public function getAuthenticationMethods()
	{
		return $this->authenticationMethods;
	}

	/**
	 * Set the enabled authentication methods
	 *
	 * @param   array  $authenticationMethods
	 *
	 * @codeCoverageIgnore
	 */
	public function setAuthenticationMethods($authenticationMethods)
	{
		$this->authenticationMethods = $authenticationMethods;
	}

	/**
	 * Enable an authentication method
	 *
	 * @param   integer  $method
	 */
	public function addAuthenticationMethod($method)
	{
		if (!in_array($method, $this->authenticationMethods))
		{
			$this->authenticationMethods[] = $method;
		}
	}

	/**
	 * Disable an authentication method
	 *
	 * @param   integer  $method
	 */
	public function removeAuthenticationMethod($method)
	{
		if (in_array($method, $this->authenticationMethods))
		{
			$key = array_search($method, $this->authenticationMethods);
			unset($this->authenticationMethods[$key]);
		}
	}

	/**
	 * Get the required username for the HTTP Basic Authentication with TOTP method
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	public function getBasicAuthUsername()
	{
		return $this->basicAuthUsername;
	}

	/**
	 * Set the required username for the HTTP Basic Authentication with TOTP method
	 *
	 * @param   string  $basicAuthUsername
	 *
	 * @codeCoverageIgnore
	 */
	public function setBasicAuthUsername($basicAuthUsername)
	{
		$this->basicAuthUsername = $basicAuthUsername;
	}

	/**
	 * Get the query parameter for the Auth_QueryString_TOTP method
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	public function getQueryParam()
	{
		return $this->queryParam;
	}

	/**
	 * Set the query parameter for the Auth_QueryString_TOTP method
	 *
	 * @param   string  $queryParam
	 *
	 * @codeCoverageIgnore
	 */
	public function setQueryParam($queryParam)
	{
		$this->queryParam = $queryParam;
	}

	/**
	 * Get the query string for the password in the Auth_SplitQueryString_Plaintext method
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	public function getQueryParamPassword()
	{
		return $this->queryParamPassword;
	}

	/**
	 * Set the query string for the password in the Auth_SplitQueryString_Plaintext method
	 *
	 * @param   string  $queryParamPassword
	 *
	 * @codeCoverageIgnore
	 */
	public function setQueryParamPassword($queryParamPassword)
	{
		$this->queryParamPassword = $queryParamPassword;
	}

	/**
	 * Get the query string for the username in the Auth_SplitQueryString_Plaintext method
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	public function getQueryParamUsername()
	{
		return $this->queryParamUsername;
	}

	/**
	 * Set the query string for the username in the Auth_SplitQueryString_Plaintext method
	 *
	 * @param   string  $queryParamUsername
	 *
	 * @codeCoverageIgnore
	 */
	public function setQueryParamUsername($queryParamUsername)
	{
		$this->queryParamUsername = $queryParamUsername;
	}

	/**
	 * Get the time step in seconds for the TOTP in the Auth_HTTPBasicAuth_TOTP method
	 *
	 * @return int
	 *
	 * @codeCoverageIgnore
	 */
	public function getTimeStep()
	{
		return $this->timeStep;
	}

	/**
	 * Set the time step in seconds for the TOTP in the Auth_HTTPBasicAuth_TOTP method
	 *
	 * @param   int  $timeStep
	 *
	 * @codeCoverageIgnore
	 */
	public function setTimeStep($timeStep)
	{
		$this->timeStep = (int) $timeStep;
	}

	/**
	 * Get the secret key for the TOTP in the Auth_HTTPBasicAuth_TOTP method
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	public function getTotpKey()
	{
		return $this->totpKey;
	}

	/**
	 * Set the secret key for the TOTP in the Auth_HTTPBasicAuth_TOTP method
	 *
	 * @param   string  $totpKey
	 *
	 * @codeCoverageIgnore
	 */
	public function setTotpKey($totpKey)
	{
		$this->totpKey = $totpKey;
	}

	/**
	 * Should I log out when the dispatcher finishes?
	 *
	 * @return boolean
	 *
	 * @codeCoverageIgnore
	 */
	public function getLogoutOnExit()
	{
		return $this->logoutOnExit;
	}

	/**
	 * Set the log out on exit flag (for testing)
	 *
	 * @param   boolean  $logoutOnExit
	 *
	 * @codeCoverageIgnore
	 */
	public function setLogoutOnExit($logoutOnExit)
	{
		$this->logoutOnExit = $logoutOnExit;
	}

	/**
	 * Tries to get the transparent authentication credentials from the request
	 *
	 * @return  array|null
	 */
	public function getTransparentAuthenticationCredentials()
	{
		$return = null;

		// Always run onFOFGetTransparentAuthenticationCredentials. These methods take precedence over anything else.
		$this->container->platform->importPlugin('user');
		$this->container->platform->importPlugin('fof');
		$pluginResults = $this->container->platform->runPlugins('onFOFGetTransparentAuthenticationCredentials', [$this->container]);

		foreach ($pluginResults as $result)
		{
			if (empty($result))
			{
				continue;
			}

			if (is_array($result))
			{
				return $result;
			}
		}

		// Make sure there are enabled transparent authentication methods
		if (empty($this->authenticationMethods))
		{
			return $return;
		}

		$input = $this->container->input;

		foreach ($this->authenticationMethods as $method)
		{
			switch ($method)
			{
				case self::Auth_HTTPBasicAuth_TOTP:
					if (empty($this->totpKey))
					{
						continue 2;
					}

					if (empty($this->basicAuthUsername))
					{
						continue 2;
					}

					if (!isset($_SERVER['PHP_AUTH_USER']))
					{
						continue 2;
					}

					if (!isset($_SERVER['PHP_AUTH_PW']))
					{
						continue 2;
					}

					if ($_SERVER['PHP_AUTH_USER'] != $this->basicAuthUsername)
					{
						continue 2;
					}

					$encryptedData = $_SERVER['PHP_AUTH_PW'];

					return $this->decryptWithTOTP($encryptedData);

					break;

				case self::Auth_QueryString_TOTP:
					if (empty($this->queryParam))
					{
						continue 2;
					}

					$encryptedData = $input->get($this->queryParam, '', 'raw');

					if (empty($encryptedData))
					{
						continue 2;
					}

					$return = $this->decryptWithTOTP($encryptedData);

					if (!is_null($return))
					{
						return $return;
					}

					break;

				case self::Auth_HTTPBasicAuth_Plaintext:
					if (!isset($_SERVER['PHP_AUTH_USER']))
					{
						continue 2;
					}

					if (!isset($_SERVER['PHP_AUTH_PW']))
					{
						continue 2;
					}

					return [
						'username' => $_SERVER['PHP_AUTH_USER'],
						'password' => $_SERVER['PHP_AUTH_PW'],
					];

					break;

				case self::Auth_QueryString_Plaintext:
					if (empty($this->queryParam))
					{
						continue 2;
					}

					$jsonEncoded = $input->get($this->queryParam, '', 'raw');

					if (empty($jsonEncoded))
					{
						continue 2;
					}

					$authInfo = json_decode($jsonEncoded, true);

					if (!is_array($authInfo))
					{
						continue 2;
					}

					if (!array_key_exists('username', $authInfo) || !array_key_exists('password', $authInfo))
					{
						continue 2;
					}

					return $authInfo;

					break;

				case self::Auth_SplitQueryString_Plaintext:
					if (empty($this->queryParamUsername))
					{
						continue 2;
					}

					if (empty($this->queryParamPassword))
					{
						continue 2;
					}

					$username = $input->get($this->queryParamUsername, '', 'raw');
					$password = $input->get($this->queryParamPassword, '', 'raw');

					if (empty($username))
					{
						continue 2;
					}

					if (empty($password))
					{
						continue 2;
					}

					return [
						'username' => $username,
						'password' => $password,
					];

					break;
			}
		}

		return $return;
	}

	/**
	 * Parses a list of transparent authentication methods (array or comma separated list of integers or method names)
	 * and converts it into an array of integers this class understands.
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	protected function parseAuthenticationMethods($methods)
	{
		if (empty($methods))
		{
			return [];
		}

		if (!is_array($methods))
		{
			$methods = explode(',', $methods);
		}

		$return = [];

		foreach ($methods as $method)
		{
			if (empty($method))
			{
				continue;
			}

			$method = trim($method);

			if ((int) $method == $method)
			{
				$return[] = (int) $method;
			}

			switch ($method)
			{
				case 'HTTPBasicAuth_TOTP':
					$return[] = 1;
					break;

				case 'QueryString_TOTP':
					$return[] = 2;
					break;

				case 'HTTPBasicAuth_Plaintext':
					$return[] = 3;
					break;

				case 'QueryString_Plaintext':
					$return[] = 4;
					break;

				case 'SplitQueryString_Plaintext':
					$return[] = 5;

			}
		}

		return $return;
	}

	/**
	 * Decrypts a transparent authentication message using a TOTP
	 *
	 * @param   string  $encryptedData  The encrypted data
	 *
	 * @return  array  The decrypted data
	 */
	private function decryptWithTOTP($encryptedData)
	{
		if (empty($this->totpKey))
		{
			$this->cryptoKey = null;

			return null;
		}

		$totp   = new Totp($this->timeStep);
		$period = $totp->getPeriod();
		$period--;

		for ($i = 0; $i <= 2; $i++)
		{
			$time            = ($period + $i) * $this->timeStep;
			$otp             = $totp->getCode($this->totpKey, $time);
			$this->cryptoKey = hash('sha256', $this->totpKey . $otp);

			$aes = new Aes($this->cryptoKey);
			try
			{
				$ret = $aes->decryptString($encryptedData);
			}
			catch (Exception $e)
			{
				continue;
			}
			$ret = rtrim($ret, "\000");

			$ret = json_decode($ret, true);

			if (!is_array($ret))
			{
				continue;
			}

			if (!array_key_exists('username', $ret))
			{
				continue;
			}

			if (!array_key_exists('password', $ret))
			{
				continue;
			}

			// Successful decryption!
			return $ret;
		}

		// Obviously if we're here we could not decrypt anything. Bail out.
		$this->cryptoKey = null;

		return null;
	}
}
