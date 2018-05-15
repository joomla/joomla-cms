<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Provider;

defined('_JEXEC') or die;

use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;

/**
 * Media provider interface.
 *
 * @since  4.0.0
 */
interface ProviderInterface
{
	/**
	 * Returns the ID of the provider
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function getID();

	/**
	 * Returns the display name
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function getDisplayName();

	/**
	 * Returns a list of adapters
	 *
	 * @return  AdapterInterface[]
	 *
	 * @since  4.0.0
	 */
	public function getAdapters();
}
