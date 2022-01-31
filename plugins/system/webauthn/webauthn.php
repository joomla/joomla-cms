<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use Joomla\Plugin\System\Webauthn\PluginTraits\AdditionalLoginButtons;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandler;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerChallenge;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerCreate;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerDelete;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerLogin;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerSaveLabel;
use Joomla\Plugin\System\Webauthn\PluginTraits\UserDeletion;
use Joomla\Plugin\System\Webauthn\PluginTraits\UserProfileFields;

/**
 * WebAuthn Passwordless Login plugin
 *
 * The plugin features are broken down into Traits for the sole purpose of making an otherwise supermassive class
 * somewhat manageable. You can find the Traits inside the Webauthn/PluginTraits folder.
 *
 * @since  4.0.0
 */
class PlgSystemWebauthn extends CMSPlugin
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

	// Add WebAuthn buttons
	use AdditionalLoginButtons;

	/**
	 * Constructor. Loads the language files as well.
	 *
	 * @param   DispatcherInterface  $subject  The object to observe
	 * @param   array                $config   An optional associative array of configuration
	 *                                         settings. Recognized key values include 'name',
	 *                                         'group', 'params', 'language (this list is not meant
	 *                                         to be comprehensive).
	 *
	 * @since  4.0.0
	 */
	public function __construct(&$subject, array $config = [])
	{
		parent::__construct($subject, $config);

		/**
		 * Note: Do NOT try to load the language in the constructor. This is called before Joomla initializes the
		 * application language. Therefore the temporary Joomla language object and all loaded strings in it will be
		 * destroyed on application initialization. As a result we need to call loadLanguage() in each method
		 * individually, even though all methods make use of language strings.
		 */

		// Register a debug log file writer
		Joomla::addLogger('system');
	}
}
