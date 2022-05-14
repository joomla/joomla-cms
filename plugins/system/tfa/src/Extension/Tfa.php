<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.tfa
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Tfa\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Joomla! Two Factor Authentication
 *
 * @since __DEPLOY_VERSION__
 */
class Tfa extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var   CMSApplication
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var   DatabaseDriver
	 * @since __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Forbid registration of legacy (Joomla 3) event listeners.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $allowLegacyListeners = false;

	/**
	 * User groups for which Two Step Verification is never applied
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	private $neverTFAUserGroups = [];

	/**
	 * User groups for which Two Step Verification is mandatory
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	private $forceTFAUserGroups = [];

	/**
	 * Should I do a Captive Login after a silent login?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	private $doTfaOnSilentLogin = false;

	/**
	 * Silent login response types
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	private $silentLoginResponseTypes = [];

	/**
	 * Should I redirect the user to the TFA setup page after logging in?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	private $doRedirectOnLogin = false;

	/**
	 * Custom URL to redirect instead of the TFA setup page after logging in.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $redirectOnLoginUrl = '';

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRoute'      => 'onAfterRoute',
			'onUserAfterLogin'  => 'onUserAfterLogin',
			'onUserAfterDelete' => 'onUserAfterDelete',
		];
	}

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  $subject  The object to observe.
	 * @param   array                $config   Configuration settings.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);

		$cParams = ComponentHelper::getParams('com_users');

		$this->neverTFAUserGroups = $cParams->get('neverTFAUserGroups', []);
		$this->neverTFAUserGroups = is_array($this->neverTFAUserGroups) ? $this->neverTFAUserGroups : [];

		$this->forceTFAUserGroups = $cParams->get('forceTFAUserGroups', []);
		$this->forceTFAUserGroups = is_array($this->forceTFAUserGroups) ? $this->forceTFAUserGroups : [];

		$this->doTfaOnSilentLogin = $cParams->get('tfaonsilent', 0) == 1;

		$silentResponseTypes = array_map(
			'trim',
			explode(',', $cParams->get('silentresponses', '') ?: '')
		);
		$this->silentLoginResponseTypes = $silentResponseTypes ?: ['cookie', 'passwordless'];

		$this->doRedirectOnLogin  = $cParams->get('tfaredirectonlogin', 0) == 1;
		$this->redirectOnLoginUrl = $cParams->get('tfaredirecturl', '') ?: '';
	}

	/**
	 * Gets triggered right after Joomla has finished with the SEF routing, before it dipatches the
	 * application (loads components).
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRoute(Event $event): void
	{
		if (!($this->app->isClient('administrator')) && !($this->app->isClient('site')))
		{
			return;
		}

		// This also checks if we are in the site/administrator app and the user is logged in.
		if (!$this->willNeedRedirect())
		{
			return;
		}

		// Make sure we are logged in
		try
		{
			$user = $this->app->getIdentity();
		}
		catch (Exception $e)
		{
			return;
		}

		if ($user->guest)
		{
			return;
		}

		// We only kick in when the user has actually set up TFA or must definitely enable TFA.
		$needsTFA     = $this->needsTFA($user);
		$disabledTSV  = $this->disabledTSV($user);
		$mandatoryTSV = $this->mandatoryTSV($user);
		$session      = $this->app->getSession();

		if ($needsTFA && !$disabledTSV)
		{
			/**
			 * Saves the current URL as the return URL if all of the following conditions apply
			 * - It is not a URL to com_users' TFA feature itself
			 * - A return URL does not already exist, is imperfect or external to the site
			 *
			 * If no return URL has been set up and the current URL is com_users' TFA feature
			 * we will save the home page as the redirect target.
			 */
			$returnUrl       = $session->get('com_users.return_url', '');
			$imperfectReturn = $session->get('com_users.imperfect_return', false);

			if ($imperfectReturn || empty($returnUrl) || !Uri::isInternal($returnUrl))
			{
				if (!$this->isTfaPage())
				{
					$session->set(
						'com_users.return_url',
						Uri::getInstance()->toString(['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'])
					);
				}
				elseif (empty($returnUrl))
				{
					$session->set('com_users.return_url', Uri::base());
				}
			}

			// Redirect
			$captiveUrl = $session->get('com_users.captiveUrl') ?:
				Route::_('index.php?option=com_users&view=captive', false);
			$session->remove('com_users.captiveUrl');

			$this->app->redirect($captiveUrl, 307);

			return;
		}

		// If we're here someone just logged in but does not have TFA set up. Just flag him as logged in and continue.
		$session->set('com_users.tfa_checked', 1);

		// If we don't have TFA set up yet AND the user plugin had set up a redirection we will honour it
		$redirectionUrl = $session->get('com_users.postloginredirect', null);

		// If the user is in a group that requires TFA we will redirect them to the setup page
		if (!$needsTFA && $mandatoryTSV)
		{
			// First unset the flag to make sure the redirection will apply until they conform to the mandatory TFA
			$session->set('com_users.tfa_checked', 0);

			// Now set a flag which forces rechecking TSV for this user
			$session->set('com_users.recheck_mandatory_tsv', 1);

			// Then redirect them to the setup page
			$this->redirectToTSVSetup();
		}

		if (!$needsTFA && $redirectionUrl && !$disabledTSV)
		{
			$session->set('com_users.postloginredirect', null);

			$this->app->redirect($redirectionUrl);
		}
	}

	/**
	 * Hooks on the Joomla! login event. Detects silent logins and disables the Two Step Verification Captive page in
	 * this case.
	 *
	 * Moreover, it will save the redirection URL and the Captive URL which is necessary in Joomla 4. You see, in Joomla
	 * 4 having unified sessions turned on makes the backend login redirect you to the frontend of the site AFTER
	 * logging in, something which would cause the Captive page to appear in the frontend and redirect you to the public
	 * frontend homepage after successfully passing the Two Step verification process.
	 *
	 * @param   Event   $event   The event we are handling
	 *
	 * @return void
	 * @since  __DEPLOY_VERSION__
	 */
	public function onUserAfterLogin(Event $event): void
	{
		if (!($this->app->isClient('administrator')) && !($this->app->isClient('site')))
		{
			return;
		}

		/**
		 * @var   array $options Passed by Joomla. user: a User object; responseType: string, authentication response type.
		 */
		[$options] = $event->getArguments();

		$this->saveRedirectionUrlToSession();
		$this->disableTfaOnSilentLogin($options);
		$this->redirectToTFASetup($options);
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   Event   $event   The event we are handling
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterDelete(Event $event): void
	{
		/**
		 * @var array  $user    Holds the user data
		 * @var bool   $success True if user was successfully stored in the database
		 * @var string $msg     Message
		 */
		[$user, $success, $msg] = $event->getArguments();
		$result = $event->getArgument('result') ?: [];

		if (!$success)
		{
			$result[] = false;
			$event->setArgument('result', $result);

			return;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if (!$userId)
		{
			$result[] = true;
			$event->setArgument('result', $result);

			return;
		}

		$db = $this->db;

		// Delete user profile records
		$profileKey = 'tfa.%';
		$query      = $db->getQuery(true)
			->delete($db->quoteName('#__user_profiles'))
			->where($db->quoteName('user_id') . ' = :userId')
			->where($db->quoteName('profile_key') . ' LIKE :profileKey')
			->bind(':userId', $userId, ParameterType::INTEGER)
			->bind(':profileKey', $profileKey, ParameterType::STRING);

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// No sweat if it failed
		}

		// Delete TFA records
		$query = $db->getQuery(true)
			->delete($db->qn('#__user_tfa'))
			->where($db->quoteName('user_id') . ' = :userId')
			->bind(':userId', $userId, ParameterType::INTEGER);

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// No sweat if it failed
		}

		$result[] = true;
		$event->setArgument('result', $result);
	}

	/**
	 * Does the current user need to complete TFA authentication before being allowed to access the site?
	 *
	 * @param   User   $user   The user object
	 *
	 * @return  boolean
	 * @since   __DEPLOY_VERSION__
	 */
	private function needsTFA(User $user): bool
	{
		// TODO Automatically migrate from legacy TFA

		// Get the user's TFA records
		$records = TfaHelper::getUserTfaRecords($user->id);

		// No TFA Methods? Then we obviously don't need to display a Captive login page.
		if (count($records) < 1)
		{
			return false;
		}

		// Let's get a list of all currently active TFA Methods
		$tfaMethods = TfaHelper::getTfaMethods();

		// If not TFA Method is active we can't really display a Captive login page.
		if (empty($tfaMethods))
		{
			return false;
		}

		// Get a list of just the Method names
		$methodNames = [];

		foreach ($tfaMethods as $tfaMethod)
		{
			$methodNames[] = $tfaMethod['name'];
		}

		// Filter the records based on currently active TFA Methods
		foreach ($records as $record)
		{
			if (in_array($record->method, $methodNames))
			{
				// We found an active Method. Show the Captive page.
				return true;
			}
		}

		// No viable TFA Method found. We won't show the Captive page.
		return false;
	}

	/**
	 * Does the user belong in a group indicating TSV should be disabled for them?
	 *
	 * @param   User   $user  The user we are checking for
	 *
	 * @return  boolean
	 * @since   __DEPLOY_VERSION__
	 */
	private function disabledTSV(User $user): bool
	{
		// If the user belongs to a "never check for TSV" user group they are exempt from TSV
		$userGroups             = $user->getAuthorisedGroups();
		$belongsToTSVUserGroups = array_intersect($this->neverTFAUserGroups, $userGroups);

		return !empty($belongsToTSVUserGroups);
	}

	/**
	 * Does the user belong in a group indicating TSV is required for them?
	 *
	 * @param   User   $user  The user we are checking for
	 *
	 * @return  boolean
	 * @since   __DEPLOY_VERSION__
	 */
	private function mandatoryTSV(User $user): bool
	{
		// If the user belongs to a "never check for TSV" user group they are exempt from TSV
		$userGroups             = $user->getAuthorisedGroups();
		$belongsToTSVUserGroups = array_intersect($this->forceTFAUserGroups, $userGroups);

		return !empty($belongsToTSVUserGroups);
	}

	/**
	 * Redirect the user to the Two Step Verification Method setup page.
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	private function redirectToTSVSetup(): void
	{
		// If we are in a com_users' TFA page do not redirect
		if ($this->isTfaPage())
		{
			return;
		}

		// Otherwise redirect to the TFA setup page after enqueueing a message
		$url = Route::_('index.php?option=com_users&view=methods');
		$this->app->redirect($url, 307);
	}

	/**
	 * Check whether we'll need to do a redirection to the Captive page.
	 *
	 * @return  boolean
	 * @throws  Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function willNeedRedirect(): bool
	{
		$isAdmin = $this->app->isClient('administrator');

		/**
		 * We only kick in if the session flag is not set AND the user is not flagged for monitoring of their TSV status
		 *
		 * In case a user belongs to a group which requires TSV to be always enabled and they logged in without having
		 * TSV enabled we have the recheck flag. This prevents the user from enabling and immediately disabling TSV,
		 * circumventing the requirement for TSV.
		 */
		$session    = $this->app->getSession();
		$tfaChecked = $session->get('com_users.tfa_checked', 0) != 0;
		$tfaRecheck = $session->get('com_users.recheck_mandatory_tsv', 0) != 0;

		if ($tfaChecked && !$tfaRecheck)
		{
			return false;
		}

		// Make sure we are logged in
		try
		{
			$user = $this->app->getIdentity();
		}
		catch (Exception $e)
		{
			// This would happen if we are in CLI or under an old Joomla! version. Either case is not supported.
			return false;
		}

		// The plugin only needs to kick in when you have logged in
		if (empty($user) || $user->guest)
		{
			return false;
		}

		/**
		 * Special handling when the requireReset flag is set on the user account.
		 *
		 * Joomla checks the requireReset flag on the user account in the application's doExecute Method. If it is set
		 * it will call CMSApplication::checkUserRequireReset() which issues a redirection for the user to reset their
		 * password.
		 *
		 * One easy option here is to say "if the user must reset their password don't show the TFA Captive page"
		 * Unfortunately, that would be a bad idea because of the naive and insecure manner Joomla goes about the forced
		 * password reset. Instead of going through the actual password reset (“Forgot your password?”) page it instead
		 * redirects the user the user profile editor page! This allows the logged in user to view and change everything
		 * in the user profile, including disabling and changing the 2SV options. Considering that forced password reset
		 * is meant to be primarily used when we suspect that the user's account has been compromised this creates a
		 * grave security risk. The attacker in possession of the username and password can trick a Super User into
		 * forcing a password reset, thereby allowing them to bypass Two Step Verification and take over the user
		 * account.
		 *
		 * Instead, we unset the requireReset user flag for the duration of the page load when this Method here is
		 * called. This prevents Joomla from redirecting. As a result you need to go through Two Step Verification as
		 * per usual. Once you do that the tfa_checked flag is set in the session and this Method never reaches this
		 * point of execution where we unset the requireReset flag. Therefore Joomla now sees the requireReset flag and
		 * shows you the user profile edit page. Now it's safe to do so since you have already proven your identity by
		 * means of Two Step Verification i.e. there's no doubt we should let you make any kind of user account change.
		 *
		 * @see \Joomla\CMS\Application\SiteApplication::doExecute()
		 * @see \Joomla\CMS\Application\CMSApplication::checkUserRequireReset()
		 */
		if ($user->requireReset || 0)
		{
			$user->requireReset = 0;
		}

		// If we are in the administrator section we only kick in when the user has backend access privileges
		if ($isAdmin && !$user->authorise('core.login.admin'))
		{
			return false;
		}

		$needsTFA = $this->needsTFA($user);

		if ($tfaChecked && $tfaRecheck && $needsTFA)
		{
			return false;
		}

		// We only kick in if the option and task are not the ones of the Captive page
		if ($this->isTfaPage())
		{
			return false;
		}

		$fallbackView = $this->app->input->getCmd('controller', '');
		$option       = strtolower($this->app->input->getCmd('option', ''));
		$task         = strtolower($this->app->input->getCmd('task', ''));
		$view         = strtolower($this->app->input->getCmd('view', $fallbackView));

		// Allow the frontend user to log out (in case they forgot their TFA code or something)
		if (!$isAdmin && ($option == 'com_users') && in_array($task, ['user.logout', 'user.menulogout']))
		{
			return false;
		}

		// Allow the backend user to log out (in case they forgot their TFA code or something)
		if ($isAdmin && ($option == 'com_login') && ($task == 'logout'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Is this a page concerning the Two Factor Authentication feature?
	 *
	 * @return boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private function isTfaPage(): bool
	{
		if (!$this->app->isClient('site') && $this->app->isClient('administrator'))
		{
			return false;
		}

		$option = $this->app->input->get('option', null);
		$task    = $this->app->input->get('task', null);
		$view    = $this->app->input->get('view', null);

		if ($option !== 'com_users')
		{
			return false;
		}

		$allowedViews = ['captive', 'method', 'methods', 'callback'];
		$allowedTasks = [
			'captive.display', 'captive.captive', 'captive.validate',
			'method.display', 'method.add', 'method.edit', 'method.regenbackupcodes', 'method.delete', 'method.save',
			'methods.display', 'methods.disable', 'methods.dontshowthisagain',
		];

		return in_array($view, $allowedViews) || in_array($task, $allowedTasks);
	}

	/**
	 * Save the current URL as the post-TFA redirection URL in the session
	 *
	 * @return void
	 * @since  __DEPLOY_VERSION__
	 */
	private function saveRedirectionUrlToSession(): void
	{
		$session = $this->app->getSession();

		// Save the current URL and mark it as an imperfect return (we'll fall back to it if all else fails)
		$returnUrl = $session->get('com_users.return_url', '') ?:
			Uri::getInstance()->toString(['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']);
		$session->set('com_users.return_url', $returnUrl);
		$session->set('com_users.imperfect_return', true);

		// Set up the correct Captive URL
		$captiveUrl = $session->get('com_users.captiveUrl') ?:
			Route::_('index.php?option=com_users&view=captive', false);
		$session->set('com_users.captiveUrl', $captiveUrl);
	}

	/**
	 * Detect silent logins and disable TFA if the relevant com_users option is set.
	 *
	 * @param   array  $options  The array of login options and login result
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	private function disableTfaOnSilentLogin(array $options): void
	{
		// Should I show 2SV even on silent logins? Default: 1 (yes, show)
		if ($this->doTfaOnSilentLogin)
		{
			return;
		}

		// Make sure I have a valid user
		/** @var User $user */
		$user = $options['user'];

		if (!is_object($user) || !($user instanceof User) || $user->guest)
		{
			return;
		}

		// Only proceed if this is not a silent login
		if (!in_array(strtolower($options['responseType'] ?? ''), $this->silentLoginResponseTypes))
		{
			return;
		}

		// Set the flag indicating that TFA is already checked.
		$this->app->getSession()->set('com_users.tfa_checked', 1);
	}

	/**
	 * Conditionally redirect to the TFA page after logging in.
	 *
	 * @param   array  $options  The Joomla login options array
	 *
	 * @return void
	 * @since  __DEPLOY_VERSION__
	 */
	private function redirectToTFASetup(array $options): void
	{
		// Make sure the option to redirect is set
		if (!$this->doRedirectOnLogin)
		{
			return;
		}

		// Make sure I have a valid user
		/** @var User $user */
		$user = $options['user'];

		if (!is_object($user) || !($user instanceof User))
		{
			return;
		}

		/**
		 * If the user already has 2SV enabled and we need to show the Captive page we won't
		 * redirect them to the 2SV setup page, of course.
		 *
		 * If the user has already asked us to not show him the 2SV setup page we have to honour
		 * their wish.
		 */
		if ($this->needsTFA($user)
			|| $this->hasDoNotShowAgainFlag($user))
		{
			return;
		}

		// Get the redirection URL to the 2SV setup page or custom redirection per plugin configuration
		$url = $this->redirectOnLoginUrl ?:
			Route::_('index.php?option=com_users&view=methods&layout=firsttime', false);

		// Prepare to redirect
		$this->app->getSession()->set('com_users.postloginredirect', $url);
	}

	/**
	 * Does the user have a "don't show this again" flag?
	 *
	 * @param   User   $user   The user to check
	 *
	 * @return  boolean
	 * @since   __DEPLOY_VERSION__
	 */
	private function hasDoNotShowAgainFlag(User $user): bool
	{
		$profileKey = 'tfa.dontshow';
		$db         = $this->db;
		$query      = $db->getQuery(true)
			->select($db->quoteName('profile_value'))
			->from($db->quoteName('#__user_profiles'))
			->where($db->quoteName('user_id') . ' = :userId')
			->where($db->quoteName('profile_key') . ' = :profileKey')
			->bind(':userId', $user->id, ParameterType::INTEGER)
			->bind(':profileKey', $profileKey, ParameterType::STRING);

		try
		{
			$result = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			$result = 1;
		}

		return $result == 1;
	}
}
