<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Image\Filter;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Image\ImageFilter;

\JLog::add('JImageFilterEmboss is deprecated, use Joomla\Image\Filter\Emboss instead.', \JLog::WARNING, 'deprecated');

/**
 * Image Filter class to emboss an image.
 *
 * @since       11.3
 * @deprecated  5.0  Use Joomla\Image\Filter\Emboss instead
 */
class Emboss extends ImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @deprecated  5.0  Use Joomla\Image\Filter\Emboss::execute() instead
	 */
	public function execute(array $options = array())
	{
		// Perform the emboss filter.
		imagefilter($this->handle, IMG_FILTER_EMBOSS);
	}
}
