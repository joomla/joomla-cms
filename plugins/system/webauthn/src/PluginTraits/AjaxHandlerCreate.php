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
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Helper\CredentialsCreation;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use RuntimeException;
use Webauthn\PublicKeyCredentialSource;

/**
 * Ajax handler for akaction=create
 *
 * Handles the browser postback for the credentials creation flow
 *
 * @since   4.0.0
 */
trait AjaxHandlerCreate
{
	/**
	 * Handle the callback to add a new WebAuthn authenticator
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function onAjaxWebauthnCreate(): string
	{
		// Load the language files
		$this->loadLanguage();

		/**
		 * Fundamental sanity check: this callback is only allowed after a Public Key has been created server-side and
		 * the user it was created for matches the current user.
		 *
		 * This is also checked in the validateAuthenticationData() so why check here? In case we have the wrong user
		 * I need to fail early with a Joomla error page instead of falling through the code and possibly displaying
		 * someone else's Webauthn configuration thus mitigating a major privacy and security risk. So, please, DO NOT
		 * remove this sanity check!
		 */
		$storedUserId = Joomla::getSessionVar('registration_user_id', 0, 'plg_system_webauthn');
		$thatUser     = empty($storedUserId) ?
			Factory::getApplication()->getIdentity() :
			Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($storedUserId);
		$myUser = Factory::getApplication()->getIdentity();

		if ($thatUser->guest || ($thatUser->id != $myUser->id))
		{
			// Unset the session variables used for registering authenticators (security precaution).
			Joomla::unsetSessionVar('registration_user_id', 'plg_system_webauthn');
			Joomla::unsetSessionVar('publicKeyCredentialCreationOptions', 'plg_system_webauthn');

			// Politely tell the presumed hacker trying to abuse this callback to go away.
			throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_USER'));
		}

		// Get the credentials repository object. It's outside the try-catch because I also need it to display the GUI.
		$credentialRepository = new CredentialRepository;

		// Try to validate the browser data. If there's an error I won't save anything and pass the message to the GUI.
		try
		{
			/** @var CMSApplication $app */
			$app   = Factory::getApplication();
			$input = $app->input;

			// Retrieve the data sent by the device
			$data = $input->get('data', '', 'raw');

			$publicKeyCredentialSource = CredentialsCreation::validateAuthenticationData($data);

			if (!\is_object($publicKeyCredentialSource) || !($publicKeyCredentialSource instanceof PublicKeyCredentialSource))
			{
				throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_ATTESTED_DATA'));
			}

			$credentialRepository->saveCredentialSource($publicKeyCredentialSource);
		}
		catch (Exception $e)
		{
			$error                  = $e->getMessage();
			$publicKeyCredentialSource = null;
		}

		// Unset the session variables used for registering authenticators (security precaution).
		Joomla::unsetSessionVar('registration_user_id', 'plg_system_webauthn');
		Joomla::unsetSessionVar('publicKeyCredentialCreationOptions', 'plg_system_webauthn');

		// Render the GUI and return it
		$layoutParameters = [
			'user'        => $thatUser,
			'allow_add'   => $thatUser->id == $myUser->id,
			'credentials' => $credentialRepository->getAll($thatUser->id),
		];

		if (isset($error) && !empty($error))
		{
			$layoutParameters['error'] = $error;
		}

		return Joomla::renderLayout('plugins.system.webauthn.manage', $layoutParameters);
	}
}
