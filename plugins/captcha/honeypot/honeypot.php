<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * Honeypot Plugin.
 */

class PlgCaptchaHoneypot extends JPlugin
{

	/**
	 * Displays the honeypot input field and adds style declaration to hide it
	 *
	 * @param   string  $this->params->get("fieldname")   The name of the field.
	 *
	 * @return  string  The HTML to be embedded in the form.
	 *
	 * @since  _DEPLOY_VERSION_
	 */
	public function onDisplay()
	{
		$doc = JFactory::getDocument();
/*		$doc->addStyleDeclaration('body label[for="' . $this->params->get("fieldname")  . '"], body label[for="jform_captcha"], body label[for="jform_captcha"] + span.optional, body #' . $this->params->get("fieldname")  . ' {display: none;  visibility: hidden; }');*/
		return '<input id="' . $this->params->get("fieldname")  . '" name="' . $this->params->get("fieldname")  . '" size="40" type="text" autocomplete="false" autofill="off" />';
	}


	/**
	 * Verifies if the honeypot field is empty
	 *
	 * @param   string  $code  Answer provided by user.
	 *
	 * @return  True if the answer is correct, false otherwise
	 *
	 * @since  _DEPLOY_VERSION_
	 */
	public function onCheckAnswer()
	{
		$input      = JFactory::getApplication()->input;
		$honeypot   = $this->params->get("fieldname");
		$response   = $input->get($honeypot, '', 'string');
		$spam       = ($response != '');

		// Discard spam submissions
		if ($spam)
		{
			$this->_subject->setError(JText::_('PLG_RECAPTCHA_ERROR_HONEYPOT'));

			return false;
		}

		return true;

	}

}