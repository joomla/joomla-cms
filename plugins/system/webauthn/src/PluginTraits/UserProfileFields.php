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
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use Joomla\Registry\Registry;

/**
 * Add extra fields in the User Profile page.
 *
 * This class only injects the custom form fields. The actual interface is rendered through
 * JFormFieldWebauthn.
 *
 * @see     JFormFieldWebauthn::getInput()
 *
 * @since   4.0.0
 */
trait UserProfileFields
{
	/**
	 * User object derived from the displayed user profile data.
	 *
	 * This is required to display the number and names of authenticators already registered when
	 * the user displays the profile view page.
	 *
	 * @var   User|null
	 * @since 4.0.0
	 */
	private static $userFromFormData = null;

	/**
	 * HTMLHelper method to render the WebAuthn user profile field in the profile view page.
	 *
	 * Instead of showing a nonsensical "Website default" label next to the field, this method
	 * displays the number and names of authenticators already registered by the user.
	 *
	 * This static method is set up for use in the onContentPrepareData method of this plugin.
	 *
	 * @param   mixed  $value  Ignored. The WebAuthn profile field is virtual, it doesn't have a
	 *                         stored value. We only use it as a proxy to render a sub-form.
	 *
	 * @return  string
	 */
	public static function renderWebauthnProfileField($value): string
	{
		if (\is_null(self::$userFromFormData))
		{
			return '';
		}

		$credentialRepository = new CredentialRepository;
		$credentials          = $credentialRepository->getAll(self::$userFromFormData->id);
		$authenticators       = array_map(
			function (array $credential) {
				return $credential['label'];
			},
			$credentials
		);

		return Text::plural('PLG_SYSTEM_WEBAUTHN_FIELD_N_AUTHENTICATORS_REGISTERED', \count($authenticators), implode(', ', $authenticators));
	}

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// This feature only applies to HTTPS sites.
		if (!Uri::getInstance()->isSsl())
		{
			return true;
		}

		$name = $form->getName();

		$allowedForms = [
			'com_users.user', 'com_users.profile', 'com_users.registration',
		];

		if (!\in_array($name, $allowedForms))
		{
			return true;
		}

		// Get the user object
		$user = $this->getUserFromData($data);

		// Make sure the loaded user is the correct one
		if (\is_null($user))
		{
			return true;
		}

		// Make sure I am either editing myself OR I am a Super User
		if (!Joomla::canEditUser($user))
		{
			return true;
		}

		// Add the fields to the form.
		Joomla::log(
			'system',
			'Injecting WebAuthn Passwordless Login fields in user profile edit page'
		);
		Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');
		$this->loadLanguage();
		$form->loadFile('webauthn', false);

		return true;
	}

	/**
	 * Get the user object based on the ID found in the provided user form data
	 *
	 * @param   array|object|null  $data  The user form data
	 *
	 * @return  User|null  A user object or null if no match is found
	 *
	 * @throws  Exception
	 * @since   4.0.0
	 */
	private function getUserFromData($data): ?User
	{
		$id = null;

		if (\is_array($data))
		{
			$id = $data['id'] ?? null;
		}
		elseif (\is_object($data) && ($data instanceof Registry))
		{
			$id = $data->get('id');
		}
		elseif (\is_object($data))
		{
			$id = $data->id ?? null;
		}

		$user = empty($id) ? Factory::getApplication()->getIdentity() : Factory::getContainer()
			->get(UserFactoryInterface::class)
			->loadUserById($id);

		// Make sure the loaded user is the correct one
		if ($user->id != $id)
		{
			return null;
		}

		return $user;
	}

	/**
	 * @param   string|null        $context  The context for the data
	 * @param   array|object|null  $data     An object or array containing the data for the form.
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	public function onContentPrepareData(?string $context, $data): bool
	{
		if (!\in_array($context, ['com_users.profile', 'com_users.user']))
		{
			return true;
		}

		self::$userFromFormData = $this->getUserFromData($data);

		if (!HTMLHelper::isRegistered('users.webauthnWebauthn'))
		{
			HTMLHelper::register('users.webauthn', [__CLASS__, 'renderWebauthnProfileField']);
		}

		return true;
	}
}
