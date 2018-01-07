<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Google\HttpBridgePostRequestMethod;
use ReCaptcha\ReCaptcha;

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
		$pubkey = $this->params->get('public_key', '');

		if ($pubkey === '')
		{
			throw new Exception(JText::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY'));
		}

		// Load callback first for browser compatibility
		JHtml::_('script', 'plg_captcha_recaptcha/recaptcha.min.js', array('version' => 'auto', 'relative' => true));

		JHtml::_(
			'script',
			'https://www.google.com/recaptcha/api.js?onload=JoomlaInitReCaptcha2&render=explicit&hl=' . JFactory::getLanguage()->getTag()
		);

		return true;
	}

	/**
	 * Gets the challenge HTML
	 *
	 * @param   string  $name   The name of the field. Not Used.
	 * @param   string  $id     The id of the field.
	 * @param   string  $class  The class of the field. This should be passed as
	 *                          e.g. 'class="required"'.
	 *
	 * @return  string  The HTML to be embedded in the form.
	 *
	 * @since  2.5
	 */
	public function onDisplay($name = null, $id = 'dynamic_recaptcha_1', $class = '')
	{
		return '<div id="' . $id . '" ' . str_replace('class="', 'class="g-recaptcha ', $class)
				. ' data-sitekey="' . $this->params->get('public_key', '')
				. '" data-theme="' . $this->params->get('theme2', 'light')
				. '" data-size="' . $this->params->get('size', 'normal')
				. '"></div>';
	}

	/**
	 * Calls an HTTP POST function to verify if the user's guess was correct
	 *
	 * @param   string  $code  Answer provided by user. Not needed for the Recaptcha implementation
	 *
	 * @return  True if the answer is correct, false otherwise
	 *
	 * @since  2.5
	 */
	public function onCheckAnswer($code = null)
	{
		$input      = JFactory::getApplication()->input;
		$privatekey = $this->params->get('private_key');
		$version    = $this->params->get('version', '2.0');
		$remoteip   = $input->server->get('REMOTE_ADDR', '', 'string');
		$challenge  = null;
		$response   = null;
		$spam       = false;

		switch ($version)
		{
			case '2.0':
				// Challenge Not needed in 2.0 but needed for getResponse call
				$challenge = null;
				$response  = $input->get('g-recaptcha-response', '', 'string');
				$spam      = ($response === '');
				break;
		}

		// Check for Private Key
		if (empty($privatekey))
		{
			throw new RuntimeException(JText::_('PLG_RECAPTCHA_ERROR_NO_PRIVATE_KEY'), 500);
		}

		// Check for IP
		if (empty($remoteip))
		{
			throw new RuntimeException(JText::_('PLG_RECAPTCHA_ERROR_NO_IP'), 500);
		}

		// Discard spam submissions
		if ($spam)
		{
			throw new RuntimeException(JText::_('PLG_RECAPTCHA_ERROR_EMPTY_SOLUTION'), 500);
		}

		return $this->getResponse($privatekey, $remoteip, $response, $challenge);
	}

	/**
	 * Get the reCaptcha response.
	 *
	 * @param   string  $privatekey  The private key for authentication.
	 * @param   string  $remoteip    The remote IP of the visitor.
	 * @param   string  $response    The response received from Google.
	 *
	 * @return  bool True if response is good | False if response is bad.
	 *
	 * @since   3.4
	 */
	private function getResponse(string $privatekey, string $remoteip, string $response)
	{
		$version = $this->params->get('version', '2.0');

		switch ($version)
		{
			case '2.0':
				$apiResponse = (new ReCaptcha($privatekey, new HttpBridgePostRequestMethod))->verify($response, $remoteip);

				if (!$apiResponse->isSuccess())
				{
					foreach ($apiResponse->getErrorCodes() as $error)
					{
						throw new RuntimeException($error, 403);
					}

					return false;
				}

				break;
		}

		return true;
	}
}
