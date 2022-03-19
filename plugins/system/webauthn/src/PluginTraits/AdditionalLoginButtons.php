<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

// Protect from unauthorized access
\defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;

/**
 * Inserts Webauthn buttons into login modules
 *
 * @since   4.0.0
 */
trait AdditionalLoginButtons
{
	/**
	 * Do I need to I inject buttons? Automatically detected (i.e. disabled if I'm already logged in).
	 *
	 * @var     boolean|null
	 * @since   4.0.0
	 */
	protected $allowButtonDisplay = null;

	/**
	 * Have I already injected CSS and JavaScript? Prevents double inclusion of the same files.
	 *
	 * @var     boolean
	 * @since   4.0.0
	 */
	private $injectedCSSandJS = false;

	/**
	 * Should I allow this plugin to add a WebAuthn login button?
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	private function mustDisplayButton(): bool
	{
		if (\is_null($this->allowButtonDisplay))
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
				Joomla::isAdminPage();
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
	 *
	 * @since   4.0.0
	 */
	public function onUserLoginButtons(string $form): array
	{
		// If we determined we should not inject a button return early
		if (!$this->mustDisplayButton())
		{
			return [];
		}

		// Load the language files
		$this->loadLanguage();

		// Load necessary CSS and Javascript files
		$this->addLoginCSSAndJavascript();

		// Return URL
		$uri = new Uri(Uri::base() . 'index.php');
		$uri->setVar(Joomla::getToken(), '1');

		// Unique ID for this button (allows display of multiple modules on the page)
		$randomId = 'plg_system_webauthn-' . UserHelper::genRandomPassword(12) . '-' . UserHelper::genRandomPassword(8);

		// Set up the JavaScript callback
		$url = $uri->toString();

		// Get local path to image
		$image = HTMLHelper::_('image', 'plg_system_webauthn/webauthn.svg', '', '', true, true);

		// If you can't find the image then skip it
		$image = $image ? JPATH_ROOT . substr($image, \strlen(Uri::root(true))) : '';

		// Extract image if it exists
		$image = file_exists($image) ? file_get_contents($image) : '';

		return [
			[
				'label'              => 'PLG_SYSTEM_WEBAUTHN_LOGIN_LABEL',
				'tooltip'            => 'PLG_SYSTEM_WEBAUTHN_LOGIN_DESC',
				'id'                 => $randomId,
				'data-webauthn-form' => $form,
				'data-webauthn-url'  => $url,
				'svg'                => $image,
				'class'              => 'plg_system_webauthn_login_button',
			],
		];
	}

	/**
	 * Injects the WebAuthn CSS and Javascript for frontend logins, but only once per page load.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function addLoginCSSAndJavascript(): void
	{
		if ($this->injectedCSSandJS)
		{
			return;
		}

		// Set the "don't load again" flag
		$this->injectedCSSandJS = true;

		/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		if (!$wa->assetExists('style', 'plg_system_webauthn.button'))
		{
			$wa->registerStyle('plg_system_webauthn.button', 'plg_system_webauthn/button.css');
		}

		if (!$wa->assetExists('script', 'plg_system_webauthn.login'))
		{
			$wa->registerScript('plg_system_webauthn.login', 'plg_system_webauthn/login.js', [], ['defer' => true], ['core']);
		}

		$wa->useStyle('plg_system_webauthn.button')
			->useScript('plg_system_webauthn.login');

		// Load language strings client-side
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME');
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME');
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME');

		// Store the current URL as the default return URL after login (or failure)
		Joomla::setSessionVar('returnUrl', Uri::current(), 'plg_system_webauthn');
	}

}
