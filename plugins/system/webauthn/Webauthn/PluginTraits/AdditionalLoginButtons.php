<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Plugin\System\Webauthn\Helper\Integration;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Inserts Webauthn buttons into login modules
 */
trait AdditionalLoginButtons
{
	/**
	 * Do I need to I inject buttons? Automatically detected (i.e. disabled if I'm already logged in).
	 *
	 * @var   bool|null
	 */
	protected $allowButtonDisplay = null;

	/**
	 * Have I already injected CSS and JavaScript? Prevents double inclusion of the same files.
	 *
	 * @var   bool
	 */
	private $injectedCSSandJS = false;

	/**
	 * Should I allow this plugin to add a WebAuthn login button?
	 *
	 * @return  bool
	 */
	private function mustDisplayButton(): bool
	{
		if (is_null($this->allowButtonDisplay))
		{
			$this->allowButtonDisplay = false;

			/**
			 * Do not add a WebAuthn login button if we are already logged in
			 */
			try
			{
				if (!Factory::getApplication()->getIdentity()->guest)
				{
					return false;
				}
			}
			catch (Exception $e)
			{
				return false;
			}

			/**
			 * Don't try to show a button if we can't figure out if this is a front- or backend page (it's probably a
			 * CLI or custom application).
			 */
			try
			{
				$isAdminPage = Joomla::isAdminPage();
			}
			catch (Exception $e)
			{
				return false;
			}

			/**
			 * Only display a button on HTML output
			 */
			if (Joomla::getDocumentType() != 'html')
			{
				return false;
			}

			/**
			 * WebAuthn only works on HTTPS. This is a security-related limitation of the W3C Web Authentication
			 * specification, not an issue with this plugin :)
			 */
			if (!Uri::getInstance()->isSsl())
			{
				return false;
			}

			// All checks passed; we should allow displaying a WebAuthn login button
			$this->allowButtonDisplay = true;
		}

		return $this->allowButtonDisplay;
	}

	/**
	 * Creates additional login buttons
	 *
	 * @param   string  $form             The HTML ID of the form we are enclosed in
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @see AuthenticationHelper::getLoginButtons()
	 */
	public function onUserLoginButtons(string $form): array
	{
		// If we determined we should not inject a button return early
		if (!$this->mustDisplayButton())
		{
			return [];
		}

		$this->addLoginCSSAndJavascript();

		// Return URL
		$uri = new Uri(Uri::base() . 'index.php');
		$uri->setVar(Joomla::getToken(), '1');

		// Unique ID for this button (allows display of multiple modules on the page)
		$randomId = 'plg_system_webauthn-' . UserHelper::genRandomPassword(12) . '-' . UserHelper::genRandomPassword(8);

		// Set up the JavaScript callback
		$url = $uri->toString();
		$onClick = "return plg_system_webauthn_login('{$form}', '{$url}')";

		return [
			[
				'label'   => 'PLG_SYSTEM_WEBAUTHN_LOGIN_LABEL',
				'onclick' => $onClick,
				'id'      => $randomId,
				'image'   => 'plg_system_webauthn/webauthn-black.png',
				'class'   => 'plg_system_webauthn_login_button',
			],
		];
	}

	/**
	 * Injects the WebAuthn CSS and Javascript for frontend logins, but only once per page load.
	 *
	 * @return  void
	 */
	private function addLoginCSSAndJavascript(): void
	{
		if ($this->injectedCSSandJS)
		{
			return;
		}

		// Set the "don't load again" flag
		$this->injectedCSSandJS = true;

		// Load the CSS
		HTMLHelper::_('stylesheet', 'plg_system_webauthn/button.css', [
			'relative' => true,
		]);

		// Load the JavaScript
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'plg_system_webauthn/login.js', [
			'relative'  => true,
		]);

		// Load language strings client-side
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME');
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME');
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME');

		// Store the current URL as the default return URL after login (or failure)
		Joomla::setSessionVar('returnUrl', Uri::current(), 'plg_system_webauthn');
	}

}
