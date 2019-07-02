<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\Helper;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

abstract class Integration
{
	/**
	 * Have I already injected CSS and JavaScript? Prevents double inclusion of the same files.
	 *
	 * @var   bool
	 */
	private static $injectedCSSandJS = false;

	/**
	 * Returns the HTML for the Passwordless Login button.
	 *
	 * The following options are recognized and sent to the akeeba.webauthn.button layout:
	 *
	 * - class  CSS class for the button.
	 * - image  Image file to put in the button, before the label.
	 * - icon   CSS class for a span to use instead of an image file in the button.
	 *
	 * Any missing options are replaced with default values per the plugin's configuration.
	 *
	 * @param   array  $options
	 *
	 * @return  string
	 */
	public static function getLoginButtonHTML(array $options = []): string
	{
		self::addLoginCSSAndJavascript();

		$options = array_merge([
			'class' => 'akeeba-passwordless-login-button',
			'image' => 'plg_system_webauthn/webauthn-black.png',
			'icon'  => '',
		], $options);

		return Joomla::renderLayout('akeeba.webauthn.button', $options);
	}

	/**
	 * Injects the Webauthn CSS and Javascript for frontend logins, but only once per page load.
	 *
	 * @return  void
	 */
	protected static function addLoginCSSAndJavascript(): void
	{
		if (self::$injectedCSSandJS)
		{
			return;
		}

		// Load the CSS
		HTMLHelper::_('stylesheet', 'plg_system_webauthn/button.css', [
			'relative' => true,
		]);

		// Load the JavaScript
		HTMLHelper::_('script', 'plg_system_webauthn/dist/login.js', [
			'relative'  => true,
			'framework' => true,
		]);

		// Load language strings client-side
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME');
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME');
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME');

		// Store the current URL as the default return URL after login (or failure)
		Joomla::setSessionVar('returnUrl', Uri::current(), 'plg_system_webauthn');

		// Set the "don't load again" flag
		self::$injectedCSSandJS = true;
	}
}