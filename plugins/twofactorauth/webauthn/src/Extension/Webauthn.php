<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Twofactorauth\Webauthn\Extension;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Table\TfaTable as TfaTable;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Twofactorauth\Webauthn\Helper\Credentials;
use RuntimeException;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Joomla Two Factor Authentication plugin for WebAuthn
 *
 * @since __DEPLOY_VERSION__
 */
class Webauthn extends CMSPlugin implements SubscriberInterface
{
	/**
	 * The application object
	 *
	 * @var    CMSApplication|SiteApplication|AdministratorApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Auto-load the plugin's language files
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * The TFA Method name handled by this plugin
	 *
	 * @var   string
	 * @since  __DEPLOY_VERSION__
	 */
	private $tfaMethodName = 'webauthn';

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
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
		$this->setResult(
			$event,
			new MethodDescriptor(
				[
					'name'               => $this->tfaMethodName,
					'display'            => Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_DISPLAYEDAS'),
					'shortinfo'          => Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_SHORTINFO'),
					'image'              => 'media/plg_twofactorauth_webauthn/images/webauthn.svg',
					'allowMultiple'      => true,
					'allowEntryBatching' => true,
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
		/**
		 * @var   TfaTable $record The record currently selected by the user.
		 */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			return;
		}

		// Get some values assuming that we are NOT setting up U2F (the key is already registered)
		$submitClass = '';
		$preMessage  = Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_CONFIGURED');
		$type        = 'input';
		$html        = '';
		$hiddenData  = [];

		/**
		 * If there are no authenticators set up yet I need to show a different message and take a different action when
		 * my user clicks the submit button.
		 */
		if (!is_array($record->options) || empty($record->options['credentialId'] ?? ''))
		{
			$document = $this->app->getDocument();
			$wam      = $document->getWebAssetManager();
			$wam->getRegistry()->addExtensionRegistryFile('PLG_TWOFACTORAUTH_WEBAUTHN');
			$wam->useScript('plg_twofactorauth_webauthn.webauthn');

			$layoutPath = PluginHelper::getLayoutPath('twofactorauth', 'webauthn', 'register');
			ob_start();
			include $layoutPath;
			$html = ob_get_clean();
			$type = 'custom';

			// Load JS translations
			Text::script('PLG_TWOFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_HEAD');

			$document->addScriptOptions('com_users.pagetype', 'setup', false);

			// Save the WebAuthn request to the session
			$user                    = Factory::getApplication()->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
			$hiddenData['pkRequest'] = base64_encode(Credentials::requestAttestation($user));

			// Special button handling
			$submitClass = "twofactorauth_webauthn_setup";

			// Message to display
			$preMessage = Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_INSTRUCTIONS');
		}

		$this->setResult(
			$event,
			new SetupRenderOptions(
				[
					'default_title' => Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_DISPLAYEDAS'),
					'pre_message'   => $preMessage,
					'hidden_data'   => $hiddenData,
					'field_type'    => $type,
					'input_type'    => 'hidden',
					'html'          => $html,
					'show_submit'   => false,
					'submit_class'  => $submitClass,
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
		 * @var   TfaTable $record The record currently selected by the user.
		 * @var   Input    $input  The user input you are going to take into account.
		 */
		$record = $event['record'];
		$input  = $event['input'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->tfaMethodName)
		{
			return;
		}

		$code                = $input->get('code', null, 'base64');
		$session             = $this->app->getSession();
		$registrationRequest = $session->get('plg_twofactorauth_webauthn.publicKeyCredentialCreationOptions', null);

		// If there was no registration request BUT there is a registration response throw an error
		if (empty($registrationRequest) && !empty($code))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// If there is no registration request (and there isn't a registration response) we are just saving the title.
		if (empty($registrationRequest))
		{
			$this->setResult($event, $record->options);

			return;
		}

		// In any other case try to authorize the registration
		try
		{
			$publicKeyCredentialSource = Credentials::verifyAttestation($code);
		}
		catch (Exception $err)
		{
			throw new RuntimeException($err->getMessage(), 403);
		}
		finally
		{
			// Unset the request data from the session.
			$session->set('plg_twofactorauth_webauthn.publicKeyCredentialCreationOptions', null);
			$session->set('plg_twofactorauth_webauthn.registration_user_id', null);
		}

		// Return the configuration to be serialized
		$this->setResult(
			$event,
			[
				'credentialId' => base64_encode($publicKeyCredentialSource->getAttestedCredentialData()->getCredentialId()),
				'pubkeysource' => json_encode($publicKeyCredentialSource),
				'counter'      => 0,
			]
		);
	}

	/**
	 * Returns the information which allows Joomla to render the Captive TFA page. This is the page
	 * which appears right after you log in and asks you to validate your login with TFA.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @throws  Exception
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

		/**
		 * The following code looks stupid. An explanation is in order.
		 *
		 * What we normally want to do is save the authentication data returned by getAuthenticateData into the session.
		 * This is what is sent to the authenticator through the Javascript API and signed. The signature is posted back
		 * to the form as the "code" which is read by onUserTwofactorauthValidate. That Method will read the authentication
		 * data from the session and pass it along with the key registration data (from the database) and the
		 * authentication response (the "code" submitted in the form) to the WebAuthn library for validation.
		 *
		 * Validation will work as long as the challenge recorded in the encrypted AUTHENTICATION RESPONSE matches, upon
		 * decryption, the challenge recorded in the AUTHENTICATION DATA.
		 *
		 * I observed that for whatever stupid reason the browser was sometimes sending TWO requests to the server's
		 * Captive login page but only rendered the FIRST. This meant that the authentication data sent to the key had
		 * already been overwritten in the session by the "invisible" second request. As a result the challenge would
		 * not match and we'd get a validation error.
		 *
		 * The code below will attempt to read the authentication data from the session first. If it exists it will NOT
		 * try to replace it (technically it replaces it with a copy of the same data - same difference!). If nothing
		 * exists in the session, however, it WILL store the (random seeded) result of the getAuthenticateData Method.
		 * Therefore the first request to the Captive login page will store a new set of authentication data whereas the
		 * second, "invisible", request will just reuse the same data as the first request, fixing the observed issue in
		 * a way that doesn't compromise security.
		 *
		 * In case you are wondering, yes, the data is removed from the session in the onUserTwofactorauthValidate Method.
		 * In fact it's the first thing we do after reading it, preventing constant reuse of the same set of challenges.
		 *
		 * That was fun to debug - for "poke your eyes with a rusty fork" values of fun.
		 */

		$session          = $this->app->getSession();
		$pkOptionsEncoded = $session->get('plg_twofactorauth_webauthn.publicKeyCredentialRequestOptions', null);

		$force = $this->app->input->getInt('force', 0);

		try
		{
			if ($force)
			{
				throw new RuntimeException('Expected exception (good): force a new key request');
			}

			if (empty($pkOptionsEncoded))
			{
				throw new RuntimeException('Expected exception (good): we do not have a pending key request');
			}

			$serializedOptions = base64_decode($pkOptionsEncoded);
			$pkOptions         = unserialize($serializedOptions);

			if (!is_object($pkOptions) || empty($pkOptions) || !($pkOptions instanceof PublicKeyCredentialRequestOptions))
			{
				throw new RuntimeException('The pending key request is corrupt; a new one will be created');
			}

			$pkRequest = json_encode($pkOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}
		catch (Exception $e)
		{
			// phpcs:ignore
			$pkRequest = Credentials::requestAssertion($record->user_id);
		}

		try
		{
			/** @var CMSApplication $app */
			$app = Factory::getApplication();
			$app->getDocument()->addScriptOptions('com_users.authData', base64_encode($pkRequest), false);
			$layoutPath = PluginHelper::getLayoutPath('twofactorauth', 'webauthn', 'validate');
			ob_start();
			include $layoutPath;
			$html = ob_get_clean();
		}
		catch (Exception $e)
		{
			return;
		}

		$document = $this->app->getDocument();
		$wam      = $document->getWebAssetManager();
		$wam->getRegistry()->addExtensionRegistryFile('plg_twofactorauth_webauthn');
		$wam->useScript('plg_twofactorauth_webauthn.webauthn');

		// Load JS translations
		Text::script('PLG_TWOFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_HEAD');
		Text::script('PLG_TWOFACTORAUTH_WEBAUTHN_ERR_NO_STORED_CREDENTIAL');

		$document->addScriptOptions('com_users.pagetype', 'validate', false);

		$this->setResult(
			$event,
			new CaptiveRenderOptions(
				[
					// Custom HTML to display above the TFA form
					'pre_message'        => Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_INSTRUCTIONS'),
					// How to render the TFA code field. "input" (HTML input element) or "custom" (custom HTML)
					'field_type'         => 'custom',
					// The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
					'input_type'         => '',
					// Placeholder text for the HTML input box. Leave empty if you don't need it.
					'placeholder'        => '',
					// Label to show above the HTML input box. Leave empty if you don't need it.
					'label'              => '',
					// Custom HTML. Only used when field_type = custom.
					'html'               => $html,
					// Custom HTML to display below the TFA form
					'post_message'       => '',
					// Should I hide the submit button?
					'hide_submit'        => true,
					// Allow authentication against all entries of this TFA Method.
					'allowEntryBatching' => true,
				]
			)
		);
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
		 * @var   TfaTable $record The TFA Method's record you're validatng against
		 * @var   User     $user   The user record
		 * @var   string   $code   The submitted code
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

		try
		{
			Credentials::verifyAssertion($code);
		}
		catch (Exception $e)
		{
			try
			{
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			catch (Exception $e)
			{
			}

			$this->setResult($event, false);

			return;
		}

		$this->setResult($event, true);
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
