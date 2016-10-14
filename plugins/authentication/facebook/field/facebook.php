<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.facebook
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Custom login form field for Facebook Login
 *
 * @since  3.7
 */
class JAuthenticationFieldFacebook implements JAuthenticationFieldInterface
{
	/**
	 * Facebook login URL
	 *
	 * @var   string
	 */
	private $url;

	/**
	 * PlgAuthenticationFacebookField constructor.
	 *
	 * @param   JFacebookOAuth  $facebook  The Facebook OAuth object we're going to use
	 */
	public function __construct(JFacebookOAuth $facebook)
	{
		$this->url = $facebook->createUrl();
	}

	public function getType()
	{
		return 'button';
	}

	public function getIcon()
	{
		return '';
	}

	public function getLabel()
	{
		$text = JText::_('PLG_AUTHENTICATION_FACEBOOK_BTN_LABEL');

		return JHtml::_('image', 'plg_authentication_facebook/fb.png', $text, array('title' => $text, 'width' => 16), true) .
			' ' . $text;
	}

	public function getInput()
	{
		return $this->url;
	}
}
