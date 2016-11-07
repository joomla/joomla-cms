<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.yubikey
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Two Factor Authentication using Yubikey Plugin
 *
 * @since  3.2
 */
class PlgTwofactorauthYubikey extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method name
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $methodName = 'yubikey';

	/**
	 * This method returns the identification object for this two factor
	 * authentication plugin.
	 *
	 * @return  stdClass  An object with public properties method and title
	 *
	 * @since   3.2
	 */
	public function onUserTwofactorIdentify()
	{
		$section         = (int) $this->params->get('section', 3);
		$current_section = 0;

		try
		{
			$app = JFactory::getApplication();

			if ($app->isAdmin())
			{
				$current_section = 2;
			}
			elseif ($app->isSite())
			{
				$current_section = 1;
			}
		}
		catch (Exception $exc)
		{
			$current_section = 0;
		}

		if (!($current_section & $section))
		{
			return false;
		}

		return (object) array(
			'method' => $this->methodName,
			'title'  => JText::_('PLG_TWOFACTORAUTH_YUBIKEY_METHOD_TITLE'),
		);
	}

	/**
	 * Shows the configuration page for this two factor authentication method.
	 *
	 * @param   object   $otpConfig  The two factor auth configuration object
	 * @param   integer  $user_id    The numeric user ID of the user whose form we'll display
	 *
	 * @return  boolean|string  False if the method is not ours, the HTML of the configuration page otherwise
	 *
	 * @see     UsersModelUser::getOtpConfig
	 * @since   3.2
	 */
	public function onUserTwofactorShowConfiguration($otpConfig, $user_id = null)
	{
		if ($otpConfig->method == $this->methodName)
		{
			// This method is already activated. Reuse the same Yubikey ID.
			$yubikey = $otpConfig->config['yubikey'];
		}
		else
		{
			// This methods is not activated yet. We'll need a Yubikey TOTP to setup this Yubikey.
			$yubikey = '';
		}

		// Is this a new TOTP setup? If so, we'll have to show the code validation field.
		$new_totp    = $otpConfig->method != $this->methodName;

		// Start output buffering
		@ob_start();

		// Include the form.php from a template override. If none is found use the default.
		$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_yubikey', true);

		JLoader::import('joomla.filesystem.file');

		if (JFile::exists($path . '/form.php'))
		{
			include_once $path . '/form.php';
		}
		else
		{
			include_once __DIR__ . '/tmpl/form.php';
		}

		// Stop output buffering and get the form contents
		$html = @ob_get_clean();

		// Return the form contents
		return array(
			'method' => $this->methodName,
			'form'   => $html,
		);
	}

	/**
	 * The save handler of the two factor configuration method's configuration
	 * page.
	 *
	 * @param   string  $method  The two factor auth method for which we'll show the config page
	 *
	 * @return  boolean|stdClass  False if the method doesn't match or we have an error, OTP config object if it succeeds
	 *
	 * @see     UsersModelUser::setOtpConfig
	 * @since   3.2
	 */
	public function onUserTwofactorApplyConfiguration($method)
	{
		if ($method != $this->methodName)
		{
			return false;
		}

		// Get a reference to the input data object
		$input = JFactory::getApplication()->input;

		// Load raw data
		$rawData = $input->get('jform', array(), 'array');

		if (!isset($rawData['twofactor']['yubikey']))
		{
			return false;
		}

		$data = $rawData['twofactor']['yubikey'];

		// Warn if the securitycode is empty
		if (array_key_exists('securitycode', $data) && empty($data['securitycode']))
		{
			try
			{
				JFactory::getApplication()->enqueueMessage(JText::_('PLG_TWOFACTORAUTH_YUBIKEY_ERR_VALIDATIONFAILED'), 'error');
			}
			catch (Exception $exc)
			{
				// This only happens when we are in a CLI application. We cannot
				// enqueue a message, so just do nothing.
			}

			return false;
		}

		// Validate the Yubikey OTP
		$check = $this->validateYubikeyOtp($data['securitycode']);

		if (!$check)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_TWOFACTORAUTH_YUBIKEY_ERR_VALIDATIONFAILED'), 'error');

			// Check failed. Do not change two factor authentication settings.
			return false;
		}

		// Remove the last 32 digits and store the rest in the user configuration parameters
		$yubikey      = substr($data['securitycode'], 0, -32);

		// Check succeedeed; return an OTP configuration object
		$otpConfig    = (object) array(
			'method'  => $this->methodName,
			'config'  => array(
				'yubikey' => $yubikey
			),
			'otep'    => array()
		);

		return $otpConfig;
	}

	/**
	 * This method should handle any two factor authentication and report back
	 * to the subject.
	 *
	 * @param   array  $credentials  Array holding the user credentials
	 * @param   array  $options      Array of extra options
	 *
	 * @return  boolean  True if the user is authorised with this two-factor authentication method
	 *
	 * @since   3.2
	 */
	public function onUserTwofactorAuthenticate($credentials, $options)
	{
		// Get the OTP configuration object
		$otpConfig = $options['otp_config'];

		// Make sure it's an object
		if (empty($otpConfig) || !is_object($otpConfig))
		{
			return false;
		}

		// Check if we have the correct method
		if ($otpConfig->method != $this->methodName)
		{
			return false;
		}

		// Check if there is a security code
		if (empty($credentials['secretkey']))
		{
			return false;
		}

		// Check if the Yubikey starts with the configured Yubikey user string
		$yubikey_valid = $otpConfig->config['yubikey'];
		$yubikey       = substr($credentials['secretkey'], 0, -32);

		$check = $yubikey == $yubikey_valid;

		if ($check)
		{
			$check = $this->validateYubikeyOtp($credentials['secretkey']);
		}

		return $check;
	}

	/**
	 * Validates a Yubikey OTP against the Yubikey servers
	 *
	 * @param   string  $otp  The OTP generated by your Yubikey
	 *
	 * @return  boolean  True if it's a valid OTP
	 *
	 * @since   3.2
	 */
	public function validateYubikeyOtp($otp)
	{
		$server_queue = array(
			'api.yubico.com',
			'api2.yubico.com',
			'api3.yubico.com',
			'api4.yubico.com',
			'api5.yubico.com',
		);

		shuffle($server_queue);

		$gotResponse = false;
		$check       = false;

		$http  = JHttpFactory::getHttp();
		$token = JSession::getFormToken();
		$nonce = md5($token . uniqid(mt_rand()));

		while (!$gotResponse && !empty($server_queue))
		{
			$server = array_shift($server_queue);
			$uri    = new JUri('https://' . $server . '/wsapi/2.0/verify');

			// I don't see where this ID is used?
			$uri->setVar('id', 1);

			// The OTP we read from the user
			$uri->setVar('otp', $otp);

			// This prevents a REPLAYED_OTP status of the token doesn't change
			// after a user submits an invalid OTP
			$uri->setVar('nonce', $nonce);

			// Minimum service level required: 50% (at least 50% of the YubiCloud
			// servers must reply positively for the OTP to validate)
			$uri->setVar('sl', 50);

			// Timeou waiting for YubiCloud servers to reply: 5 seconds.
			$uri->setVar('timeout', 5);

			try
			{
				$response = $http->get($uri->toString(), null, 6);

				if (!empty($response))
				{
					$gotResponse = true;
				}
				else
				{
					continue;
				}
			}
			catch (Exception $exc)
			{
				// No response, continue with the next server
				continue;
			}
		}

		// No server replied; we can't validate this OTP
		if (!$gotResponse)
		{
			return false;
		}

		// Parse response
		$lines = explode("\n", $response->body);
		$data  = array();

		foreach ($lines as $line)
		{
			$line  = trim($line);
			$parts = explode('=', $line, 2);

			if (count($parts) < 2)
			{
				continue;
			}

			$data[$parts[0]] = $parts[1];
		}

		// Validate the response - We need an OK message reply
		if ($data['status'] != 'OK')
		{
			return false;
		}

		// Validate the response - We need a confidence level over 50%
		if ($data['sl'] < 50)
		{
			return false;
		}

		// Validate the response - The OTP must match
		if ($data['otp'] != $otp)
		{
			return false;
		}

		// Validate the response - The token must match
		if ($data['nonce'] != $nonce)
		{
			return false;
		}

		return true;
	}
}
