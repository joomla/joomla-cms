<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Recaptcha Plugin.
 * Based on the official recaptcha library( https://developers.google.com/recaptcha/docs/php )
 *
 * @since  2.5
 */
class PlgCaptchaRecaptcha extends JPlugin
{
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
	 * @throws  Exception
	 *
	 * @since  2.5
	 */
	public function onInit($id = 'dynamic_recaptcha_1')
	{
		$document = JFactory::getDocument();
		$app      = JFactory::getApplication();

		JHtml::_('jquery.framework');

		$lang       = $this->_getLanguage();
		$version    = $this->params->get('version', '1.0');
		$pubkey     = $this->params->get('public_key', '');

		if ($pubkey == null || $pubkey == '')
		{
			throw new Exception(JText::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY'));
		}

		switch ($version)
		{
			case '1.0':
				$theme = $this->params->get('theme', 'clean');

				$file = $app->isSSLConnection() ? 'https' : 'http';
				$file .= '://www.google.com/recaptcha/api/js/recaptcha_ajax.js';
				JHtml::_('script', $file);

				$document->addScriptDeclaration('jQuery( document ).ready(function()
				{
					Recaptcha.create("' . $pubkey . '", "' . $id . '", {theme: "' . $theme . '",' . $lang . 'tabindex: 0});});'
				);
				break;
			case '2.0':
				$theme = $this->params->get('theme2', 'light');

				$file = $app->isSSLConnection() ? 'https' : 'http';
				$file .= '://www.google.com/recaptcha/api.js?hl=' . JFactory::getLanguage()
						->getTag() . '&onload=onloadCallback&render=explicit';

				JHtml::_('script', $file, true, true);

				$document->addScriptDeclaration('var onloadCallback = function() {'
					. 'grecaptcha.render("' . $id . '", {sitekey: "' . $pubkey . '", theme: "' . $theme . '"});'
					. '}'
				);
				break;
		}

		return true;
	}

	/**
	 * Gets the challenge HTML
	 *
	 * @param   string  $name   The name of the field.
	 * @param   string  $id     The id of the field.
	 * @param   string  $class  The class of the field. This should be passed as
	 *                          e.g. 'class="required"'.
	 *
	 * @return  string  The HTML to be embedded in the form.
	 *
	 * @since  2.5
	 */
	public function onDisplay($name, $id = 'dynamic_recaptcha_1', $class = '')
	{
		return '<div id="' . $id . '" ' . $class . '></div>';
	}

	/**
	 * Calls an HTTP POST function to verify if the user's guess was correct
	 *
	 * @param   string  $code  Answer provided by user.
	 *
	 * @return  True if the answer is correct, false otherwise
	 *
	 * @since  2.5
	 */
	public function onCheckAnswer($code)
	{
		$input      = JFactory::getApplication()->input;
		$privatekey = $this->params->get('private_key');
		$version    = $this->params->get('version', '1.0');
		$remoteip   = $input->server->get('REMOTE_ADDR', '', 'string');

		switch ($version)
		{
			case '1.0':
				$challenge = $input->get('recaptcha_challenge_field', '', 'string');
				$response  = $input->get('recaptcha_response_field', '', 'string');
				$spam      = ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0);
				break;
			case '2.0':
				$response = $input->get('g-recaptcha-response', '', 'string');
				$spam     = ($response == null || strlen($response) == 0);
				break;
		}

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
		if ($spam)
		{
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_EMPTY_SOLUTION'));

			return false;
		}

		return $this->getResponse($privatekey, $remoteip, $response, $challenge);
	}

	/**
	 * Get the reCaptcha response.
	 *
	 * @param   string  $privatekey  The private key for authentication.
	 * @param   string  $remoteip    The remote IP of the visitor.
	 * @param   string  $response    The response received from Google.
	 * @param   string  $challenge   The challenge field from the reCaptcha.
	 *
	 * @return bool True if response is good | False if response is bad.
	 *
	 * @since   3.4
	 */
	private function getResponse($privatekey, $remoteip, $response, $challenge = null)
	{
		$version = $this->params->get('version', '1.0');

		switch ($version)
		{
			case '1.0':
				$response = $this->_recaptcha_http_post(
					'www.google.com', '/recaptcha/api/verify',
					array(
						'privatekey' => $privatekey,
						'remoteip'   => $remoteip,
						'challenge'  => $challenge,
						'response'   => $response
					)
				);

				$answers = explode("\n", $response[1]);

				if (trim($answers[0]) !== 'true')
				{
					// @todo use exceptions here
					$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_' . strtoupper(str_replace('-', '_', $answers[1]))));

					return false;
				}
				break;
			case '2.0':
				require_once 'recaptchalib.php';

				$reCaptcha = new JReCaptcha($privatekey);
				$response  = $reCaptcha->verifyResponse($remoteip, $response);

				if ( !isset($response->success) || !$response->success)
				{
					// @todo use exceptions here
					foreach ($response->errorCodes as $error)
					{
						$this->_subject->setError($error);
					}

					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * Encodes the given data into a query string format.
	 *
	 * @param   array  $data  Array of string elements to be encoded
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
	 * @param   string  $host  Host name to POST to.
	 * @param   string  $path  Path on host to POST to.
	 * @param   array   $data  Data to be POSTed.
	 * @param   int     $port  Optional port number on host.
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
