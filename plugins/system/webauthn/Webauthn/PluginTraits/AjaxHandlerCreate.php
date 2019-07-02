<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\PluginTraits;

use Akeeba\Passwordless\Webauthn\CredentialRepository;
use Akeeba\Passwordless\Webauthn\Helper\CredentialsCreation;
use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Exception;
use Joomla\CMS\Language\Text;
use RuntimeException;
use Webauthn\AttestedCredentialData;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Ajax handler for akaction=create
 *
 * Handles the browser postback for the credentials creation flow
 */
trait AjaxHandlerCreate
{
	public function onAjaxWebauthnCreate()
	{
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
		$thatUser     = Joomla::getUser($storedUserId);
		$myUser = Joomla::getUser();

		if ($thatUser->guest || ($thatUser->id != $myUser->id))
		{
			// Unset the session variables used for registering authenticators (security precaution).
			Joomla::unsetSessionVar('registration_user_id', 'plg_system_webauthn');
			Joomla::unsetSessionVar('publicKeyCredentialCreationOptions', 'plg_system_webauthn');

			// Politely tell the presumed hacker trying to abuse this callback to go away.
			throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_USER'));
		}

		// Get the credentials repository object. It's outside the try-catch because I also need it to display the GUI.
		$credentialRepository = new CredentialRepository();

		// Try to validate the browser data. If there's an error I won't save anything and pass the message to the GUI.
		try
		{
			$input = Joomla::getApplication()->input;

			// Retrieve the data sent by the device
			$data = $input->get('data', '', 'raw');

			$attestedCredentialData = CredentialsCreation::validateAuthenticationData($data);

			if (!is_object($attestedCredentialData) || !($attestedCredentialData instanceof AttestedCredentialData))
			{
				throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_ATTESTED_DATA'));
			}

			$credentialRepository->set($attestedCredentialData);
		}
		catch (Exception $e)
		{
			$error                  = $e->getMessage();
			$attestedCredentialData = null;
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

		return Joomla::renderLayout('akeeba.webauthn.manage', $layoutParameters);
	}
}