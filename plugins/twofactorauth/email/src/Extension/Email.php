<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      Twofactorauth.email
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Twofactorauth\Email\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Encrypt\Totp;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Component\Users\Administrator\Table\TfaTable;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use PHPMailer\PHPMailer\Exception as phpMailerException;
use RuntimeException;
use function count;

/**
 * Joomla! Two Factor Authentication using a Validation Code sent by Email.
 *
 * Requires entering a 6-digit code sent to the user through email. These codes change automatically
 * on a frequency set in the plugin options (30 seconds to 5 minutes, default 2 minutes).
 *
 * @since __DEPLOY_VERSION__
 */
class Email extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Generated OTP length. Constant: 6 numeric digits.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private const CODE_LENGTH = 6;

	/**
	 * Length of the secret key used for generating the OTPs. Constant: 20 characters.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private const SECRET_KEY_LENGTH = 20;

	/**
	 * The CMS application we are running under
	 *
	 * @var   CMSApplication
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Forbid registration of legacy (Joomla 3) event listeners.
	 *
	 * @var    boolean
	 * @since __DEPLOY_VERSION__
	 *
	 * @deprecated
	 */
	protected $allowLegacyListeners = false;

	/**
	 * Autoload this plugin's language files
	 *
	 * @var    boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * The TFA Method name handled by this plugin
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $tfaMethodName = 'email';

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onUserTwofactorGetMethod'            => 'onUserTwofactorGetMethod',
			'onUserTwofactorCaptive'              => 'onUserTwofactorCaptive',
			'onUserTwofactorGetSetup'             => 'onUserTwofactorGetSetup',
			'onUserTwofactorSaveSetup'            => 'onUserTwofactorSaveSetup',
			'onUserTwofactorValidate'             => 'onUserTwofactorValidate',
			'onUserTwofactorBeforeDisplayMethods' => 'onUserTwofactorBeforeDisplayMethods',
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
		$this->setResult(
			$event,
			new MethodDescriptor(
				[
					'name'      => $this->tfaMethodName,
					'display'   => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
					'shortinfo' => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_SHORTINFO'),
					'image'     => 'media/plg_twofactorauth_email/images/email.svg',
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

		// Load the options from the record (if any)
		$options = $this->decodeRecordOptions($record);
		$key     = $options['key'] ?? '';

		// Send an email message with a new code and ask the user to enter it.
		// phpcs:ignore
		$user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($record->user_id);

		try
		{
			$this->sendCode($key, $user);
		}
		catch (Exception $e)
		{
			return;
		}

		$this->setResult($event,
			new CaptiveRenderOptions(
				[
					// Custom HTML to display above the TFA form
					'pre_message'        => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_PRE_MESSAGE'),
					// How to render the TFA code field. "input" (HTML input element) or "custom" (custom HTML)
					'field_type'         => 'input',
					// The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
					'input_type'         => 'number',
					// Placeholder text for the HTML input box. Leave empty if you don't need it.
					'placeholder'        => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_SETUP_PLACEHOLDER'),
					// Label to show above the HTML input box. Leave empty if you don't need it.
					'label'              => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_LABEL'),
					// Custom HTML. Only used when field_type = custom.
					'html'               => '',
					// Custom HTML to display below the TFA form
					'post_message'       => '',
					// Should I hide the default Submit button?
					'hide_submit'        => false,
					// Is this TFA method validating against all configured authenticators of the same type?
					'allowEntryBatching' => false,
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
	 * @throws  Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorGetSetup(Event $event): void
	{
		/** @var TfaTable $record The record currently selected by the user. */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			return;
		}

		// Load the options from the record (if any)
		$options           = $this->decodeRecordOptions($record);
		$key               = $options['key'] ?? '';
		$isKeyAlreadySetup = !empty($key);

		// If there's a key in the session use that instead.
		$session = $this->app->getSession();
		$session->get('plg_twofactorauth_email.emailcode.key', $key);

		// Initialize objects
		$timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
		$totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

		// If there's still no key in the options, generate one and save it in the session
		if (!$isKeyAlreadySetup)
		{
			$key = $totp->generateSecret();

			$session->set('plg_twofactorauth_email.emailcode.key', $key);
			// phpcs:ignore
			$session->set('plg_twofactorauth_email.emailcode.user_id', $record->user_id);

			// phpcs:ignore
			$user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($record->user_id);

			$this->sendCode($key, $user);

			$this->setResult($event,
				new SetupRenderOptions(
					[
						'default_title' => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
						'hidden_data'   => [
							'key' => $key,
						],
						'field_type'    => 'input',
						'input_type'    => 'number',
						'input_value'   => '',
						'placeholder'   => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_SETUP_PLACEHOLDER'),
						'pre_message'   => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_PRE_MESSAGE'),
						'label'         => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_LABEL'),
					]
				)
			);
		}
		else
		{
			$this->setResult($event,
				new SetupRenderOptions(
					[
						'default_title' => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
						'field_type'    => 'custom',
						'html'          => '',
					]
				)
			);
		}
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
		$options           = $this->decodeRecordOptions($record);
		$key               = $options['key'] ?? '';
		$isKeyAlreadySetup = !empty($key);
		$session           = $this->app->getSession();

		// If there is no key in the options fetch one from the session
		if (empty($key))
		{
			$key = $session->get('plg_twofactorauth_email.emailcode.key', null);
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
		$code = $input->getCmd('code');

		if (empty($code) && $isKeyAlreadySetup)
		{
			$this->setResult($event, $options);

			return;
		}

		// In any other case validate the submitted code
		$timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
		$totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);
		$isValid  = $totp->checkCode((string) $key, (string) $code);

		if (!$isValid)
		{
			throw new RuntimeException(Text::_('PLG_TWOFACTORAUTH_EMAIL_ERR_INVALID_CODE'), 500);
		}

		// The code is valid. Unset the key from the session.
		$session->set('plg_twofactorauth_email.emailcode.key', null);

		// Return the configuration to be serialized
		$this->setResult($event, ['key' => $key]);
	}

	/**
	 * Validates the Two Factor Authentication code submitted by the user in the Captive Two Factor
	 * Authentication page. If the record does not correspond to your plugin return FALSE.
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

		// Double check the TFA Method is for the correct user
		// phpcs:ignore
		if ($user->id != $record->user_id)
		{
			$this->setResult($event, false);

			return;
		}

		// Load the options from the record (if any)
		$options = $this->decodeRecordOptions($record);
		$key     = $options['key'] ?? '';

		// If there is no key in the options throw an error
		if (empty($key))
		{
			$this->setResult($event, false);

			return;
		}

		// Check the TFA code for validity
		$timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
		$totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

		$this->setResult($event, $totp->checkCode($key, (string) $code));
	}

	/**
	 * Executes before showing the TFA Methods for the user. Used for the Force Enable feature.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorBeforeDisplayMethods(Event $event): void
	{
		/** @var ?User $user */
		$user = $event['user'];

		// Is the forced enable feature activated?
		if ($this->params->get('force_enable', 0) != 1)
		{
			return;
		}

		// Get second factor Methods for this user
		$userTfaRecords = TfaHelper::getUserTfaRecords($user->id);

		// If there are no Methods go back
		if (count($userTfaRecords) < 1)
		{
			return;
		}

		// If the only Method is backup codes go back
		if (count($userTfaRecords) == 1)
		{
			/** @var TfaTable $record */
			$record = reset($userTfaRecords);

			if ($record->method == 'backupcodes')
			{
				return;
			}
		}

		// If I already have the email Method go back
		$emailRecords = array_filter(
			$userTfaRecords,
			function (TfaTable $record)
			{
				return $record->method == 'email';
			}
		);

		if (count($emailRecords))
		{
			return;
		}

		// Add the email Method
		try
		{
			/** @var MVCFactoryInterface $factory */
			$factory = $this->app->bootComponent('com_users')->getMVCFactory();
			/** @var TfaTable $record */
			$record = $factory->createTable('Tfa', 'Administrator');
			$record->reset();

			$timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
			$totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

			$record->save(
				[
					'method'  => 'email',
					'title'   => Text::_('PLG_TWOFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
					'options' => [
						'key' => ($totp)->generateSecret(),
					],
					'default' => 0,
				]
			);
		}
		catch (Exception $event)
		{
			// Fail gracefully
		}
	}

	/**
	 * Decodes the options from a record into an options object.
	 *
	 * @param   TfaTable  $record  The record to decode
	 *
	 * @return  array
	 * @since   __DEPLOY_VERSION__
	 */
	private function decodeRecordOptions(TfaTable $record): array
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

	/**
	 * Creates a new TOTP code based on secret key $key and sends it to the user via email.
	 *
	 * @param   string     $key   The TOTP secret key
	 * @param   User|null  $user  The Joomla! user to use
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   __DEPLOY_VERSION__
	 */
	private function sendCode(string $key, ?User $user = null)
	{
		static $alreadySent = false;

		// Make sure we have a user
		if (!is_object($user) || !($user instanceof User))
		{
			$user = $this->app->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		}

		if ($alreadySent)
		{
			return;
		}

		$alreadySent = true;

		// Get the API objects
		$timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
		$totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

		// Create the list of variable replacements
		$code = $totp->getCode($key);

		$replacements = [
			'code'     => $code,
			'sitename' => $this->app->get('sitename'),
			'siteurl'  => Uri::base(),
			'username' => $user->username,
			'email'    => $user->email,
			'fullname' => $user->name,
		];

		try
		{
			$jLanguage = $this->app->getLanguage();
			$mailer = new MailTemplate('plg_twofactorauth_email.mail', $jLanguage->getTag());
			$mailer->addRecipient($user->email);
			$mailer->addTemplateData($replacements);
			$mailer->send();
		}
		catch (MailDisabledException | phpMailerException $exception)
		{
			try
			{
				Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
			}
			catch (\RuntimeException $exception)
			{
				$this->app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
			}
		}
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
