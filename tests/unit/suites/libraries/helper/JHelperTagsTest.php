<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelperTags.
 */
class JHelperTagsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JHelperTags
	 * @since  3.1
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		$this->object = new JHelperTags;
	}

	/**
	 * Tests the tagItem method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTagItem()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the tagItems method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTagItems()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the unTagItem method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUnTagItem()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTagsId method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagsId()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getItemTags method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetItemTags()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTagItemsQuery method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagItemsQuery()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the explodeTypeAlias method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testExplodeTypeAlias()
	{
		$alias = $this->object->explodeTypeAlias('com_content.category');

		$this->assertEquals(
			$alias[1],
			'category',
			'Assert that the alias is properly exploded and the second key is the item type'
		);
	}

	/**
	 * Tests the getTypeName method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTypeName()
	{
		$name = $this->object->getTypeName('com_content.category');

		$this->assertEquals(
			$name,
			'com_content',
			'Assert that the component name is returned'
		);
	}

	/**
	 * Tests the getContentItemUrl method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetContentItemUrl()
	{
		$url = $this->object->getContentItemUrl('com_content.category', 1);

		$this->assertEquals(
			$url,
			'index.php?option=com_content&view=category&id=1',
			'Assert that the view URL is properly returned'
		);
	}

	/**
	 * Tests the getTagUrl method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagUrl()
	{
		$url = $this->object->getTagUrl(1);

		$this->assertEquals(
			$url,
			'index.php&option=com_tags&view=tag&id=1',
			'Assert that the tag URL is properly returned'
		);
	}

	/**
	 * Tests the getTableName method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTableName()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTypeId method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTypeId()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTypes method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTypes()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the tagDeleteInstances method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTagDeleteInstances()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the searchTags method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testSearchTags()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the deleteTagData method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testDeleteTagData()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTagTreeArray method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTagTreeArray()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the convertPathsToNames method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testConvertPathsToNames()
	{
		$this->markTestSkipped('Test not implemented.');
	}
}
