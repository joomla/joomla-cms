<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\PluginTraits;

use Akeeba\Passwordless\Webauthn\Helper\Integration;
use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Exception;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Inserts Webauthn buttons into login modules
 */
trait ButtonsInModules
{
	/**
	 * The names of the login modules to intercept. Default: mod_login
	 *
	 * @var   array
	 */
	protected $loginModules = array('mod_login');

	/**
	 * Should I relocate the passwordless login button next to the Login button in the login module?
	 *
	 * @var   bool
	 */
	protected $relocateButton = true;

	/**
	 * CSS selectors for the relocate feature
	 *
	 * @var   array
	 */
	protected $relocateSelectors = [];

	/**
	 * Do I need to I inject buttons? Automatically detected (i.e. disabled if I'm already logged in).
	 *
	 * @var   bool
	 */
	protected $needButtonInjection = null;

	/**
	 * Set up the login module button injection feature.
	 *
	 * @return  void
	 */
	protected function setupLoginModuleButtons(): void
	{
		// Don't try to set up this feature if we are alraedy logged in
		if (!$this->isButtonInjectionNecessary())
		{
			return;
		}

		// Don't try to set up this feature if we can't figure out if this is a front- or backend page.
		try
		{
			$isAdminPage = Joomla::isAdminPage();
		}
		catch (Exception $e)
		{
			$this->needButtonInjection = false;

			return;
		}

		// Load the plugin options into properties
		$loginModulesParameter   = $isAdminPage ? 'backendloginmodules' : 'loginmodules';
		$defaultModules          = $isAdminPage ? 'mod_login' : 'mod_login';
		$loginModules            = $this->params->get($loginModulesParameter);
		$loginModules            = trim($loginModules);
		$loginModules            = empty($loginModules) ? $defaultModules : $loginModules;
		$loginModules            = explode(',', $loginModules);
		$this->loginModules      = array_map('trim', $loginModules);
		$this->relocateButton    = $this->params->get('relocate', 1);
		$this->relocateSelectors = explode("\n", str_replace(',', "\n", $this->params->get('relocate_selectors', '')));
	}

	/**
	 * Should I enable the substitutions performed by this plugin?
	 *
	 * @return  bool
	 */
	private function isButtonInjectionNecessary(): bool
	{
		if (is_null($this->needButtonInjection))
		{
			$this->needButtonInjection = true;

			if (!Joomla::getUser()->guest)
			{
				$this->needButtonInjection = false;
			}
		}

		return $this->needButtonInjection;
	}

	/**
	 * Intercepts module rendering, appending the Webauthn button to the configured login module.
	 *
	 * @param   object  $module   The module being rendered
	 * @param   object  $attribs  The module rendering attributes
	 *
	 * @throws  Exception
	 */
	public function onRenderModule(&$module, &$attribs): void
	{
		if (!$this->isButtonInjectionNecessary())
		{
			return;
		}

		// We need this convoluted check because the JDocument is not initialized on plugin object construction or even
		// during onAfterInitialize. This is the only safe way to determine the document type.
		static $docType = null;

		if (is_null($docType))
		{
			try
			{
				$document = Joomla::getApplication()->getDocument();
			}
			catch (Exception $e)
			{
				$document = null;
			}

			$docType = (is_null($document)) ? 'error' : $document->getType();

			if ($docType != 'html')
			{
				$this->needButtonInjection = false;

				return;
			}
		}

		// If it's not a module I need to intercept bail out
		if (!in_array($module->module, $this->loginModules))
		{
			return;
		}

		// Append the passwordless login buttons content to the login module
		Joomla::log('system', "Injecting Webauthn passwordless login buttons to {$module->module} module.");

		$options = [
			'relocate' => $this->relocateButton,
			'selectors' => $this->relocateSelectors,
		];

		if (empty($this->relocateSelectors))
		{
			unset($options['selectors']);
		}

		$module->content .= Integration::getLoginButtonHTML($options);
	}
}