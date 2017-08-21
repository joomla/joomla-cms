<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JImageFilterGrayscale is deprecated, use Joomla\Image\Filter\Grayscale instead.', JLog::WARNING, 'deprecated');

/**
 * Image Filter class to transform an image to grayscale.
 *
 * @since       11.3
 * @deprecated  5.0  Use Joomla\Image\Filter\Grayscale instead
 */
class JImageFilterGrayscale extends JImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @deprecated  5.0  Use Joomla\Image\Filter\Grayscale::execute() instead
	 */
	public function execute(array $options = array())
	{
		// Perform the grayscale filter.
		imagefilter($this->handle, IMG_FILTER_GRAYSCALE);
	}
}
