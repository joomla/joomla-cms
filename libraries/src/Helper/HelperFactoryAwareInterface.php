<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a helper factory.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HelperFactoryAwareInterface
{
	/**
	 * Sets the helper factory to use.
	 *
	 * @param   HelperFactory  $helper  The helper factory to use.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHelperFactory(HelperFactory $helper);
}
