<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Webauthn\Extension;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Event\MultiFactor\Captive;
use Joomla\CMS\Event\MultiFactor\GetMethod;
use Joomla\CMS\Event\MultiFactor\GetSetup;
use Joomla\CMS\Event\MultiFactor\SaveSetup;
use Joomla\CMS\Event\MultiFactor\Validate;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Event\SubscriberInterface;
use Joomla\Input\Input;
use Joomla\Plugin\Multifactorauth\Webauthn\Helper\Credentials;
use RuntimeException;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Joomla Multi-factor Authentication plugin for WebAuthn
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
	 * The MFA Method name handled by this plugin
	 *
	 * @var   string
	 * @since  __DEPLOY_VERSION__
	 */
	private $mfaMethodName = 'webauthn';

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
			'onUserMultifactorGetMethod' => 'onUserMultifactorGetMethod',
			'onUserMultifactorCaptive'   => 'onUserMultifactorCaptive',
			'onUserMultifactorGetSetup'  => 'onUserMultifactorGetSetup',
			'onUserMultifactorSaveSetup' => 'onUserMultifactorSaveSetup',
			'onUserMultifactorValidate'  => 'onUserMultifactorValidate',
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
	public function onUserMultifactorGetMethod(GetMethod $event): void
	{
		$event->addResult(
			new MethodDescriptor(
				[
					'name'               => $this->mfaMethodName,
					'display'            => Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_DISPLAYEDAS'),
					'shortinfo'          => Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_SHORTINFO'),
					'image'              => 'media/plg_multifactorauth_webauthn/images/webauthn.svg',
					'allowMultiple'      => true,
					'allowEntryBatching' => true,
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
	 * @throws  Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserMultifactorGetSetup(GetSetup $event): void
	{
		/**
		 * @var   MfaTable $record The record currently selected by the user.
		 */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->mfaMethodName)
		{
			return;
		}

		// Get some values assuming that we are NOT setting up U2F (the key is already registered)
		$submitClass = '';
		$preMessage  = Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_CONFIGURED');
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
			$wam->getRegistry()->addExtensionRegistryFile('PLG_MULTIFACTORAUTH_WEBAUTHN');

			$layoutPath = PluginHelper::getLayoutPath('multifactorauth', 'webauthn', 'register');
			ob_start();
			include $layoutPath;
			$html = ob_get_clean();
			$type = 'custom';

			// Load JS translations
			Text::script('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_HEAD');

			$document->addScriptOptions('com_users.pagetype', 'setup', false);

			// Save the WebAuthn request to the session
			$user                    = Factory::getApplication()->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
			$hiddenData['pkRequest'] = base64_encode(Credentials::requestAttestation($user));

			// Special button handling
			$submitClass = "multifactorauth_webauthn_setup";

			// Message to display
			$preMessage = Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_INSTRUCTIONS');
		}

		$event->addResult(
			new SetupRenderOptions(
				[
					'default_title' => Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_DISPLAYEDAS'),
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
	public function onUserMultifactorSaveSetup(SaveSetup $event): void
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

		$code                = $input->get('code', null, 'base64');
		$session             = $this->app->getSession();
		$registrationRequest = $session->get('plg_multifactorauth_webauthn.publicKeyCredentialCreationOptions', null);

		// If there was no registration request BUT there is a registration response throw an error
		if (empty($registrationRequest) && !empty($code))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// If there is no registration request (and there isn't a registration response) we are just saving the title.
		if (empty($registrationRequest))
		{
			$event->addResult($record->options);

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
			$session->set('plg_multifactorauth_webauthn.publicKeyCredentialCreationOptions', null);
			$session->set('plg_multifactorauth_webauthn.registration_user_id', null);
		}

		// Return the configuration to be serialized
		$event->addResult(
			[
				'credentialId' => base64_encode($publicKeyCredentialSource->getAttestedCredentialData()->getCredentialId()),
				'pubkeysource' => json_encode($publicKeyCredentialSource),
				'counter'      => 0,
			]
		);
	}

	/**
	 * Returns the information which allows Joomla to render the Captive MFA page. This is the page
	 * which appears right after you log in and asks you to validate your login with MFA.
	 *
	 * @param   Captive  $event  The event we are handling
	 *
	 * @return  void
	 * @throws Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserMultifactorCaptive(Captive $event): void
	{
		/**
		 * @var   MfaTable $record The record currently selected by the user.
		 */
		$record = $event['record'];

		// Make sure we are actually meant to handle this Method
		if ($record->method != $this->mfaMethodName)
		{
			return;
		}

		/**
		 * The following code looks stupid. An explanation is in order.
		 *
		 * What we normally want to do is save the authentication data returned by getAuthenticateData into the session.
		 * This is what is sent to the authenticator through the Javascript API and signed. The signature is posted back
		 * to the form as the "code" which is read by onUserMultifactorauthValidate. That Method will read the authentication
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
		 * In case you are wondering, yes, the data is removed from the session in the onUserMultifactorauthValidate Method.
		 * In fact it's the first thing we do after reading it, preventing constant reuse of the same set of challenges.
		 *
		 * That was fun to debug - for "poke your eyes with a rusty fork" values of fun.
		 */

		$session          = $this->app->getSession();
		$pkOptionsEncoded = $session->get('plg_multifactorauth_webauthn.publicKeyCredentialRequestOptions', null);

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

		$document = $this->app->getDocument();
		$wam      = $document->getWebAssetManager();
		$wam->getRegistry()->addExtensionRegistryFile('plg_multifactorauth_webauthn');

		try
		{
			/** @var CMSApplication $app */
			$app = Factory::getApplication();
			$app->getDocument()->addScriptOptions('com_users.authData', base64_encode($pkRequest), false);
			$layoutPath = PluginHelper::getLayoutPath('multifactorauth', 'webauthn', 'validate');
			ob_start();
			include $layoutPath;
			$html = ob_get_clean();
		}
		catch (Exception $e)
		{
			return;
		}

		// Load JS translations
		Text::script('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_HEAD');
		Text::script('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NO_STORED_CREDENTIAL');

		$document->addScriptOptions('com_users.pagetype', 'validate', false);

		$event->addResult(
			new CaptiveRenderOptions(
				[
					// Custom HTML to display above the MFA form
					'pre_message'        => Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_INSTRUCTIONS'),
					// How to render the MFA code field. "input" (HTML input element) or "custom" (custom HTML)
					'field_type'         => 'custom',
					// The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
					'input_type'         => '',
					// Placeholder text for the HTML input box. Leave empty if you don't need it.
					'placeholder'        => '',
					// Label to show above the HTML input box. Leave empty if you don't need it.
					'label'              => '',
					// Custom HTML. Only used when field_type = custom.
					'html'               => $html,
					// Custom HTML to display below the MFA form
					'post_message'       => '',
					// Should I hide the submit button?
					'hide_submit'        => true,
					// Allow authentication against all entries of this MFA Method.
					'allowEntryBatching' => true,
				]
			)
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
	public function onUserMultifactorValidate(Validate $event): void
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
		if ($record->method != $this->mfaMethodName)
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

			$event->addResult(false);

			return;
		}

		$event->addResult(true);
	}
}
