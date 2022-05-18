<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.totp
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Totp\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Encrypt\Totp as TotpHelper;
use Joomla\CMS\Event\MultiFactor\Captive;
use Joomla\CMS\Event\MultiFactor\GetMethod;
use Joomla\CMS\Event\MultiFactor\GetSetup;
use Joomla\CMS\Event\MultiFactor\SaveSetup;
use Joomla\CMS\Event\MultiFactor\Validate;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Input\Input;
use RuntimeException;

/**
 * Joomla! Multi-factor Authentication using Google Authenticator TOTP Plugin
 *
 * @since  3.2
 */
class Totp extends CMSPlugin implements SubscriberInterface
{
	/**
	 * The application we are running under.
	 *
	 * @var    CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * The MFA Method name handled by this plugin
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $mfaMethodName = 'totp';

	/**
	 * Should I try to detect and register legacy event listeners?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 *
	 * @deprecated
	 */
	protected $allowLegacyListeners = false;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onUserTwofactorGetMethod' => 'onUserTwofactorGetMethod',
			'onUserTwofactorCaptive'   => 'onUserTwofactorCaptive',
			'onUserTwofactorGetSetup'  => 'onUserTwofactorGetSetup',
			'onUserTwofactorSaveSetup' => 'onUserTwofactorSaveSetup',
			'onUserTwofactorValidate'  => 'onUserTwofactorValidate',
		];
	}

	/**
	 * Gets the identity of this MFA Method
	 *
	 * @param   GetMethod  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorGetMethod(GetMethod $event): void
	{
		$event->addResult(
			new MethodDescriptor(
				[
					'name'      => $this->mfaMethodName,
					'display'   => Text::_('PLG_MULTIFACTORAUTH_TOTP_METHOD_TITLE'),
					'shortinfo' => Text::_('PLG_MULTIFACTORAUTH_TOTP_SHORTINFO'),
					'image'     => 'media/plg_multifactorauth_totp/images/totp.svg',
				]
			)
		);
	}

	/**
	 * Returns the information which allows Joomla to render the Captive MFA page. This is the page
	 * which appears right after you log in and asks you to validate your login with MFA.
	 *
	 * @param   Captive  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorCaptive(Captive $event): void
	{
		/**
		 * @var   MfaTable $record The record currently selected by the user.
		 */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method !== $this->mfaMethodName)
		{
			return;
		}

		$event->addResult(
			new CaptiveRenderOptions(
				[
					// Custom HTML to display above the MFA form
					'pre_message'  => '',
					// How to render the MFA code field. "input" (HTML input element) or "custom" (custom HTML)
					'field_type'   => 'input',
					// The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
					'input_type'   => 'number',
					// Placeholder text for the HTML input box. Leave empty if you don't need it.
					'placeholder'  => '',
					// Label to show above the HTML input box. Leave empty if you don't need it.
					'label'        => Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_LABEL'),
					// Custom HTML. Only used when field_type = custom.
					'html'         => '',
					// Custom HTML to display below the MFA form
					'post_message' => '',
				]
			)
		);
	}

	/**
	 * Returns the information which allows Joomla to render the MFA setup page. This is the page
	 * which allows the user to add or modify a MFA Method for their user account. If the record
	 * does not correspond to your plugin return an empty array.
	 *
	 * @param   GetSetup  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorGetSetup(GetSetup $event): void
	{
		/**
		 * @var   MfaTable $record The record currently selected by the user.
		 */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method !== $this->mfaMethodName)
		{
			return;
		}

		$totp = new TotpHelper;

		// Load the options from the record (if any)
		$options      = $this->decodeRecordOptions($record);
		$key          = $options['key'] ?? '';
		$session      = $this->app->getSession();
		$isConfigured = !empty($key);

		// If there's a key in the session use that instead.
		$sessionKey = $session->get('com_users.totp.key', null);

		if (!empty($sessionKey))
		{
			$key = $sessionKey;
		}

		// If there's still no key in the options, generate one and save it in the session
		if (empty($key))
		{
			$key = $totp->generateSecret();
			$session->set('com_users.totp.key', $key);
		}

		// Generate a QR code for the key
		// phpcs:ignore
		$user     = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($record->user_id);
		$hostname = Uri::getInstance()->toString(['host']);
		$otpURL   = sprintf("otpauth://totp/%s@%s?secret=%s", $user->username, $hostname, $key);
		$document = $this->app->getDocument();
		$wam      = $document->getWebAssetManager();

		$document->addScriptOptions('plg_multifactorauth_totp.totp.qr', $otpURL);

		$wam->getRegistry()->addExtensionRegistryFile('plg_multifactorauth_totp');
		$wam->useScript('plg_multifactorauth_totp.setup');

		$event->addResult(
			new SetupRenderOptions(
				[
					'default_title' => Text::_('PLG_MULTIFACTORAUTH_TOTP_METHOD_TITLE'),
					'pre_message'   => Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_INSTRUCTIONS'),
					'table_heading' => Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_HEADING'),
					'tabular_data'  => [
						'' => '<h5>' . Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_SUBHEAD') . '</h5>',
						Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_KEY')  => $key,
						Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_QR')   => "<span id=\"users-mfa-totp-qrcode\" />",
						Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_LINK')
							=> Text::sprintf('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_LINK_TEXT', $otpURL) .
							'<br/><small>' . Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_TABLE_LINK_NOTE') . '</small>',
					],
					'hidden_data'   => [
						'key' => $key,
					],
					'field_type'    => $isConfigured ? 'custom' : 'input',
					'input_type'    => 'number',
					'input_value'   => '',
					'placeholder'   => Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_SETUP_PLACEHOLDER'),
					'label'         => Text::_('PLG_MULTIFACTORAUTH_TOTP_LBL_LABEL'),
				]
			)
		);
	}

	/**
	 * Parse the input from the MFA setup page and return the configuration information to be saved to the database. If
	 * the information is invalid throw a RuntimeException to signal the need to display the editor page again. The
	 * message of the exception will be displayed to the user. If the record does not correspond to your plugin return
	 * an empty array.
	 *
	 * @param   SaveSetup  $event  The event we are handling
	 *
	 * @return  void The configuration data to save to the database
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorSaveSetup(SaveSetup $event): void
	{
		/**
		 * @var   MfaTable $record The record currently selected by the user.
		 * @var   Input    $input  The user input you are going to take into account.
		 */
		$record = $event['record'];
		$input  = $event['input'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->mfaMethodName)
		{
			return;
		}

		// Load the options from the record (if any)
		$options    = $this->decodeRecordOptions($record);
		$optionsKey = $options['key'] ?? '';
		$key        = $optionsKey;
		$session    = $this->app->getSession();

		// If there is no key in the options fetch one from the session
		if (empty($key))
		{
			$key = $session->get('com_users.totp.key', null);
		}

		// If there is still no key in the options throw an error
		if (empty($key))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		/**
		 * If the code is empty but the key already existed in $options someone is simply changing the title / default
		 * Method status. We can allow this and stop checking anything else now.
		 */
		$code = $input->getInt('code');

		if (empty($code) && !empty($optionsKey))
		{
			$event->addResult($options);

			return;
		}

		// In any other case validate the submitted code
		$totp    = new TotpHelper;
		$isValid = $totp->checkCode($key, $code);

		if (!$isValid)
		{
			throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_TOTP_ERR_VALIDATIONFAILED'), 500);
		}

		// The code is valid. Unset the key from the session.
		$session->set('com_users.totp.key', null);

		// Return the configuration to be serialized
		$event->addResult(
			[
				'key' => $key,
			]
		);
	}

	/**
	 * Validates the Multi-factor Authentication code submitted by the user in the Multi-Factor
	 * Authentication page. If the record does not correspond to your plugin return FALSE.
	 *
	 * @param   Validate  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorValidate(Validate $event): void
	{
		/**
		 * @var   MfaTable $record The MFA Method's record you're validatng against
		 * @var   User     $user   The user record
		 * @var   string   $code   The submitted code
		 */
		$record = $event['record'];
		$user   = $event['user'];
		$code   = $event['code'];

		// Make sure we are actually meant to handle this Method
		if ($record->method !== $this->mfaMethodName)
		{
			$event->addResult(false);

			return;
		}

		// Double check the MFA Method is for the correct user
		// phpcs:ignore
		if ($user->id != $record->user_id)
		{
			$event->addResult(false);

			return;
		}

		// Load the options from the record (if any)
		$options = $this->decodeRecordOptions($record);
		$key     = $options['key'] ?? '';

		// If there is no key in the options throw an error
		if (empty($key))
		{
			$event->addResult(false);

			return;
		}

		// Check the MFA code for validity
		$event->addResult((new TotpHelper)->checkCode($key, $code));
	}

	/**
	 * Decodes the options from a record into an options object.
	 *
	 * @param   MfaTable  $record  The record to decode options for
	 *
	 * @return  array
	 * @since   __DEPLOY_VERSION__
	 */
	private function decodeRecordOptions(MfaTable $record): array
	{
		$options = [
			'key' => '',
		];

		if (!empty($record->options))
		{
			$recordOptions = $record->options;

			$options = array_merge($options, $recordOptions);
		}

		return $options;
	}
}
