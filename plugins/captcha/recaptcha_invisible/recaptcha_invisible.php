<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Google\HttpBridgePostRequestMethod;
use Joomla\Utilities\IpHelper; 

/**
 * Invisible reCAPTCHA Plugin.
 *
 * @since  3.9.0
 */
class PlgCaptchaRecaptcha_Invisible extends \JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Reports the privacy related capabilities for this plugin to site administrators.
	 *
	 * @return  array
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyCollectAdminCapabilities()
	{
		$this->loadLanguage();

		return array(
			JText::_('PLG_CAPTCHA_RECAPTCHA_INVISIBLE') => array(
				JText::_('PLG_RECAPTCHA_INVISIBLE_PRIVACY_CAPABILITY_IP_ADDRESS'),
			)
		);
	}

	/**
	 * Initialise the captcha
	 *
	 * @param   string  $id  The id of the field.
	 *
	 * @return  boolean	True on success, false otherwise
	 *
	 * @since   3.9.0
	 * @throws  \RuntimeException
	 */
	public function onInit($id = 'dynamic_recaptcha_invisible_1')
	{
		$pubkey = $this->params->get('public_key', '');

		if ($pubkey === '')
		{
			throw new \RuntimeException(JText::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PUBLIC_KEY'));
		}

		// Load callback first for browser compatibility
		\JHtml::_(
			'script',
			'plg_captcha_recaptcha_invisible/recaptcha.min.js',
			array('version' => 'auto', 'relative' => true),
			array('async' => 'async', 'defer' => 'defer')
		);

		// Load Google reCAPTCHA api js
		$file = 'https://www.google.com/recaptcha/api.js'
			. '?onload=JoomlaInitReCaptchaInvisible'
			. '&render=explicit'
			. '&hl=' . \JFactory::getLanguage()->getTag();
		\JHtml::_(
			'script',
			$file,
			array(),
			array('async' => 'async', 'defer' => 'defer')
		);

		return true;
	}

	/**
	 * Gets the challenge HTML
	 *
	 * @param   string  $name   The name of the field. Not Used.
	 * @param   string  $id     The id of the field.
	 * @param   string  $class  The class of the field.
	 *
	 * @return  string  The HTML to be embedded in the form.
	 *
	 * @since  3.9.0
	 */
	public function onDisplay($name = null, $id = 'dynamic_recaptcha_invisible_1', $class = '')
	{
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$ele = $dom->createElement('div');
		$ele->setAttribute('id', $id);
		$ele->setAttribute('class', ((trim($class) == '') ? 'g-recaptcha' : ($class . ' g-recaptcha')));
		$ele->setAttribute('data-sitekey', $this->params->get('public_key', ''));
		$ele->setAttribute('data-badge', $this->params->get('badge', 'bottomright'));
		$ele->setAttribute('data-size', 'invisible');
		$ele->setAttribute('data-tabindex', $this->params->get('tabindex', '0'));
		$ele->setAttribute('data-callback', $this->params->get('callback', ''));
		$ele->setAttribute('data-expired-callback', $this->params->get('expired_callback', ''));
		$ele->setAttribute('data-error-callback', $this->params->get('error_callback', ''));
		$dom->appendChild($ele);

		return $dom->saveHTML($ele);
	}

	/**
	 * Calls an HTTP POST function to verify if the user's guess was correct
	 *
	 * @param   string  $code  Answer provided by user. Not needed for the Recaptcha implementation
	 *
	 * @return  boolean  True if the answer is correct, false otherwise
	 *
	 * @since   3.9.0
	 * @throws  \RuntimeException
	 */
	public function onCheckAnswer($code = null)
	{
		$input      = \JFactory::getApplication()->input;
		$privatekey = $this->params->get('private_key');
		$remoteip   = IpHelper::getIp();

		$response  = $input->get('g-recaptcha-response', '', 'string');

		// Check for Private Key
		if (empty($privatekey))
		{
			throw new \RuntimeException(JText::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PRIVATE_KEY'));
		}

		// Check for IP
		if (empty($remoteip))
		{
			throw new \RuntimeException(JText::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_IP'));
		}

		// Discard spam submissions
		if (trim($response) == '')
		{
			throw new \RuntimeException(JText::_('PLG_RECAPTCHA_INVISIBLE_ERROR_EMPTY_SOLUTION'));
		}

		return $this->getResponse($privatekey, $remoteip, $response);
	}

	/**
	 * Method to react on the setup of a captcha field. Gives the possibility
	 * to change the field and/or the XML element for the field.
	 *
	 * @param   \Joomla\CMS\Form\Field\CaptchaField  $field    Captcha field instance
	 * @param   \SimpleXMLElement                    $element  XML form definition
	 *
	 * @return void
	 *
	 * @since 3.9.0
	 */
	public function onSetupField(\Joomla\CMS\Form\Field\CaptchaField $field, \SimpleXMLElement $element)
	{
		// Hide the label for the invisible recaptcha type
		$element['hiddenLabel'] = true;
	}

	/**
	 * Get the reCaptcha response.
	 *
	 * @param   string  $privatekey  The private key for authentication.
	 * @param   string  $remoteip    The remote IP of the visitor.
	 * @param   string  $response    The response received from Google.
	 *
	 * @return  boolean  True if response is good | False if response is bad.
	 *
	 * @since   3.9.0
	 * @throws  \RuntimeException
	 */
	private function getResponse($privatekey, $remoteip, $response)
	{
		$reCaptcha = new \ReCaptcha\ReCaptcha($privatekey, new HttpBridgePostRequestMethod);
		$response = $reCaptcha->verify($response, $remoteip);

		if (!$response->isSuccess())
		{
			foreach ($response->getErrorCodes() as $error)
			{
				throw new \RuntimeException($error);
			}

			return false;
		}

		return true;
	}
}
