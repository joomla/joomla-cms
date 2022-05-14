<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.fixed
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Twofactorauth\Fixed\Extension;

use Joomla\CMS\Language\Text;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Table\TfaTable;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Input\Input;
use RuntimeException;

/**
 * TJoomla! Two Factor Authentication using a fixed code.
 *
 * Requires a static string (password), different for each user. It effectively works as a second
 * password. The fixed code is stored hashed, like a regular password.
 *
 * This is NOT to be used on production sites. It serves as a demonstration plugin and as a template
 * for developers to create their own custom Two Factor Authentication plugins.
 *
 * @since __DEPLOY_VERSION__
 */
class Fixed extends CMSPlugin implements SubscriberInterface
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
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * The TFA Method name handled by this plugin
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $tfaMethodName = 'fixed';

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
	 * Gets the identity of this TFA Method
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorGetMethod(Event $event): void
	{
		$this->setResult($event,
			new MethodDescriptor(
				[
					'name'      => $this->tfaMethodName,
					'display'   => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_DISPLAYEDAS'),
					'shortinfo' => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_SHORTINFO'),
					'image'     => 'media/PLG_TWOFACTORAUTH_fixed/images/fixed.svg',
				]
			)
		);
	}

	/**
	 * Returns the information which allows Joomla to render the Captive TFA page. This is the page
	 * which appears right after you log in and asks you to validate your login with TFA.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorCaptive(Event $event): void
	{
		/**
		 * @var   TfaTable $record The record currently selected by the user.
		 */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			return;
		}

		$this->setResult(
			$event,
			new CaptiveRenderOptions(
				[
					// Custom HTML to display above the TFA form
					'pre_message'  => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_PREMESSAGE'),
					// How to render the TFA code field. "input" (HTML input element) or "custom" (custom HTML)
					'field_type'   => 'input',
					// The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
					'input_type'   => 'password',
					// Placeholder text for the HTML input box. Leave empty if you don't need it.
					'placeholder'  => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_PLACEHOLDER'),
					// Label to show above the HTML input box. Leave empty if you don't need it.
					'label'        => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_LABEL'),
					// Custom HTML. Only used when field_type = custom.
					'html'         => '',
					// Custom HTML to display below the TFA form
					'post_message' => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_POSTMESSAGE'),
				]
			)
		);
	}

	/**
	 * Returns the information which allows Joomla to render the TFA setup page. This is the page
	 * which allows the user to add or modify a TFA Method for their user account. If the record
	 * does not correspond to your plugin return an empty array.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorGetSetup(Event $event): void
	{
		/** @var TfaTable $record The #__loginguard_tfa record currently selected by the user. */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			return;
		}

		// Load the options from the record (if any)
		$options = $this->decodeRecordOptions($record);

		/**
		 * Return the parameters used to render the GUI.
		 *
		 * Some TFA Methods need to display a different interface before and after the setup. For example, when setting
		 * up Google Authenticator or a hardware OTP dongle you need the user to enter a TFA code to verify they are in
		 * possession of a correctly configured device. After the setup is complete you don't want them to see that
		 * field again. In the first state you could use the tabular_data to display the setup values, pre_message to
		 * display the QR code and field_type=input to let the user enter the TFA code. In the second state do the same
		 * BUT set field_type=custom, set html='' and show_submit=false to effectively hide the setup form from the
		 * user.
		 */
		$this->setResult(
			$event,
			new SetupRenderOptions(
				[
					'default_title' => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_DEFAULTTITLE'),
					'pre_message'   => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_SETUP_PREMESSAGE'),
					'field_type'    => 'input',
					'input_type'    => 'password',
					// phpcs:ignore
					'input_value'   => $options->fixed_code,
					'placeholder'   => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_PLACEHOLDER'),
					'label'         => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_LABEL'),
					'post_message'  => Text::_('PLG_TWOFACTORAUTH_FIXED_LBL_SETUP_POSTMESSAGE'),
				]
			)
		);
	}

	/**
	 * Parse the input from the TFA setup page and return the configuration information to be saved to the database. If
	 * the information is invalid throw a RuntimeException to signal the need to display the editor page again. The
	 * message of the exception will be displayed to the user. If the record does not correspond to your plugin return
	 * an empty array.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void The configuration data to save to the database
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorSaveSetup(Event $event): void
	{
		/**
		 * @var TfaTable $record The record currently selected by the user.
		 * @var Input    $input  The user input you are going to take into account.
		 */
		$record = $event['record'];
		$input  = $event['input'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			return;
		}

		// Load the options from the record (if any)
		$options = $this->decodeRecordOptions($record);

		// Merge with the submitted form data
		// phpcs:ignore
		$code = $input->get('code', $options->fixed_code, 'raw');

		// Make sure the code is not empty
		if (empty($code))
		{
			throw new RuntimeException(Text::_('PLG_TWOFACTORAUTH_FIXED_ERR_EMPTYCODE'));
		}

		// Return the configuration to be serialized
		$this->setResult($event, ['fixed_code' => $code]);
	}

	/**
	 * Validates the Two Factor Authentication code submitted by the user in the Captive Two Step Verification page. If
	 * the record does not correspond to your plugin return FALSE.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorValidate(Event $event): void
	{
		/**
		 * @var   TfaTable    $record The TFA Method's record you're validating against
		 * @var   User        $user   The user record
		 * @var   string|null $code   The submitted code
		 */
		$record = $event['record'];
		$user   = $event['user'];
		$code   = $event['code'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			$this->setResult($event, false);

			return;
		}

		// Load the options from the record (if any)
		$options = $this->decodeRecordOptions($record);

		// Double check the TFA Method is for the correct user
		// phpcs:ignore
		if ($user->id != $record->user_id)
		{
			$this->setResult($event, false);

			return;
		}

		// Check the TFA code for validity
		// phpcs:ignore
		$this->setResult($event, hash_equals($options->fixed_code, $code ?? ''));
	}

	/**
	 * Decodes the options from a record into an options object.
	 *
	 * @param   TfaTable  $record  The record to decode options for
	 *
	 * @return  object
	 * @since __DEPLOY_VERSION__
	 */
	private function decodeRecordOptions(TfaTable $record): object
	{
		$options = [
			'fixed_code' => '',
		];

		if (!empty($record->options))
		{
			$recordOptions = $record->options;

			$options = array_merge($options, $recordOptions);
		}

		return (object) $options;
	}

	/**
	 * Add a result to an event
	 *
	 * @param   Event  $event   The event to add a result to
	 * @param   mixed  $return  The result value to add to the event
	 *
	 * @return void
	 * @since __DEPLOY_VERSION__
	 */
	private function setResult(Event $event, $return)
	{
		$result   = $event->getArgument('result', []) ?: [];
		$result[] = $return;

		$event->setArgument('result', $result);
	}
}
