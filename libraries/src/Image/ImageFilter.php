<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Image;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;

/**
 * Class to manipulate an image.
 *
 * @since  1.7.3
 */
abstract class ImageFilter extends \Joomla\Image\ImageFilter
{
	/**
	 * Class constructor.
	 *
	 * @param   resource  $handle  The image resource on which to apply the filter.
	 *
	 * @since  1.7.3
	 */
	public function __construct($handle)
	{
		// Inject the PSR-3 compatible logger in for forward compatibility
		$this->setLogger(Log::createDelegatedLogger());

		parent::__construct($handle);
	}
}
