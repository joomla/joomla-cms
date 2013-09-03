<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JApplicationBase class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 *
 * @since       12.2
 */
class JArchiveZipInspector extends JArchiveZip
{
	/**
	 * Test...
	 *
	 * @param   string  $archive      @todo
	 * @param   string  $destination  @todo
	 * @param   array   $options      @todo
	 *
	 * @return mixed
	 */
	public function accessExtractCustom($archive, $destination, array $options = array())
	{
		return parent::extractCustom($archive, $destination, $options);
	}

	/**
	 * Test...
	 *
	 * @param   string  $archive      @todo
	 * @param   string  $destination  @todo
	 * @param   array   $options      @todo
	 *
	 * @return bool
	 */
	public function accessExtractNative($archive, $destination, array $options = array())
	{
		return parent::extractNative($archive, $destination, $options);
	}
}
