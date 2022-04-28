<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\Extension;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\Webauthn\Authentication;
use Joomla\Plugin\System\Webauthn\PluginTraits\AdditionalLoginButtons;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandler;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerChallenge;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerCreate;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerDelete;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerInitCreate;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerLogin;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerSaveLabel;
use Joomla\Plugin\System\Webauthn\PluginTraits\EventReturnAware;
use Joomla\Plugin\System\Webauthn\PluginTraits\UserDeletion;
use Joomla\Plugin\System\Webauthn\PluginTraits\UserProfileFields;

/**
 * WebAuthn Passwordless Login plugin
 *
 * The plugin features are broken down into Traits for the sole purpose of making an otherwise
 * supermassive class somewhat manageable. You can find the Traits inside the Webauthn/PluginTraits
 * folder.
 *
 * @since  4.0.0
 */
class Webauthn extends CMSPlugin implements SubscriberInterface
{
	/**
	 * The CMS application we are running in
	 *
	 * @var   CMSApplication
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The application's database driver object
	 *
	 * @var   DatabaseDriver
	 * @since __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Autoload the language files
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Should I try to detect and register legacy event listeners?
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 *
	 * @deprecated
	 */
	protected $allowLegacyListeners = false;

	/**
	 * The WebAuthn authentication helper object
	 *
	 * @var   Authentication
	 * @since __DEPLOY_VERSION__
	 */
	protected $authenticationHelper;

	// AJAX request handlers
	use AjaxHandler;
	use AjaxHandlerInitCreate;
	use AjaxHandlerCreate;
	use AjaxHandlerSaveLabel;
	use AjaxHandlerDelete;
	use AjaxHandlerChallenge;
	use AjaxHandlerLogin;

	// Custom user profile fields
	use UserProfileFields;

	// Handle user profile deletion
	use UserDeletion;

	// Add WebAuthn buttons
	use AdditionalLoginButtons;

	// Utility methods for setting the events' return values
	use EventReturnAware;

	/**
	 * Constructor. Loads the language files as well.
	 *
	 * @param   DispatcherInterface  $subject    The object to observe
	 * @param   array                $config     An optional associative array of configuration
	 *                                           settings. Recognized key values include 'name',
	 *                                           'group', 'params', 'language (this list is not meant
	 *                                           to be comprehensive).
	 * @param   Authentication|null  $authHelper The WebAuthn helper object
	 *
	 * @since  4.0.0
	 */
	public function __construct(&$subject, array $config = [], Authentication $authHelper = null)
	{
		parent::__construct($subject, $config);

		/**
		 * Note: Do NOT try to load the language in the constructor. This is called before Joomla initializes the
		 * application language. Therefore the temporary Joomla language object and all loaded strings in it will be
		 * destroyed on application initialization. As a result we need to call loadLanguage() in each method
		 * individually, even though all methods make use of language strings.
		 */

		// Register a debug log file writer
		$logLevels = Log::ERROR | Log::CRITICAL | Log::ALERT | Log::EMERGENCY;

		if (\defined('JDEBUG') && JDEBUG)
		{
			$logLevels = Log::ALL;
		}

		Log::addLogger([
				'text_file'         => "webauthn_system.php",
				'text_entry_format' => '{DATETIME}	{PRIORITY} {CLIENTIP}	{MESSAGE}',
			], $logLevels, ["webauthn.system"]
		);

		$this->authenticationHelper = $authHelper ?? (new Authentication);
	}

	/**
	 * Returns the Authentication helper object
	 *
	 * @return Authentication
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAuthenticationHelper(): Authentication
	{
		return $this->authenticationHelper;
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		try
		{
			$app = Factory::getApplication();
		}
		catch (\Exception $e)
		{
			return [];
		}

		if (!$app->isClient('site') && !$app->isClient('administrator'))
		{
			return [];
		}

		return [
			'onAjaxWebauthn'           => 'onAjaxWebauthn',
			'onAjaxWebauthnChallenge'  => 'onAjaxWebauthnChallenge',
			'onAjaxWebauthnCreate'     => 'onAjaxWebauthnCreate',
			'onAjaxWebauthnDelete'     => 'onAjaxWebauthnDelete',
			'onAjaxWebauthnInitcreate' => 'onAjaxWebauthnInitcreate',
			'onAjaxWebauthnLogin'      => 'onAjaxWebauthnLogin',
			'onAjaxWebauthnSavelabel'  => 'onAjaxWebauthnSavelabel',
			'onUserAfterDelete'        => 'onUserAfterDelete',
			'onUserLoginButtons'       => 'onUserLoginButtons',
			'onContentPrepareForm'     => 'onContentPrepareForm',
			'onContentPrepareData'     => 'onContentPrepareData',
		];
	}
}
