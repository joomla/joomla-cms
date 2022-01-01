<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

\defined('JPATH_PLATFORM') or die;

/**
 * Trait for allowing calls that should have been deprecated in 4.0 but are still used.
 *
 * @since  __DEPLOY_VERSION__
 *
 * @deprecated  5.0  Will be removed in Joomla 5.0 - You have been warned!
 */
trait DeprecatedClientAwareHelper
{
	/**
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isAdmin(): bool
	{
		$this->_raiseDeprecatedNotice(__METHOD__);

		return ($this instanceof AdministratorApplication);
	}

	/**
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isSite(): bool
	{
		$this->_raiseDeprecatedNotice(__METHOD__);

		return ($this instanceof SiteApplication);
	}

	/**
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isCLI(): bool
	{
		$this->_raiseDeprecatedNotice(__METHOD__);

		return ($this instanceof ConsoleApplication);
	}

	/**
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isApi(): bool
	{
		$this->_raiseDeprecatedNotice(__METHOD__);

		return ($this instanceof ApiApplication);
	}

	/**
	 * @param   string  $function  The function that is deprecated
	 *
	 * @return  void
	 */
	private function _raiseDeprecatedNotice($function)
	{
		@trigger_error(
			sprintf(
				'%s() is deprecated and will be removed in Joomla 5.0',
				$function
			),
			E_USER_DEPRECATED
		);
	}
}
