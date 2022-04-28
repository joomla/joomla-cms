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

/**
 * Ajax handler for akaction=savelabel
 *
 * Stores a new label for a security key
 *
 * @since   4.0.0
 */
trait AjaxHandlerSaveLabel
{
	/**
	 * Handle the callback to rename an authenticator
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   4.0.0
	 */
	public function onAjaxWebauthnSavelabel(Event $event): void
	{
		// Initialize objects
		$input      = $this->app->input;
		$repository = $this->authenticationHelper->getCredentialsRepository();

		// Retrieve data from the request
		$credentialId = $input->getBase64('credential_id', '');
		$newLabel     = $input->getString('new_label', '');

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
			$credentialHandle = $repository->getUserHandleFor($credentialId);
			$user             = $this->app->getIdentity() ?? new User;
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

		// Make sure the new label is not empty
		if (empty($newLabel))
		{
			$this->returnFromEvent($event, false);

			return;
		}

		// Save the new label
		try
		{
			$repository->setLabel($credentialId, $newLabel);
		}
		catch (Exception $e)
		{
			$this->returnFromEvent($event, false);

			return;
		}

		$this->returnFromEvent($event, true);
	}
}
