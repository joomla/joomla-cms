<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\PluginTraits;

use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Add extra fields in the User Profile page.
 *
 * This class only injects the custom form fields. The actual interface is rendered through JFormFieldWebauthn.
 *
 * @see JFormFieldWebauthn::getInput()
 */
trait UserProfileFields
{
	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   Form  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Check we are manipulating a valid form.
		if (!($form instanceof Form))
		{
			return true;
		}

		$name = $form->getName();

		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Get the user ID
		$id = null;

		if (is_array($data))
		{
			$id = isset($data['id']) ? $data['id'] : null;
		}
		elseif (is_object($data) && is_null($data) && ($data instanceof Registry))
		{
			$id = $data->get('id');
		}
		elseif (is_object($data) && !is_null($data))
		{
			$id = isset($data->id) ? $data->id : null;
		}

		$user = Joomla::getUser($id);

		// Make sure the loaded user is the correct one
		if ($user->id != $id)
		{
			return true;
		}

		// Make sure I am either editing myself OR I am a Super User
		if (!Joomla::canEditUser($user))
		{
			return true;
		}

		// Add the fields to the form.
		Joomla::log('system', 'Injecting Akeeba Passwordless Login fields in user profile edit page');
		Form::addFormPath(dirname(__FILE__) . '/../../fields');
		$this->loadLanguage();
		$form->loadFile('webauthn', false);

		return true;
	}

}