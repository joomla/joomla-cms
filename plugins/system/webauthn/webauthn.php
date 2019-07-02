<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Akeeba\Passwordless\Webauthn\PluginTraits\AjaxHandler;
use Akeeba\Passwordless\Webauthn\PluginTraits\AjaxHandlerChallenge;
use Akeeba\Passwordless\Webauthn\PluginTraits\AjaxHandlerCreate;
use Akeeba\Passwordless\Webauthn\PluginTraits\AjaxHandlerDelete;
use Akeeba\Passwordless\Webauthn\PluginTraits\AjaxHandlerLogin;
use Akeeba\Passwordless\Webauthn\PluginTraits\AjaxHandlerSaveLabel;
use Akeeba\Passwordless\Webauthn\PluginTraits\ButtonsInModules;
use Akeeba\Passwordless\Webauthn\PluginTraits\ButtonsInUserPage;
use Akeeba\Passwordless\Webauthn\PluginTraits\UserDeletion;
use Akeeba\Passwordless\Webauthn\PluginTraits\UserProfileFields;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;

// Protect from unauthorized access
defined('_JEXEC') or die();

// Register a PSR-4 autoloader for this plugin's classes if necessary
if (!class_exists('Akeeba\\Passwordless\\Webauthn\\Helper\\Joomla', true))
{
	JLoader::registerNamespace('Akeeba\\Passwordless\\Webauthn', __DIR__ . '/Webauthn', false, false, 'psr4');
}

/**
 * Akeeba Passwordless Login plugin providing Webauthn integration.
 *
 * The plugin features are broken down into Traits for the sole purpose of making an otherwise supermassive class
 * somewhat manageable.
 */
class plgSystemWebauthn extends CMSPlugin
{
	// AJAX request handlers
	use AjaxHandler;
	use AjaxHandlerCreate;
	use AjaxHandlerSaveLabel;
	use AjaxHandlerDelete;
	use AjaxHandlerChallenge;
	use AjaxHandlerLogin;

	// Custom user profile fields
	use UserProfileFields;

	// Handle user profile deletion
	use UserDeletion;

	// Add Webauthn buttons
	use ButtonsInModules;
	use ButtonsInUserPage;

	/**
	 * Constructor. Loads the language files as well.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct($subject, array $config = [])
	{
		parent::__construct($subject, $config);

		// Load the language files
		$this->loadLanguage();

		// Register a debug log file writer
		Joomla::addLogger('system');

		// Load the Composer autoloader
		require_once __DIR__ . '/vendor/autoload.php';

		// Setup login module interception
		$this->setupLoginModuleButtons();
		$this->setupUserLoginPageButtons();
	}
}
