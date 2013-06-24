<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Recaptcha Plugin.
 * Based on the oficial recaptcha library( http://recaptcha.net/plugins/php/ )
 *
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 * @since       2.5
 */
class PlgCaptchaRecaptcha extends JPlugin
{
	const RECAPTCHA_API_SERVER = "http://api.recaptcha.net";
	const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
	const RECAPTCHA_VERIFY_SERVER = "api-verify.recaptcha.net";

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Initialise the captcha
	 *
	 * @param   string  $id  The id of the field.
	 *
	 * @return  Boolean	True on success, false otherwise
	 *
	 * @since  2.5
	 */
	public function onInit($id)
	{
		$document = JFactory::getDocument();
		$app      = JFactory::getApplication();

		$lang   = $this->_getLanguage();
		$pubkey = $this->params->get('public_key', '');
		$theme  = $this->params->get('theme', 'clean');

		if ($pubkey == null || $pubkey == '')
		{
			throw new Exception(JText::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY'));
		}

		$server = self::RECAPTCHA_API_SERVER;

		if ($app->isSSLConnection())
		{
			$server = self::RECAPTCHA_API_SECURE_SERVER;
		}

		JHtml::_('script', $server . '/js/recaptcha_ajax.js');
		$document->addScriptDeclaration('window.addEvent(\'domready\', function()
		{
			Recaptcha.create("' . $pubkey . '", "dynamic_recaptcha_1", {theme: "' . $theme . '",' . $lang . 'tabindex: 0});});'
		);

		return true;
	}

	/**
	 * Gets the challenge HTML
	 *
	 * @param   string  $name   The name of the field.
	 * @param   string  $id     The id of the field.
	 * @param   string  $class  The class of the field.
	 *
	 * @return  string  The HTML to be embedded in the form.
	 *
	 * @since  2.5
	 */
	public function onDisplay($name, $id, $class)
	{
		return '<div id="dynamic_recaptcha_1"></div>';
	}

	/**
	  * Calls an HTTP POST function to verify if the user's guess was correct
	  *
	  * @return  True if the answer is correct, false otherwise
	  *
	  * @since  2.5
	  */
	public function onCheckAnswer($code)
	{
		$input      = JFactory::getApplication()->input;
		$privatekey = $this->params->get('private_key');
		$remoteip   = $input->server->get('REMOTE_ADDR', '', 'string');
		$challenge  = $input->get('recaptcha_challenge_field', '', 'string');
		$response   = $input->get('recaptcha_response_field', '', 'string');

		// Check for Private Key
		if (empty($privatekey))
		{
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_NO_PRIVATE_KEY'));

			return false;
		}

		// Check for IP
		if (empty($remoteip))
		{
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_NO_IP'));

			return false;
		}

		// Discard spam submissions
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0)
		{
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_EMPTY_SOLUTION'));

			return false;
		}

		$response = $this->_recaptcha_http_post(
			self::RECAPTCHA_VERIFY_SERVER, "/verify",
			array(
				'privatekey' => $privatekey,
				'remoteip'   => $remoteip,
				'challenge'  => $challenge,
				'response'   => $response
			)
	);

		$answers = explode("\n", $response[1]);

		if (trim($answers[0]) == 'true')
			{
				return true;
		}
		else
		{
			// @todo use exceptions here
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_' . strtoupper(str_replace('-', '_', $answers[1]))));

			return false;
		}
	}

	/**
	 * Encodes the given data into a query string format.
	 *
	 * @param   string  $data  Array of string elements to be encoded
	 *
	 * @return  string  Encoded request
	 *
	 * @since  2.5
	 */
	private function _recaptcha_qsencode($data)
	{
		$req = "";

		foreach ($data as $key => $value)
		{
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}

		// Cut the last '&'
		$req = rtrim($req, '&');

		return $req;
	}

	/**
	 * Submits an HTTP POST to a reCAPTCHA server.
	 *
	 * @param   string  $host
	 * @param   string  $path
	 * @param   array   $data
	 * @param   int     $port
	 *
	 * @return  array   Response
	 *
	 * @since  2.5
	 */
	private function _recaptcha_http_post($host, $path, $data, $port = 80)
	{
		$req = $this->_recaptcha_qsencode($data);

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';

		if (($fs = @fsockopen($host, $port, $errno, $errstr, 10)) == false )
		{
			die('Could not open socket');
		}

		fwrite($fs, $http_request);

		while (!feof($fs))
		{
			// One TCP-IP packet
			$response .= fgets($fs, 1160);
		}

		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	/**
	 * Get the language tag or a custom translation
	 *
	 * @return  string
	 *
	 * @since  2.5
	 */
	private function _getLanguage()
	{
		$language = JFactory::getLanguage();

		$tag = explode('-', $language->getTag());
		$tag = $tag[0];
		$available = array('en', 'pt', 'fr', 'de', 'nl', 'ru', 'es', 'tr');

		if (in_array($tag, $available))
		{
			return "lang : '" . $tag . "',";
		}

		// If the default language is not available, let's search for a custom translation
		if ($language->hasKey('PLG_RECAPTCHA_CUSTOM_LANG'))
		{
			$custom[] = 'custom_translations : {';
			$custom[] = "\t" . 'instructions_visual : "' . JText::_('PLG_RECAPTCHA_INSTRUCTIONS_VISUAL') . '",';
			$custom[] = "\t" . 'instructions_audio : "' . JText::_('PLG_RECAPTCHA_INSTRUCTIONS_AUDIO') . '",';
			$custom[] = "\t" . 'play_again : "' . JText::_('PLG_RECAPTCHA_PLAY_AGAIN') . '",';
			$custom[] = "\t" . 'cant_hear_this : "' . JText::_('PLG_RECAPTCHA_CANT_HEAR_THIS') . '",';
			$custom[] = "\t" . 'visual_challenge : "' . JText::_('PLG_RECAPTCHA_VISUAL_CHALLENGE') . '",';
			$custom[] = "\t" . 'audio_challenge : "' . JText::_('PLG_RECAPTCHA_AUDIO_CHALLENGE') . '",';
			$custom[] = "\t" . 'refresh_btn : "' . JText::_('PLG_RECAPTCHA_REFRESH_BTN') . '",';
			$custom[] = "\t" . 'help_btn : "' . JText::_('PLG_RECAPTCHA_HELP_BTN') . '",';
			$custom[] = "\t" . 'incorrect_try_again : "' . JText::_('PLG_RECAPTCHA_INCORRECT_TRY_AGAIN') . '",';
			$custom[] = '},';
			$custom[] = "lang : '" . $tag . "',";

			return implode("\n", $custom);
		}

		// If nothing helps fall back to english
		return '';
	}
}
