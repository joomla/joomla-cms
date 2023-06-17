<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Feed;

use InvalidArgumentException;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedPerson;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Feed.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class FeedTest extends UnitTestCase
{
    /**
     * @var    Feed
     * @since  3.1.4
     */
    private $feed;

    /**
     * Setup the tests.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->feed = new Feed();
    }

    /**
     * Method to tear down whatever was set up before the test.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    protected function tearDown(): void
    {
        unset($this->feed);

        parent::tearDown();
    }

    /**
     * Tests the Feed::__get method when the property has been set to a value.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testMagicGetterWithDefaultProperty()
    {
        $this->assertEquals('', $this->feed->uri);
    }


    /**
     * Tests the Feed::__get method when the property has not been set to a value.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testMagicGetterWithUnknownProperty()
    {
        $this->assertEquals(null, $this->feed->unknown);
    }

    /**
     * Tests the Feed::__get method when the property has been set to a value.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testMagicGetterWithValue()
    {
        $value                 = 'test';
        $this->feed->testValue = $value;

        $this->assertEquals($value, $this->feed->testValue);
    }

    /**
     * Tests the Feed::__set method with updatedDate with a string.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testSetUpdatedDateWithString()
    {
        $this->feed->updatedDate = 'May 2nd, 1967';

        $this->assertInstanceOf(Date::class, $this->feed->updatedDate);
    }

    /**
     * Tests the Feed::__set method with updatedDate with a JDate object.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testMagicSetUpdatedDateWithDateObject()
    {
        $date                    = new Date('October 12, 2011');
        $this->feed->updatedDate = $date;

        $updatedDate = $this->feed->updatedDate;
        $this->assertInstanceOf(Date::class, $updatedDate);
        $this->assertSame($date, $updatedDate);
    }

    /**
     * Tests the Feed::setAuthor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testSetAuthorWithNameAndEmail()
    {
        $name  = 'Brian Kernighan';
        $email = 'brian@example.com';

        $this->feed->setAuthor($name, $email);

        $author = $this->feed->author;
        $this->assertInstanceOf(FeedPerson::class, $author);
        $this->assertEquals($name, $author->name);
        $this->assertEquals($email, $author->email);
    }

    /**
     * Tests the Feed::__set method with a person object.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testSetAuthorWithPerson()
    {
        $name   = 'Brian Kernighan';
        $email  = 'brian@example.com';
        $person = new FeedPerson($name, $email);

        $this->feed->author = $person;

        $author = $this->feed->author;
        $this->assertInstanceOf(FeedPerson::class, $author);
        $this->assertEquals($name, $author->name);
        $this->assertEquals($email, $author->email);
    }

    /**
     * Tests the Feed::__set method with an invalid argument for author.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testSetAuthorWithInvalidAuthor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feed->author = 'Jack Sprat';
    }

    /**
     * Tests the Feed::__set method with a disallowed property.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testSetCategoriesWithInvalidProperty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feed->categories = 'Can\'t touch this';
    }

    /**
     * Tests the Feed::__set method with a disallowed property.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testSetContributorsWithInvalidProperty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feed->contributors = 'Can\'t touch this';
    }

    /**
     * Tests the Feed::__set method with a typical property.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testMagicSetter()
    {
        $value                 = 'test';
        $this->feed->testValue = $value;

        $this->assertEquals($value, $this->feed->testValue);
    }

    /**
     * Tests the Feed::addCategory method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddSingleCategory()
    {
        $name = 'category';
        $uri  = 'http://www.example.com';
        $this->feed->addCategory($name, $uri);

        $categories = $this->feed->categories;
        $this->assertCount(1, $categories);
        $this->assertArrayHasKey($name, $categories);
        $this->assertEquals($uri, $categories[$name]);
    }

    /**
     * Tests the Feed::addCategory method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddMultipleCategories()
    {
        $categories = [
            ['name' => 'category1', 'uri' => 'http://www.example1.com'],
            ['name' => 'category2', 'uri' => 'http://www.example2.com'],
            ['name' => 'category3', 'uri' => 'http://www.example3.com'],
        ];

        foreach ($categories as $category) {
            $this->feed->addCategory($category['name'], $category['uri']);
        }

        $feedCategories = $this->feed->categories;
        $this->assertCount(3, $feedCategories);

        foreach ($categories as $category) {
            $this->assertArrayHasKey($category['name'], $feedCategories);
            $this->assertEquals($category['uri'], $feedCategories[$category['name']]);
        }
    }

    /**
     * Tests the Feed::addContributor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddNewContributor()
    {
        $name  = 'Dennis Ritchie';
        $email = 'dennis.ritchie@example.com';

        $this->feed->addContributor($name, $email);

        $contributors = $this->feed->contributors;
        $this->assertCount(1, $contributors);
        $this->assertEquals($name, $contributors[0]->name);
        $this->assertEquals($email, $contributors[0]->email);
    }

    /**
     * Tests the Feed::addContributor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddExistingContributor()
    {
        $name  = 'Dennis Ritchie';
        $email = 'dennis.ritchie@example.com';

        $this->feed->addContributor($name, $email);
        $this->feed->addContributor($name, $email);

        $contributors = $this->feed->contributors;
        $this->assertCount(1, $contributors);
        $this->assertEquals($name, $contributors[0]->name);
        $this->assertEquals($email, $contributors[0]->email);
    }

    /**
     * Tests the Feed::addContributor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddMultipleContributors()
    {
        $contributors = [
            ['name' => 'name1', 'email' => 'email1@email.com'],
            ['name' => 'name2', 'email' => 'email2@email.com'],
            ['name' => 'name3', 'email' => 'email3@email.com'],
        ];

        foreach ($contributors as $contributor) {
            $this->feed->addContributor($contributor['name'], $contributor['email']);
        }

        $feedContributors = $this->feed->contributors;
        $this->assertCount(3, $feedContributors);

        foreach ($contributors as $index => $contributor) {
            $this->assertEquals($contributor['name'], $feedContributors[$index]->name);
            $this->assertEquals($contributor['email'], $feedContributors[$index]->email);
        }
    }

    /**
     * Tests the Feed::addEntry method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddNewEntry()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->addEntry($feedEntry);

        $this->assertEquals(1, $this->feed->count());
        $this->assertEquals($feedEntry, $this->feed[0]);
    }

    /**
     * Tests the Feed::addEntry method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddExistingEntry()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->addEntry($feedEntry);
        $this->feed->addEntry($feedEntry);

        $this->assertEquals(1, $this->feed->count());
        $this->assertEquals($feedEntry, $this->feed[0]);
    }

    /**
     * Tests the Feed::addEntry method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddMultipleEntries()
    {
        $feedEntry1       = $this->createMock(FeedEntry::class);
        $feedEntry2       = $this->createMock(FeedEntry::class);
        $feedEntry2->name = 'name2';

        $this->feed->addEntry($feedEntry1);
        $this->feed->addEntry($feedEntry2);

        $this->assertEquals(2, $this->feed->count());
        $this->assertEquals($feedEntry1, $this->feed[0]);
        $this->assertEquals($feedEntry2, $this->feed[1]);
    }

    /**
     * Tests the Feed::offsetExists method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testOffsetExists()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->addEntry($feedEntry);

        $this->assertTrue($this->feed->offsetExists(0));
    }

    /**
     * Tests the Feed::offsetGet method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testOffsetGet()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->addEntry($feedEntry);

        $this->assertEquals($feedEntry, $this->feed->offsetGet(0));
    }

    /**
     * Tests the Feed::offsetSet method with a string as a value -- invalid.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testOffsetSetWithString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feed->offsetSet(1, 'My string');
    }

    /**
     * Tests the Feed::offsetSet method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testOffsetSet()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->offsetSet(1, $feedEntry);

        $this->assertEquals(1, $this->feed->count());
        $this->assertEquals($feedEntry, $this->feed->offsetGet(1));
    }

    /**
     * Tests the Feed::offsetUnset method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testOffsetUnset()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->offsetSet(10, $feedEntry);
        $this->feed->offsetUnset(10);

        $this->assertEquals(0, $this->feed->count());
    }

    /**
     * Tests the Feed::removeCategory method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testRemoveCategory()
    {
        $name = 'category';
        $uri  = 'http://www.example.com';

        $this->feed->addCategory($name, $uri);
        $this->feed->removeCategory($name);

        $categories = $this->feed->categories;
        $this->assertCount(0, $categories);
        $this->assertArrayNotHasKey($name, $categories);
    }

    /**
     * Tests the Feed::removeContributor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testRemoveContributor()
    {
        $name  = 'Dennis Ritchie';
        $email = 'dennis.ritchie@example.com';

        $feedPerson = new FeedPerson($name, $email);

        $this->feed->addContributor($name, $email);
        $this->feed->removeContributor($feedPerson);

        $this->assertCount(0, $this->feed->contributors);
    }

    /**
     * Tests the Feed::removeEntry method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testRemoveEntry()
    {
        $feedEntry = $this->createMock(FeedEntry::class);

        $this->feed->addEntry($feedEntry);
        $this->feed->removeEntry($feedEntry);

        $this->assertEquals(0, $this->feed->count());
    }
}
