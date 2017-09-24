<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

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

		// Check the modal's html structure
		$crawler = new \Symfony\Component\DomCrawler\Crawler($result);
		$element = $crawler->filter('#user-list');

		$this->assertEquals(
			count($element),
			1,
			'Failed to find element with id of "user-list"'
		);

		$this->assertEquals(
			'select',
			$element->nodeName()
		);

		$childElements = $element->children();

		$this->assertEquals(
			count($childElements),
			5
		);

		$optionElement = $childElements->eq(2);

		$this->assertEquals(
			'option',
			$optionElement->nodeName()
		);

		$this->assertEquals(
			'Publisher',
			$optionElement->getNode(0)->textContent
		);

		$this->assertEquals(
			'43',
			$optionElement->attr('value'),
			'Expected a <select> element with id "user-list" containing a child <option value="43">Publisher</option>'
		);

		$this->assertEquals(
			'selected',
			$optionElement->attr('selected')
		);

		$result = JHtmlList::users('user-list', '42');

		// Check the modal's html structure
		$crawler = new \Symfony\Component\DomCrawler\Crawler($result);
		$element = $crawler->filter('#user-list');

		$this->assertEquals(
			count($element),
			1,
			'Failed to find element with id of "user-list"'
		);

		$this->assertEquals(
			'select',
			$element->nodeName()
		);

		$childElements = $element->children();

		$this->assertEquals(
			count($childElements),
			4
		);

		$optionElement = $childElements->eq(1);

		$this->assertEquals(
			'option',
			$optionElement->nodeName()
		);

		$this->assertEquals(
			'Publisher',
			$optionElement->getNode(0)->textContent
		);

		$this->assertEquals(
			'43',
			$optionElement->attr('value'),
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

		// Check the modal's html structure
		$crawler = new \Symfony\Component\DomCrawler\Crawler($result);
		$element = $crawler->filter('#positions');

		$this->assertEquals(
			count($element),
			1,
			'Failed to find element with id of "positions"'
		);

		$this->assertEquals(
			'select',
			$element->nodeName()
		);

		$childElements = $element->children();

		$this->assertEquals(
			count($childElements),
			4
		);

		$optionElement = $childElements->eq(2);

		$this->assertEquals(
			'option',
			$optionElement->nodeName()
		);

		$this->assertEquals(
			'Left',
			$optionElement->getNode(0)->textContent
		);

		$this->assertEquals(
			'left',
			$optionElement->attr('value'),
			'Expected a <select> element with id "user-list" containing a child <option value="left">Left</option>'
		);
	}
}
