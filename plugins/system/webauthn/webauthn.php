<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandler;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerChallenge;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerCreate;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerDelete;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerLogin;
use Joomla\Plugin\System\Webauthn\PluginTraits\AjaxHandlerSaveLabel;
use Joomla\Plugin\System\Webauthn\PluginTraits\ButtonsInModules;
use Joomla\Plugin\System\Webauthn\PluginTraits\ButtonsInUserPage;
use Joomla\Plugin\System\Webauthn\PluginTraits\UserDeletion;
use Joomla\Plugin\System\Webauthn\PluginTraits\UserProfileFields;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;

// Protect from unauthorized access
defined('_JEXEC') or die();

// Register a PSR-4 autoloader for this plugin's classes if necessary
if (!class_exists('Joomla\\Plugin\\System\\Webauthn\\Helper\\Joomla', true))
{
	JLoader::registerNamespace('Joomla\\Plugin\\System\\Webauthn', __DIR__ . '/Webauthn', false, false, 'psr4');
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

		// Setup login module interception
		$this->setupLoginModuleButtons();
		$this->setupUserLoginPageButtons();
	}
}
