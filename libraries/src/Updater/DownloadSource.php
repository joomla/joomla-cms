<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

defined('JPATH_PLATFORM') or die;

/**
 * Data object representing a download source given as part of an update's `<downloads>` element
 *
 * @since  3.8.3
 */
class DownloadSource
{
	/**
	 * Defines a ZIP download package
	 *
	 * @const  string
	 * @since  3.8.3
	 */
	const FORMAT_ZIP = 'zip';

	/**
	 * Defines a full package download type
	 *
	 * @const  string
	 * @since  3.8.3
	 */
	const TYPE_FULL = 'full';

	/**
	 * The download type
	 *
	 * @var    string
	 * @since  3.8.3
	 */
	public $type = self::TYPE_FULL;

	/**
	 * The download file's format
	 *
	 * @var    string
	 * @since  3.8.3
	 */
	public $format = self::FORMAT_ZIP;

	/**
	 * The URL to retrieve the package from
	 *
	 * @var    string
	 * @since  3.8.3
	 */
	public $url;
}
