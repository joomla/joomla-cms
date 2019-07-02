<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserFactoryInterface;

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
			return Text::_('PLG_SYSTEM_WEBAUTHN_ERR_NOUSER');
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

		$app                  = Factory::getApplication();
		$credentialRepository = new \Joomla\Plugin\System\Webauthn\CredentialRepository();

		return Joomla::renderLayout('plugins.system.webauthn.manage', [
			'user'        => Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($user_id),
			'allow_add'   => $user_id == $app->getIdentity()->id,
			'credentials' => $credentialRepository->getAll($user_id),
		]);
	}
}
