<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\PluginTraits;

use Akeeba\Passwordless\Webauthn\CredentialRepository;
use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Exception;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Ajax handler for akaction=savelabel
 *
 * Stores a new label for a security key
 */
trait AjaxHandlerSaveLabel
{
	public function onAjaxWebauthnSavelabel(): bool
	{
		// Initialize objects
		$input      = Joomla::getApplication()->input;
		$repository = new CredentialRepository();

		// Retrieve data from the request
		$credential_id = $input->getBase64('credential_id', '');
		$new_label     = $input->getString('new_label', '');

		// Is this a valid credential?
		if (empty($credential_id))
		{
			return false;
		}

		$credential_id = base64_decode($credential_id);

		if (empty($credential_id) || !$repository->has($credential_id))
		{
			return false;
		}

		// Make sure I am editing my own key
		try
		{
			$credential_handle = $repository->getUserHandleFor($credential_id);
			$my_handle         = $repository->getHandleFromUserId(Joomla::getUser()->id);
		}
		catch (Exception $e)
		{
			return false;
		}

		if ($credential_handle !== $my_handle)
		{
			return false;
		}

		// Make sure the new label is not empty
		if (empty($new_label))
		{
			return false;
		}

		// Save the new label
		try
		{
			$repository->setLabel($credential_id, $new_label);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}