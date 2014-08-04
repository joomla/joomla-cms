<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for Email cloaking plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 * @since       3.4
 */
class PlgContentEmailcloak extends TestCaseDatabase
{
	/**
	 * Tests JCategories::getInstance()
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testCloak()
	{
		$dispatcher = JEventDispatcher::getInstance();

		$plugin->type = 'emailcloak';
		$plugin->name = 'emailcloak';

		$instance = new $className($dispatcher, (array) ($plugin));

		$this->assertInstanceOf(
			'JCategoryNode',
			$instance
		);
	}
}
