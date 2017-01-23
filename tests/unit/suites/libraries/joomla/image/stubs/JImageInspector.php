<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JImage class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Image
 * @since       11.3
 */
class JImageInspector extends JImage
{
	/**
	 * @var    JImageFilter  A mock image filter to be returned from getFilterInstance().
	 * @since  11.3
	 */
	public $mockFilter;

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $type  The image filter type to get.
	 *
	 * @return  JImageFilter
	 *
	 * @since   11.3
	 * @throws  RuntimeException
	 */
	public function getFilterInstance($type)
	{
		if ($this->mockFilter)
		{
			return $this->mockFilter;
		}
		else
		{
			return parent::getFilterInstance($type);
		}
	}
}
