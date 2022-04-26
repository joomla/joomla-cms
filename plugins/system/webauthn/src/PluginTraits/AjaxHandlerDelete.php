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
use Joomla\CMS\User\User;
use Joomla\Event\Event;
use Joomla\Plugin\System\Webauthn\CredentialRepository;

/**
 * Ajax handler for akaction=savelabel
 *
 * Deletes a security key
 *
 * @since  4.0.0
 */
trait AjaxHandlerDelete
{
	/**
	 * Handle the callback to remove an authenticator
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @since   4.0.0
	 */
	public function onAjaxWebauthnDelete(Event $event): void
	{
		// Initialize objects
		$input      = $this->app->input;
		$repository = new CredentialRepository;

		// Retrieve data from the request
		$credentialId = $input->getBase64('credential_id', '');

		// Is this a valid credential?
		if (empty($credentialId))
		{
			$this->returnFromEvent($event, false);

			return;
		}

		$credentialId = base64_decode($credentialId);

		if (empty($credentialId) || !$repository->has($credentialId))
		{
			$this->returnFromEvent($event, false);

			return;
		}

		// Make sure I am editing my own key
		try
		{
			$user             = $this->app->getIdentity() ?? new User;
			$credentialHandle = $repository->getUserHandleFor($credentialId);
			$myHandle         = $repository->getHandleFromUserId($user->id);
		}
		catch (Exception $e)
		{
			$this->returnFromEvent($event, false);

			return;
		}

		if ($credentialHandle !== $myHandle)
		{
			$this->returnFromEvent($event, false);

			return;
		}

		// Delete the record
		try
		{
			$repository->remove($credentialId);
		}
		catch (Exception $e)
		{
			$this->returnFromEvent($event, false);

			return;
		}

		$this->returnFromEvent($event, true);
	}
}
