<?php
/**
 * @version		$Id:$
 * @package     Joomla
 * @subpackage  JFramework
 *
 * @copyright   Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

/**
 * Recaptcha Plugin.
 * Based on the oficial recaptcha library( http://recaptcha.net/plugins/php/ )
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since		1.6
 */
class plgCaptchaRecaptcha extends JPlugin
{
	const RECAPTCHA_API_SERVER = "http://api.recaptcha.net";
	const RECAPTCHA_API_SECURE_SERVER = "https://api-secure.recaptcha.net";
	const RECAPTCHA_VERIFY_SERVER = "api-verify.recaptcha.net";

	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Initialise the captcha
	 *
	 * @param	string	$id	The id of the field.
	 * @return	Boolean	True on success, false otherwise
	 */
	public function onInit($id)
	{
		// Initialise variables
		$lang		= $this->_getLanguage();
		$pubkey		= $this->params->get('public_key', '');
		$use_ssl	= JFactory::getConfig()->get('force_ssl', 0);
		$theme		= $this->params->get('theme', 'clean');

		if ($pubkey == null || $pubkey == '') {
			throw new Exception(JText::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY'));
		}

		switch ($use_ssl)
		{
			case 1:
				if(JPATH_BASE == JPATH_ADMINISTRATOR){
					$server = self::RECAPTCHA_API_SECURE_SERVER;
					break;
				}
			case 2:
				$server = self::RECAPTCHA_API_SECURE_SERVER;
				break;
			case 0:
			default:
				$server = self::RECAPTCHA_API_SERVER;
				break;
		}

		JHtml::_('script', $server.'/js/recaptcha_ajax.js');
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Recaptcha.create("'.$pubkey.'", "dynamic_recaptcha_1", {theme: "'.$theme.'",'.$lang.'tabindex: 0});});');

		return true;
	}

	/**
	 * Gets the challenge HTML
	 *
	 * @return  string  The HTML to be embedded in the form.
	 */
	public function onDisplay($name, $id, $class)
	{
		return '<div id="dynamic_recaptcha_1"></div>';
	}

	/**
	  * Calls an HTTP POST function to verify if the user's guess was correct
	  *
	  * @return  True if the answer is correct, false otherwise
	  */
	public function onCheckAnswer($code)
	{
		// Initialise variables
		$privatekey	= $this->params->get('private_key');
		$remoteip	= JRequest::getVar('REMOTE_ADDR', '', 'SERVER');
		$challenge	= JRequest::getString('recaptcha_challenge_field', '');
		$response	= JRequest::getString('recaptcha_response_field', '');;

		// Check for Private Key
		if (empty($privatekey)) {
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_NO_PRIVATE_KEY'));
			return false;
		}

		// Check for IP
		if (empty($remoteip)) {
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_NO_IP'));
			return false;
		}

		// Discard spam submissions
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_EMPTY_SOLUTION'));
			return false;
		}

		$response = $this->_recaptcha_http_post(self::RECAPTCHA_VERIFY_SERVER, "/verify",
												array(
													'privatekey'	=> $privatekey,
													'remoteip'		=> $remoteip,
													'challenge'		=> $challenge,
													'response'		=> $response
												)
										  );

		$answers = explode("\n", $response[1]);

		if (trim($answers[0]) == 'true') {
				return true;
		}
		else
		{
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_'.strtoupper(str_replace('-', '_', $answers[1]))));
			return false;
		}
	}

	/**
 	 * Encodes the given data into a query string format.
 	 *
 	 * @param   string  $data  Array of string elements to be encoded
 	 * @return  string  Encoded request
 	 */
	private function _recaptcha_qsencode($data)
	{
		$req = "";
		foreach ($data as $key => $value) {
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
	 * @return  array   Response
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
		if(($fs = @fsockopen($host, $port, $errno, $errstr, 10)) == false ) {
			die('Could not open socket');
		}

		fwrite($fs, $http_request);

		while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet

		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	/**
	 * Get the language tag or a custom translation
	 *
	 * @return string
	 */
	private function _getLanguage()
	{
		// Initialise variables
		$language = JFactory::getLanguage();
		$lang = $this->params->get('lang');

		// If empty get the default language and see if it is available
		if(empty($lang))
		{
			$tag = $language->getTag();
			$available = array('en','pt','fr','de','nl','ru','es','tr');

			foreach($available as $v)
			{
				if(strrpos($tag, $v) == 0)
				{
					$lang = substr($tag, 0, 2);
					break;
				}
			}
			// If the default language is not available, let's search for a custom translation
			if(empty($lang) && $language->hasKey('PLG_RECAPTCHA_CUSTOM_LANG'))
			{
				$custom[] ='custom_translations : {';
				$custom[] ="\t".'instructions_visual : "'.JText::_('PLG_RECAPTCHA_INSTRUCTIONS_VISUAL').'",';
				$custom[] ="\t".'instructions_audio : "'.JText::_('PLG_RECAPTCHA_INSTRUCTIONS_AUDIO').'",';
				$custom[] ="\t".'play_again : "'.JText::_('PLG_RECAPTCHA_PLAY_AGAIN').'",';
				$custom[] ="\t".'cant_hear_this : "'.JText::_('PLG_RECAPTCHA_CANT_HEAR_THIS').'",';
				$custom[] ="\t".'visual_challenge : "'.JText::_('PLG_RECAPTCHA_VISUAL_CHALLENGE').'",';
				$custom[] ="\t".'audio_challenge : "'.JText::_('PLG_RECAPTCHA_AUDIO_CHALLENGE').'",';
				$custom[] ="\t".'refresh_btn : "'.JText::_('PLG_RECAPTCHA_REFRESH_BTN').'",';
				$custom[] ="\t".'help_btn : "'.JText::_('PLG_RECAPTCHA_HELP_BTN').'",';
				$custom[] ="\t".'incorrect_try_again : "'.JText::_('PLG_RECAPTCHA_INCORRECT_TRY_AGAIN').'",';
				$custom[] ='},';
				$custom[] ="lang : '".$lang."',";

				return implode("\n", $custom);
			}
			else {
				return '';
			}

		}
		else{
			return "lang : '".$lang."',";
		}

	}
}

