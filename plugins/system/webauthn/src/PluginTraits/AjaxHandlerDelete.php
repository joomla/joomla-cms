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
	 * @return  boolean
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function onAjaxWebauthnDelete(): bool
	{
		// Load the language files
		$this->loadLanguage();

		// Initialize objects
		/** @var CMSApplication $app */
		$app        = Factory::getApplication();
		$input      = $app->input;
		$repository = new CredentialRepository;

		// Retrieve data from the request
		$credentialId = $input->getBase64('credential_id', '');

		// Is this a valid credential?
		if (empty($credentialId))
		{
			return false;
		}

		$credentialId = base64_decode($credentialId);

		if (empty($credentialId) || !$repository->has($credentialId))
		{
			return false;
		}

		// Make sure I am editing my own key
		try
		{
			$credentialHandle = $repository->getUserHandleFor($credentialId);
			$myHandle         = $repository->getHandleFromUserId($app->getIdentity()->id);
		}
		catch (Exception $e)
		{
			return false;
		}

		if ($credentialHandle !== $myHandle)
		{
			return false;
		}

		// Delete the record
		try
		{
			$repository->remove($credentialId);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}
