<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Cms\Feed;

use InvalidArgumentException;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedLink;
use Joomla\CMS\Feed\FeedPerson;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for FeedEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class FeedEntryTest extends UnitTestCase
{
	/**
	 * @var FeedEntry
	 */
	protected $feedEntry;

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::setUp()
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->feedEntry = new FeedEntry;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   12.3
	 */
	protected function tearDown()
	{
		unset($this->feedEntry);

		parent::tearDown();
	}

	/**
	 * Tests the FeedEntry::__get method when the property has been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicGetterWithDefaultProperty()
	{
		$this->assertEquals('', $this->feedEntry->uri);
	}

	/**
	 * Tests the FeedEntry::__get method when the property has not been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicGetterWithUnknownProperty()
	{
		$this->assertEquals(null, $this->feedEntry->unknown);
	}

	/**
	 * Tests the FeedEntry::__set method with updatedDate with a string.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetUpdatedDateWithString()
	{
		$this->feedEntry->updatedDate = 'May 2nd, 1967';

		$this->assertInstanceOf(Date::class, $this->feedEntry->updatedDate);
	}

	/**
	 * Tests the FeedEntry::__set method with updatedDate with a JDate object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicSetUpdatedDateWithDateObject()
	{
		$date = new Date('October 12, 2011');
		$this->feedEntry->updatedDate = $date;

		$this->assertInstanceOf(Date::class, $this->feedEntry->updatedDate);
		$this->assertSame($date, $this->feedEntry->updatedDate);
	}

	/**
	 * Tests the FeedEntry::__set method with a person object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetAuthorWithPerson()
	{
		$name = 'Brian Kernighan';
		$email = 'brian@example.com';
		$person = new FeedPerson($name, $email);

		$this->feedEntry->author = $person;

		$this->assertInstanceOf(FeedPerson::class, $this->feedEntry->author);
		$this->assertEquals($name, $this->feedEntry->author->name);
		$this->assertEquals($email, $this->feedEntry->author->email);
	}

	/**
	 * Tests the FeedEntry::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testSetAuthorWithInvalidAuthor()
	{
		$this->feedEntry->author = 'Jack Sprat';
	}

	/**
	 * Tests the FeedEntry::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testSetSourceWithInvalidSource()
	{
		$this->feedEntry->source = 'Outer Space';
	}

	/**
	 * Tests the FeedEntry::__set method with a disallowed property.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testSetCategoriesWithInvalidProperty()
	{
		$this->feedEntry->categories = 'Can\'t touch this';
	}

	/**
	 * Tests the FeedEntry::__set method with a typical property.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicSetter()
	{
		$value = 'test';
		$this->feedEntry->testValue = $value;

		$this->assertEquals($value, $this->feedEntry->testValue);
	}

	/**
	 * Tests the FeedEntry::addCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddSingleCategory()
	{
		$name = 'category';
		$uri = 'http://www.example.com';
		$this->feedEntry->addCategory($name, $uri);

		$this->assertCount(1, $this->feedEntry->categories);
		$this->assertArrayHasKey($name, $this->feedEntry->categories);
		$this->assertEquals($uri, $this->feedEntry->categories[$name]);
	}

	/**
	 * Tests the FeedEntry::addCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddMultipleCategories()
	{
		$categories = [
			['name' => 'category1', 'uri' => 'http://www.example1.com'],
			['name' => 'category2', 'uri' => 'http://www.example2.com'],
			['name' => 'category3', 'uri' => 'http://www.example3.com'],
		];

		foreach ($categories as $category)
		{
			$this->feedEntry->addCategory($category['name'], $category['uri']);
		}

		$this->assertCount(3, $this->feedEntry->categories);
		foreach ($categories as $category)
		{
			$this->assertArrayHasKey($category['name'], $this->feedEntry->categories);
			$this->assertEquals($category['uri'], $this->feedEntry->categories[$category['name']]);
		}
	}

	/**
	 * Tests the FeedEntry::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddNewContributor()
	{
		$name = 'Dennis Ritchie';
		$email = 'dennis.ritchie@example.com';

		$this->feedEntry->addContributor($name, $email);

		$this->assertCount(1, $this->feedEntry->contributors);
		$this->assertEquals($name, $this->feedEntry->contributors[0]->name);
		$this->assertEquals($email, $this->feedEntry->contributors[0]->email);
	}

	/**
	 * Tests the FeedEntry::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddExistingContributor()
	{
		$name = 'Dennis Ritchie';
		$email = 'dennis.ritchie@example.com';

		$this->feedEntry->addContributor($name, $email);
		$this->feedEntry->addContributor($name, $email);

		$this->assertCount(1, $this->feedEntry->contributors);
		$this->assertEquals($name, $this->feedEntry->contributors[0]->name);
		$this->assertEquals($email, $this->feedEntry->contributors[0]->email);
	}

	/**
	 * Tests the FeedEntry::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddMultipleContributors()
	{
		$contributors = [
			['name' => 'name1', 'email' => 'email1@email.com'],
			['name' => 'name2', 'email' => 'email2@email.com'],
			['name' => 'name3', 'email' => 'email3@email.com'],
		];

		foreach ($contributors as $contributor)
		{
			$this->feedEntry->addContributor($contributor['name'], $contributor['email']);
		}

		$this->assertCount(3, $this->feedEntry->contributors);
		foreach ($contributors as $index => $contributor)
		{
			$this->assertEquals($contributor['name'], $this->feedEntry->contributors[$index]->name);
			$this->assertEquals($contributor['email'], $this->feedEntry->contributors[$index]->email);
		}
	}

	/**
	 * Tests FeedEntry->addLink()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddNewLink()
	{
		$link = 'http://domain.com/path/to/resource';
		$feedLink = new FeedLink($link);

		$this->feedEntry->addLink($feedLink);

		$this->assertCount(1, $this->feedEntry->links);
		$this->assertEquals($link, $this->feedEntry->links[0]->uri);
	}

	/**
	 * Tests FeedEntry->addLink()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddExistingLink()
	{
		$link = 'http://domain.com/path/to/resource';
		$feedLink = new FeedLink($link);

		$this->feedEntry->addLink($feedLink);
		$this->feedEntry->addLink($feedLink);

		$this->assertCount(1, $this->feedEntry->links);
		$this->assertEquals($link, $this->feedEntry->links[0]->uri);
	}

	/**
	 * Tests the FeedEntry::removeCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveCategory()
	{
		$name = 'category';
		$uri = 'http://www.example.com';

		$this->feedEntry->addCategory($name, $uri);
		$this->feedEntry->removeCategory($name);

		$this->assertCount(0, $this->feedEntry->categories);
		$this->assertArrayNotHasKey($name, $this->feedEntry->categories);
	}

	/**
	 * Tests the FeedEntry::removeContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveContributor()
	{
		$name = 'Dennis Ritchie';
		$email = 'dennis.ritchie@example.com';

		$feedPerson = new FeedPerson($name, $email);

		$this->feedEntry->addContributor($name, $email);
		$this->feedEntry->removeContributor($feedPerson);

		$this->assertCount(0, $this->feedEntry->contributors);
	}

	/**
	 * Tests FeedEntry->removeLink()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveLink()
	{
		$link = 'http://domain.com/path/to/resource';
		$feedLink = new FeedLink($link);

		$this->feedEntry->addLink($feedLink);
		$this->feedEntry->removeLink($feedLink);

		$this->assertCount(0, $this->feedEntry->links);
	}

	/**
	 * Tests the FeedEntry::setAuthor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetAuthor()
	{
		$name = 'name';
		$email = 'email@email.com';
		$uri = 'http://example.com';
		$type = 'some type';

		$this->feedEntry->setAuthor($name, $email, $uri, $type);

		$this->assertInstanceOf(FeedPerson::class, $this->feedEntry->author);
		$this->assertEquals($name, $this->feedEntry->author->name);
		$this->assertEquals($email, $this->feedEntry->author->email);
		$this->assertEquals($uri, $this->feedEntry->author->uri);
		$this->assertEquals($type, $this->feedEntry->author->type);
	}
}
