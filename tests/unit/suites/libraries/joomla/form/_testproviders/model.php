<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Model class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JModelTest extends JModelBase
{
	public function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		$options = array(
			(object) array(
				'value' => 'red',
				'text' => 'red'
			),
			(object) array(
				'value' => 'blue',
				'text' => 'blue'
			)
		);

		return $options;
	}

}
