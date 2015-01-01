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
 * NoCaptcha Plugin.
 * Based on the official recaptcha library( https://developers.google.com/recaptcha/docs/php )
 *
 * @since  3.4
 */
class PlgCaptchaNocaptcha extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Initialise the captcha
	 *
	 * @param   string  $id  The id of the field.
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.4
	 */
	public function onInit($id = 'dynamic_recaptcha_1')
	{
		$document = JFactory::getDocument();
		$app      = JFactory::getApplication();

		JHtml::_('jquery.framework');

		$public_key = $this->params->get('public_key', '');
		$theme      = $this->params->get('theme', 'light');

		if ($public_key == null || $public_key == '')
		{
			throw new Exception(JText::_('PLG_NOCAPTCHA_ERROR_NO_PUBLIC_KEY'));
		}

		$file = $app->isSSLConnection() ? 'https' : 'http';
		$file .= '://www.google.com/recaptcha/api.js?hl=' . JFactory::getLanguage()
																	->getTag() . '&onload=onloadCallback&render=explicit';

		JHtml::_('script', $file, true, true);

		$document->addScriptDeclaration('var onloadCallback = function() {'
			. 'grecaptcha.render("' . $id . '", {sitekey: "' . $public_key . '", theme: "' . $theme . '"});'
			. '}'
		);

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
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function onCheckAnswer($code)
	{
		$input      = JFactory::getApplication()->input;
		$privatekey = $this->params->get('private_key');
		$remoteip   = $input->server->get('REMOTE_ADDR', '', 'string');
		$response   = $input->get('g-recaptcha-response', '', 'string');

		// Check for Private Key
		if (empty($privatekey))
		{
			$this->_subject->setError(JText::_('PLG_NOCAPTCHA_ERROR_NO_PRIVATE_KEY'));

			return false;
		}

		// Check for IP
		if (empty($remoteip))
		{
			$this->_subject->setError(JText::_('PLG_NOCAPTCHA_ERROR_NO_IP'));

			return false;
		}

		// Discard spam submissions
		if ($response == null || strlen($response) == 0)
		{
			$this->_subject->setError(JText::_('PLG_NOCAPTCHA_ERROR_EMPTY_SOLUTION'));

			return false;
		}

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

		return true;
	}
}
