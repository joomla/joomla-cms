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
 * Base install script interface for use by extensions providing helper methods for common behaviors.
 *
 * @since  __DEPLOY_VERSION__
 */
interface InstallerScriptInterface
{
	/**
	 * Function called after the extension is installed.
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function install(InstallerAdapter $parent): bool;

	/**
	 * Function called after the extension is updated.
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function update(InstallerAdapter $parent): bool;

	/**
	 * Function called after the extension is uninstalled.
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function uninstall(InstallerAdapter $parent): bool;

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
	public function preflight(string $type, InstallerAdapter $parent): bool;

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
	public function postflight(string $type, InstallerAdapter $parent): bool;
}
