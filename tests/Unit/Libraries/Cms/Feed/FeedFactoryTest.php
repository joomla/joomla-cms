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
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Feed\FeedParser;
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;
use XMLReader;

/**
 * Test class for FeedFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @since   4.0.0
 */
class FeedFactoryTest extends UnitTestCase
{
    /**
     * @since   4.0.0
     */
    private FeedFactory $feedFactory;

    /**
     * Setup the tests.
     *
     *
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->feedFactory = new FeedFactory();
    }

    /**
     * Method to tear down whatever was set up before the test.
     *
     * @since   4.0.0
     */
    protected function tearDown(): void
    {
        unset($this->feedFactory);

        parent::tearDown();
    }

    /**
     * Tests FeedFactory::getFeed().
     *
     * @return void
     * @since   4.0.0
     */
    public function testGetFeed(): never
    {
        $this->markTestSkipped('We cant unit test FeedFactory::getFeed() at the moment,
		 because it uses filesystem (XMLReader::open) and http service.
		  It should be refactored and covered with integration tests.');
    }

    /**
     *  Tests FeedFactory::getParsers().
     *
     * @return void
     * @since   4.0.0
     */
    public function testGetDefaultParsers()
    {
        $defaultParsers = $this->feedFactory->getParsers();
        $this->assertCount(2, $defaultParsers);
        $this->assertArrayHasKey('rss', $defaultParsers);
        $this->assertArrayHasKey('feed', $defaultParsers);
    }

    /**
     * Tests FeedFactory::registerParser()
     *
     * @return void
     * @since   4.0.0
     */
    public function testRegisterParser()
    {
        $tagName = 'parser-mock';
        $parseMock = $this->createMock(FeedParser::class);
        $defaultParserCount = count($this->feedFactory->getParsers());

        $this->feedFactory->registerParser($tagName, $parseMock::class);

        $feedParsers = $this->feedFactory->getParsers();
        $this->assertCount($defaultParserCount + 1, $feedParsers);
        $this->assertArrayHasKey($tagName, $feedParsers);
    }

    /**
     * Tests FeedFactory::registerParser()
     *
     * @return void
     * @since   4.0.0
     */
    public function testRegisterParserWithInvalidClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->feedFactory->registerParser('does-not-exist', 'NotExistingClass');
    }

    /**
     * Tests FeedFactory::registerParser()
     *
     * @return void
     * @since   4.0.0
     */
    public function testRegisterParserWithInvalidTag()
    {
        $this->expectException(InvalidArgumentException::class);
        $parseMock = $this->createMock(FeedParser::class);
        $this->feedFactory->registerParser('42tag', $parseMock::class);
    }

    /**
     * Tests FeedFactory::_fetchFeedParser()
     *
     * @return void
     * @since   4.0.0
     * @throws \ReflectionException
     */
    public function testFetchFeedParser()
    {
        $tagName = 'parser-mock';
        $parseMock = $this->createMock(FeedParser::class);
        $this->feedFactory->registerParser($tagName, $parseMock::class);

        // Use reflection to test private method
        $reflectionClass = new ReflectionClass($this->feedFactory);
        $method = $reflectionClass->getMethod('_fetchFeedParser');
        $method->setAccessible(true);
        $parser = $method->invoke($this->feedFactory, $tagName, new \XMLReader());

        $this->assertInstanceOf(FeedParser::class, $parser);
        $this->assertSame($parseMock::class, $parser::class);
    }

    /**
     * Tests FeedFactory::_fetchFeedParser()
     *
     * @return void
     * @since   4.0.0
     * @throws \ReflectionException
     */
    public function testFetchFeedParserWithInvalidTag()
    {
        $this->expectException(\LogicException::class);

        // Use reflection to test private method
        $reflectionClass = new ReflectionClass($this->feedFactory);
        $method = $reflectionClass->getMethod('_fetchFeedParser');
        $method->setAccessible(true);
        $method->invoke($this->feedFactory, 'not-existing', new \XMLReader());
    }
}
