<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlListTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');

		return $dataSet;
	}

	/**
	 * Tests the JHtmlList::users method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUsers()
	{
		$result = JHtmlList::users('user-list', '43', '1');

		// Build the container to check the <select> element
		$matcher = array(
			'id'    => 'user-list',
			'tag'   => 'select',
			'child' => array(
				'tag'        => 'option',
				'content'    => 'Publisher',
				'attributes' => array('selected' => 'selected', 'value' => '43')
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <select> element with id "user-list" containing a child <option value="43" selected="selected">Publisher</option>'
		);

		$result = JHtmlList::users('user-list', '42');

		// Build the container to check the <select> element
		$matcher = array(
			'id'    => 'user-list',
			'tag'   => 'select',
			'child' => array(
				'tag'        => 'option',
				'content'    => 'Publisher',
				'attributes' => array('value' => '43')
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <select> element with id "user-list" containing a child <option value="43">Publisher</option>'
		);
	}

	/**
	 * Tests the JHtmlList::positions method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testPositions()
	{
		$result = JHtmlList::positions('position-list', 'center', null, '1', '1', '1', '1', 'positions');

		// Build the container to check the <select> element
		$matcher = array(
			'id'    => 'positions',
			'tag'   => 'select',
			'child' => array(
				'tag'        => 'option',
				'content'    => 'Left',
				'attributes' => array('value' => 'left')
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <select> element with id "user-list" containing a child <option value="left">Left</option>'
		);
	}
}
