<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Two Factor Authentication using Google Authenticator TOTP Plugin
 *
 * @since  3.2
 */
class PlgTwofactorauthTotp extends JPlugin
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
	protected $methodName = 'totp';

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
		$section = (int) $this->params->get('section', 3);

		$current_section = 0;

		try
		{
			$app = JFactory::getApplication();

			if ($app->isClient('administrator'))
			{
				$current_section = 2;
			}
			elseif ($app->isClient('site'))
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
			'title'  => JText::_('PLG_TWOFACTORAUTH_TOTP_METHOD_TITLE')
		);
	}

	/**
	 * Shows the configuration page for this two factor authentication method.
	 *
	 * @param   object   $otpConfig  The two factor auth configuration object
	 * @param   integer  $userId     The numeric user ID of the user whose form we'll display
	 *
	 * @return  boolean|string  False if the method is not ours, the HTML of the configuration page otherwise
	 *
	 * @see     UsersModelUser::getOtpConfig
	 * @since   3.2
	 */
	public function onUserTwofactorShowConfiguration($otpConfig, $userId = null)
	{
		// Create a new TOTP class with Google Authenticator compatible settings
		$totp = new FOFEncryptTotp(30, 6, 10);

		if ($otpConfig->method === $this->methodName)
		{
			// This method is already activated. Reuse the same secret key.
			$secret = $otpConfig->config['code'];
		}
		else
		{
			// This methods is not activated yet. Create a new secret key.
			$secret = $totp->generateSecret();
		}

		// These are used by Google Authenticator to tell accounts apart
		$username = JFactory::getUser($userId)->username;
		$hostname = JUri::getInstance()->getHost();

		// This is the URL to the QR code for Google Authenticator
		$url = sprintf("otpauth://totp/%s@%s?secret=%s", $username, $hostname, $secret);

		// Is this a new TOTP setup? If so, we'll have to show the code validation field.
		$new_totp = $otpConfig->method !== 'totp';

		// Start output buffering
		@ob_start();

		// Include the form.php from a template override. If none is found use the default.
		$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_totp', true);

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
			'form'   => $html
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
		if ($method !== $this->methodName)
		{
			return false;
		}

		// Get a reference to the input data object
		$input = JFactory::getApplication()->input;

		// Load raw data
		$rawData = $input->get('jform', array(), 'array');

		if (!isset($rawData['twofactor']['totp']))
		{
			return false;
		}

		$data = $rawData['twofactor']['totp'];

		// Warn if the securitycode is empty
		if (array_key_exists('securitycode', $data) && empty($data['securitycode']))
		{
			try
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('PLG_TWOFACTORAUTH_TOTP_ERR_VALIDATIONFAILED'), 'error');
			}
			catch (Exception $exc)
			{
				// This only happens when we are in a CLI application. We cannot
				// enqueue a message, so just do nothing.
			}

			return false;
		}

		// Create a new TOTP class with Google Authenticator compatible settings
		$totp = new FOFEncryptTotp(30, 6, 10);

		// Check the security code entered by the user (exact time slot match)
		$code = $totp->getCode($data['key']);
		$check = $code === $data['securitycode'];

		/*
		 * If the check fails, test the previous 30 second slot. This allow the
		 * user to enter the security code when it's becoming red in Google
		 * Authenticator app (reaching the end of its 30 second lifetime)
		 */
		if (!$check)
		{
			$time = time() - 30;
			$code = $totp->getCode($data['key'], $time);
			$check = $code === $data['securitycode'];
		}

		/*
		 * If the check fails, test the next 30 second slot. This allows some
		 * time drift between the authentication device and the server
		 */
		if (!$check)
		{
			$time = time() + 30;
			$code = $totp->getCode($data['key'], $time);
			$check = $code === $data['securitycode'];
		}

		if (!$check)
		{
			// Check failed. Do not change two factor authentication settings.
			return false;
		}

		// Check succeeded; return an OTP configuration object
		$otpConfig = (object) array(
			'method'   => 'totp',
			'config'   => array(
				'code' => $data['key']
			),
			'otep'     => array()
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
		if ($otpConfig->method !== $this->methodName)
		{
			return false;
		}

		// Check if there is a security code
		if (empty($credentials['secretkey']))
		{
			return false;
		}

		// Create a new TOTP class with Google Authenticator compatible settings
		$totp = new FOFEncryptTotp(30, 6, 10);

		// Check the code
		$code = $totp->getCode($otpConfig->config['code']);
		$check = $code === $credentials['secretkey'];

		/*
		 * If the check fails, test the previous 30 second slot. This allow the
		 * user to enter the security code when it's becoming red in Google
		 * Authenticator app (reaching the end of its 30 second lifetime)
		 */
		if (!$check)
		{
			$time = time() - 30;
			$code = $totp->getCode($otpConfig->config['code'], $time);
			$check = $code === $credentials['secretkey'];
		}

		/*
		 * If the check fails, test the next 30 second slot. This allows some
		 * time drift between the authentication device and the server
		 */
		if (!$check)
		{
			$time = time() + 30;
			$code = $totp->getCode($otpConfig->config['code'], $time);
			$check = $code === $credentials['secretkey'];
		}

		return $check;
	}
}
