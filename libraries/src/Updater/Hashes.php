<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

defined('JPATH_PLATFORM') or die;

/**
 * Data object representing a Hashes given as part of an update's `<shaXXX>` element
 *
 * @since  __DEPLOY_VERSION__
 */
class Hashes
{
	/**
	 * Defines a hash for a full package
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const TYPE_FULL = 'full';

	/**
	 * Defines a hash for a patch package
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const TYPE_PATCH = 'patch';

	/**
	 * Defines a hash for a upgrade package
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const TYPE_UPGRADE = 'upgrade';

	/**
	 * The hash type
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type = self::TYPE_FULL;

	/**
	 * The hash value
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $value;
}
