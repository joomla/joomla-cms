<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\Field;

// Protect from unauthorized access
\defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;

/**
 * Custom Joomla Form Field to display the WebAuthn interface
 *
 * @since 4.0.0
 */
class WebauthnField extends FormField
{
	/**
	 * Element name
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $type = 'Webauthn';

	/**
	 * Returns the input field's HTML
	 *
	 * @return  string
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function getInput()
	{
		$userId = $this->form->getData()->get('id', null);

		if (\is_null($userId))
		{
			return Text::_('PLG_SYSTEM_WEBAUTHN_ERR_NOUSER');
		}

		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_NO_BROWSER_SUPPORT', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_SAVE_LABEL', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_CANCEL_LABEL', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_MSG_SAVED_LABEL', true);
		Text::script('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED', true);

		$app                  = Factory::getApplication();
		$credentialRepository = new CredentialRepository;

		$app->getDocument()->getWebAssetManager()
			->registerAndUseScript('plg_system_webauthn.management', 'plg_system_webauthn/management.js', [], ['defer' => true], ['core']);

		return Joomla::renderLayout('plugins.system.webauthn.manage', [
			'user'        => Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId),
			'allow_add'   => $userId == $app->getIdentity()->id,
			'credentials' => $credentialRepository->getAll($userId),
			]
		);
	}
}
