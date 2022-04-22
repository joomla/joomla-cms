<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Mock JCategories class for unittesting
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class UnittestCategories extends JCategories
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.7.0
	 */
	public function __construct($options = array())
	{
		$options['table'] = '#__content';
		$options['extension'] = 'com_content';
		$options['access'] = false;

		parent::__construct($options);
	}
}
