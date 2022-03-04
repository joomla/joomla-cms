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
	/**
	 * @var    \stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	private $installerScript;

	/**
	 * @param   \stdClass  $installerScript  The script instance
	 */
	public function __construct($installerScript)
	{
		$this->installerScript = $installerScript;
	}

	/**
	 * Function called after the extension is installed.
	 *
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function install(InstallerAdapter $adapter): bool
	{
		if (!method_exists($this->installerScript, 'install'))
		{
			return true;
		}

		return (bool) $this->installerScript->install($adapter);
	}

	/**
	 * Function called after the extension is updated.
	 *
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function update(InstallerAdapter $adapter): bool
	{
		if (!method_exists($this->installerScript, 'update'))
		{
			return true;
		}

		return (bool) $this->installerScript->update($adapter);
	}

	/**
	 * Function called after the extension is uninstalled.
	 *
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function uninstall(InstallerAdapter $adapter): bool
	{
		if (!method_exists($this->installerScript, 'uninstall'))
		{
			return true;
		}

		return (bool) $this->installerScript->uninstall($adapter);
	}

	/**
	 * Function called before extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function preflight(string $type, InstallerAdapter $adapter): bool
	{
		if (!method_exists($this->installerScript, 'preflight'))
		{
			return true;
		}

		return (bool) $this->installerScript->preflight($type, $adapter);
	}

	/**
	 * Function called after extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function postflight(string $type, InstallerAdapter $adapter): bool
	{
		if (!method_exists($this->installerScript, 'postflight'))
		{
			return true;
		}

		return (bool) $this->installerScript->postflight($type, $adapter);
	}

	/**
	 * Sets the variable to the internal script.
	 *
	 * @param   string $name   The name of the variable
	 * @param   mixed  $value  The value of the variable
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __set(string $name, $value)
    {
        $this->installerScript->$name=$value;
    }

	/**
	 * Returns the variable from the internal script.
	 *
	 * @param   string $name  The name of the variable
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
    public function __get(string $name)
    {
		return $this->installerScript->$name;
    }

	/**
	 * Calls the function with the given name on the internal script.
	 *
	 * @param   string $name       The name of the function
	 * @param   array  $arguments  The arguments
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
    public function __call(string $name, array $arguments)
    {
		return call_user_func([$this->installerScript, $name], $arguments);
    }
}
