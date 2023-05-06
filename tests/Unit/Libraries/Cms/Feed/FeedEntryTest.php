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
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedLink;
use Joomla\CMS\Feed\FeedPerson;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for FeedEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class FeedEntryTest extends UnitTestCase
{
    /**
     * @var FeedEntry
     *
     * @since   4.0.0
     */
    protected $feedEntry;

    /**
     * Setup the tests.
     *
     * @return  void
     *
     * @see     \PHPUnit\Framework\TestCase::setUp()
     * @since   3.1.4
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->feedEntry = new FeedEntry();
    }

    /**
     * Method to tear down whatever was set up before the test.
     *
     * @return  void
     *
     * @see     \PHPUnit\Framework\TestCase::tearDown()
     * @since   3.1.4
     */
    protected function tearDown(): void
    {
        unset($this->feedEntry);

        parent::tearDown();
    }

    /**
     * Tests the FeedEntry::__get method with default property.
     *
     * @return  void
     *
     * @since   3.1.4
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
     * @since   3.1.4
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
     * @since   3.1.4
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
     * @since   3.1.4
     */
    public function testMagicSetUpdatedDateWithDateObject()
    {
        $date                         = new Date('October 12, 2011');
        $this->feedEntry->updatedDate = $date;

        $updatedDate = $this->feedEntry->updatedDate;
        $this->assertInstanceOf(Date::class, $updatedDate);
        $this->assertSame($date, $updatedDate);
    }

    /**
     * Tests the FeedEntry::__set method with a person object.
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

        $this->feedEntry->author = $person;

        $author = $this->feedEntry->author;
        $this->assertInstanceOf(FeedPerson::class, $author);
        $this->assertEquals($name, $author->name);
        $this->assertEquals($email, $author->email);
    }

    /**
     * Tests the FeedEntry::__set method with an invalid argument for author.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testSetAuthorWithInvalidAuthor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feedEntry->author = 'Jack Sprat';
    }

    /**
     * Tests the FeedEntry::__set method with an invalid argument for author.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testSetSourceWithInvalidSource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feedEntry->source = 'Outer Space';
    }

    /**
     * Tests the FeedEntry::__set method with a disallowed property.
     *
     * @return  void
     *
     * @since              3.1.4
     */
    public function testSetCategoriesWithInvalidProperty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feedEntry->categories = 'Can\'t touch this';
    }

    /**
     * Tests the FeedEntry::__set method with a typical property.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testMagicSetter()
    {
        $value                      = 'test';
        $this->feedEntry->testValue = $value;

        $this->assertEquals($value, $this->feedEntry->testValue);
    }

    /**
     * Tests the FeedEntry::addCategory method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddSingleCategory()
    {
        $name = 'category';
        $uri  = 'http://www.example.com';
        $this->feedEntry->addCategory($name, $uri);

        $categories = $this->feedEntry->categories;
        $this->assertCount(1, $categories);
        $this->assertArrayHasKey($name, $categories);
        $this->assertEquals($uri, $categories[$name]);
    }

    /**
     * Tests the FeedEntry::addCategory method.
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
            $this->feedEntry->addCategory($category['name'], $category['uri']);
        }

        $feedCategories = $this->feedEntry->categories;
        $this->assertCount(3, $feedCategories);

        foreach ($categories as $category) {
            $this->assertArrayHasKey($category['name'], $feedCategories);
            $this->assertEquals($category['uri'], $feedCategories[$category['name']]);
        }
    }

    /**
     * Tests the FeedEntry::addContributor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddNewContributor()
    {
        $name  = 'Dennis Ritchie';
        $email = 'dennis.ritchie@example.com';

        $this->feedEntry->addContributor($name, $email);

        $contributors = $this->feedEntry->contributors;
        $this->assertCount(1, $contributors);
        $this->assertEquals($name, $contributors[0]->name);
        $this->assertEquals($email, $contributors[0]->email);
    }

    /**
     * Tests the FeedEntry::addContributor method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddExistingContributor()
    {
        $name  = 'Dennis Ritchie';
        $email = 'dennis.ritchie@example.com';

        $this->feedEntry->addContributor($name, $email);
        $this->feedEntry->addContributor($name, $email);

        $contributors = $this->feedEntry->contributors;
        $this->assertCount(1, $contributors);
        $this->assertEquals($name, $contributors[0]->name);
        $this->assertEquals($email, $contributors[0]->email);
    }

    /**
     * Tests the FeedEntry::addContributor method.
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
            $this->feedEntry->addContributor($contributor['name'], $contributor['email']);
        }

        $feedContributors = $this->feedEntry->contributors;
        $this->assertCount(3, $feedContributors);

        foreach ($contributors as $index => $contributor) {
            $this->assertEquals($contributor['name'], $feedContributors[$index]->name);
            $this->assertEquals($contributor['email'], $feedContributors[$index]->email);
        }
    }

    /**
     * Tests FeedEntry->addLink()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddNewLink()
    {
        $link     = 'http://domain.com/path/to/resource';
        $feedLink = new FeedLink($link);

        $this->feedEntry->addLink($feedLink);

        $feedLinks = $this->feedEntry->links;
        $this->assertCount(1, $feedLinks);
        $this->assertEquals($link, $feedLinks[0]->uri);
    }

    /**
     * Tests FeedEntry->addLink()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testAddExistingLink()
    {
        $link     = 'http://domain.com/path/to/resource';
        $feedLink = new FeedLink($link);

        $this->feedEntry->addLink($feedLink);
        $this->feedEntry->addLink($feedLink);

        $feedLinks = $this->feedEntry->links;
        $this->assertCount(1, $feedLinks);
        $this->assertEquals($link, $feedLinks[0]->uri);
    }

    /**
     * Tests the FeedEntry::removeCategory method.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testRemoveCategory()
    {
        $name = 'category';
        $uri  = 'http://www.example.com';

        $this->feedEntry->addCategory($name, $uri);
        $this->feedEntry->removeCategory($name);

        $categories = $this->feedEntry->categories;
        $this->assertCount(0, $categories);
        $this->assertArrayNotHasKey($name, $categories);
    }

    /**
     * Tests the FeedEntry::removeContributor method.
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

        $this->feedEntry->addContributor($name, $email);
        $this->feedEntry->removeContributor($feedPerson);

        $this->assertCount(0, $this->feedEntry->contributors);
    }

    /**
     * Tests FeedEntry->removeLink()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testRemoveLink()
    {
        $link     = 'http://domain.com/path/to/resource';
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
     * @since   3.1.4
     */
    public function testSetAuthor()
    {
        $name  = 'name';
        $email = 'email@email.com';
        $uri   = 'http://example.com';
        $type  = 'some type';

        $this->feedEntry->setAuthor($name, $email, $uri, $type);

        $author = $this->feedEntry->author;
        $this->assertInstanceOf(FeedPerson::class, $author);
        $this->assertEquals($name, $author->name);
        $this->assertEquals($email, $author->email);
        $this->assertEquals($uri, $author->uri);
        $this->assertEquals($type, $author->type);
    }
}
