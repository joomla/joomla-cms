<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

\defined('_JEXEC') or die;

/**
 * Legacy installer script which delegates the methods to the internal instance when possible.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyInstallerScript implements InstallerScriptInterface
{
	private $installerScript;

	public function __construct($installerScript)
	{
		$this->$installerScript = $installerScript;
	}

	/**
	 * Function called after the extension is installed.
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function install(InstallerAdapter $parent): bool
	{
		if (!method_exists($this->installerScript, 'install'))
		{
			return true;
		}

		return $this->installerScript->install($parent);
	}

	/**
	 * Function called after the extension is updated.
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function update(InstallerAdapter $parent): bool
	{
		if (!method_exists($this->installerScript, 'update'))
		{
			return true;
		}

		return $this->installerScript->update($parent);
	}

	/**
	 * Function called after the extension is uninstalled.
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function uninstall(InstallerAdapter $parent): bool
	{
		if (!method_exists($this->installerScript, 'uninstall'))
		{
			return true;
		}

		return $this->installerScript->uninstall($parent);
	}

	/**
	 * Function called before extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function preflight(string $type, InstallerAdapter $parent)
	{
		if (!method_exists($this->installerScript, 'preflight'))
		{
			return true;
		}

		return $this->installerScript->preflight($parent);
	}

	/**
	 * Function called after extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function postflight(string $type, InstallerAdapter $parent)
	{
		if (!method_exists($this->installerScript, 'postflight'))
		{
			return true;
		}

		return $this->installerScript->postflight($parent);
	}
}
