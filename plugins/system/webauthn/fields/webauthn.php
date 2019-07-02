<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Prevent direct access
use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class JFormFieldWebauthn extends FormField
{
	/**
	 * Element name
	 *
	 * @var   string
	 */
	protected $_name = 'Webauthn';

	function getInput()
	{
		$user_id = $this->form->getData()->get('id', null);

		if (is_null($user_id))
		{
			return Joomla::_('PLG_SYSTEM_WEBAUTHN_ERR_NOUSER');
		}

		HTMLHelper::_('script', 'plg_system_webauthn/dist/management.js', [
			'relative'  => true,
			'framework' => true,
		]);

		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_NO_BROWSER_SUPPORT', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_SAVE_LABEL', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_CANCEL_LABEL', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_MSG_SAVED_LABEL', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED', true);

		$credentialRepository = new \Akeeba\Passwordless\Webauthn\CredentialRepository();

		return Joomla::renderLayout('akeeba.webauthn.manage', [
			'user'        => Joomla::getUser($user_id),
			'allow_add'   => $user_id == Joomla::getUser()->id,
			'credentials' => $credentialRepository->getAll($user_id),
		]);
	}
}
