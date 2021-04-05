<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

\defined('JPATH_PLATFORM') or die;

/**
 * Data object representing a download source given as part of an update's `<downloads>` element
 *
 * @since  3.8.3
 */
class DownloadSource
{
	/**
	 * Defines a BZIP2 download package
	 *
	 * @var    string
	 * @since  3.8.4
	 */
	const FORMAT_TAR_BZIP = 'bz2';

	/**
	 * Defines a TGZ download package
	 *
	 * @var    string
	 * @since  3.8.4
	 */
	const FORMAT_TAR_GZ = 'gz';

	/**
	 * Defines a ZIP download package
	 *
	 * @var    string
	 * @since  3.8.3
	 */
	const FORMAT_ZIP = 'zip';

	/**
	 * Defines a full package download type
	 *
	 * @var    string
	 * @since  3.8.3
	 */
	const TYPE_FULL = 'full';

	/**
	 * Defines a patch package download type
	 *
	 * @var    string
	 * @since  3.8.4
	 */
	const TYPE_PATCH = 'patch';

	/**
	 * Defines an upgrade package download type
	 *
	 * @var    string
	 * @since  3.8.4
	 */
	const TYPE_UPGRADE = 'upgrade';

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
