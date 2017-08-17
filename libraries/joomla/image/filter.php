<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Image\ImageFilter;

JLog::add('JImageFilter is deprecated, use Joomla\Image\ImageFilter instead.', JLog::WARNING, 'deprecated');

/**
 * Class to manipulate an image.
 *
 * @since       11.3
 * @deprecated  5.0  Use Joomla\Image\ImageFilter instead.
 */
abstract class JImageFilter extends ImageFilter
{
	/**
	 * Class constructor.
	 *
	 * @param   resource  $handle  The image resource on which to apply the filter.
	 *
	 * @since   11.3
	 * @deprecated  5.0  Use Joomla\Image\ImageFilter instead.
	 */
	public function __construct($handle)
	{
		// Inject the PSR-3 compatible logger in for forward compatibility
		$this->setLogger(JLog::createDelegatedLogger());

		parent::__construct($handle);
	}
}
