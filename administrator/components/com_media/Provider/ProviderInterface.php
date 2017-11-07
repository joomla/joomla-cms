<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Provider;

defined('_JEXEC') or die;

use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;

/**
 * Media provider interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ProviderInterface
{
	/**
	 * Returns the ID of the provider
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getID();

	/**
	 * Returns the display name
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getDisplayName();

	/**
	 * Returns a list of adapters
	 *
	 * @return  AdapterInterface[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAdapters();
}
