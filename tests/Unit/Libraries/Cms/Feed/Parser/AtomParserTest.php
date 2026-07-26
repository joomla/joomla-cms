<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Feed\Parser;

use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedLink;
use Joomla\CMS\Feed\Parser\AtomParser;
use Joomla\Test\TestHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for AtomParser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class AtomParserTest extends UnitTestCase
{
    /**
     * Tests AtomParser::handleAuthor()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleAuthor()
    {
        $author = [
            'name'  => 'John Doe',
            'email' => 'john@doe.name',
            'uri'   => 'http://doe.name',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement        = new \SimpleXMLElement('<author/>');
        $xmlElement->name  = $author['name'];
        $xmlElement->email = $author['email'];
        $xmlElement->uri   = $author['uri'];

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('setAuthor')
            ->with($author['name'], $author['email'], $author['uri']);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleAuthor', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleContributor()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleContributor()
    {
        $contributor = [
            'name'  => 'John Doe',
            'email' => 'john@doe.name',
            'uri'   => 'http://doe.name',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement        = new \SimpleXMLElement('<contributor />');
        $xmlElement->name  = $contributor['name'];
        $xmlElement->email = $contributor['email'];
        $xmlElement->uri   = $contributor['uri'];

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('addContributor')
            ->with($contributor['name'], $contributor['email'], $contributor['uri']);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleContributor', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleGenerator()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleGenerator()
    {
        $generator = 'Joomla';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<generator>' . $generator . '</generator>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('generator', $generator);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleGenerator', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleId()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleId()
    {
        $id = 'http://domain.com/path/to/resource';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<id>' . $id . '</id>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('uri', $id);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleId', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleLink()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleLink()
    {
        $href = 'http://domain.com/path/to/resource';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<link href="' . $href . '" />');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'link',
                $this->callback(
                    function ($param) use ($href): bool {
                        $this->assertInstanceOf(FeedLink::class, $param);
                        $this->assertSame($href, $param->uri);
                        return true;
                    }
                )
            );

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleLink', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleRights()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleRights()
    {
        $copyright = 'All Rights Reserved.';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<rights>' . $copyright . '</rights>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('copyright', $copyright);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleRights', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleSubtitle()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleSubtitle()
    {
        $subtitle = 'Lorem Ipsum ...';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<subtitle>' . $subtitle . '</subtitle>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('description', $subtitle);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleSubtitle', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleTitle()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleTitle()
    {
        $title = 'My Title.';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<title>' . $title . '</title>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('title', $title);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleTitle', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::handleUpdated()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleUpdated()
    {
        $date = '2019-01-01T00:00:00Z';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<updated>' . $date . '</updated>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('updatedDate', $date);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'handleUpdated', $feedMock, $xmlElement);
    }

    /**
     * Tests AtomParser::parse()
     *
     * @return void
     * @since         3.1.4
     */
    public function testInitialiseSetsDefaultVersionWithXmlDocType()
    {
        $dummyXml   = '<?xml version="1.0" encoding="utf-8" ?>
<feed xmlns="http://www.w3.org/2005/Atom" />';
        $reader = new \XMLReader();
        $reader->xml($dummyXml);
        $atomParser = new AtomParser($reader);
        $atomParser->parse();

        $this->assertSame('1.0', TestHelper::getValue($atomParser, 'version'));
    }

    /**
     * Tests AtomParser::parse()
     *
     * @return void
     * @since         3.1.4
     */
    public function testInitialiseSetsDefaultVersion()
    {
        $dummyXml   = '<?xml version="1.0" encoding="utf-8"?>
<!-- generator="Joomla! Unit Test" -->
<feed xmlns="http://www.w3.org/2005/Atom">
<title type="text">Joomla! Unit test</title>
</feed>';
        $reader   = new \XMLReader();
        $reader->xml($dummyXml);
        $atomParser = new AtomParser($reader);

        // same logic as FeedFactory.php : skip head record
        try {
            // Skip ahead to the root node.
            while ($reader->read()) {
                if ($reader->nodeType == \XMLReader::ELEMENT) {
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Error reading feed.', $e->getCode(), $e);
        }

        $atomParser->parse();

        $this->assertSame('1.0', TestHelper::getValue($atomParser, 'version'));
    }

    /**
     * Tests AtomParser::parse()
     *
     * @return void
     * @since         3.1.4
     */
    public function testInitialiseSetsOldVersion()
    {
        $dummyXml = '<?xml version="1.0" encoding="utf-8"?>
<!-- generator="Joomla! Unit Test" -->
<feed  version="0.3" xmlns="http://www.w3.org/2005/Atom">
<title type="text">Joomla! Unit test</title>
</feed>';
        $reader   = new \XMLReader();
        $reader->xml($dummyXml);
        $atomParser = new AtomParser($reader);

        // same logic as FeedFactory.php : skip head record
        try {
            // Skip ahead to the root node.
            while ($reader->read()) {
                if ($reader->nodeType == \XMLReader::ELEMENT) {
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Error reading feed.', $e->getCode(), $e);
        }

        $atomParser->parse();

        $this->assertSame('0.3', TestHelper::getValue($atomParser, 'version'));
    }

    /**
     * Tests AtomParser::processFeedEntry()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testProcessFeedEntry()
    {
        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<entry><id>http://example.com/id</id>
			<title>title</title><updated>August 25, 1991</updated><summary>summary</summary></entry>');

        $feedEntryMock = $this->createMock(FeedEntry::class);
        $matcher       = $this->exactly(4);
        $feedEntryMock
            ->expects($matcher)
            ->method('__set')
            ->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('uri', $parameters[0]);
                    $this->assertSame('http://example.com/id', $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('title', $parameters[0]);
                    $this->assertSame('title', $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('updatedDate', $parameters[0]);
                    $this->assertSame('August 25, 1991', $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('content', $parameters[0]);
                    $this->assertSame('summary', $parameters[1]);
                }
            });

        /**
         * Ensure that for the test to work we correctly return the content element (as a normal class would do
         * when a property is set)
         */
        $map = [
            ['content', 'summary'],
        ];

        $feedEntryMock
            ->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap($map);

        // Use reflection to test protected method
        $atomParser = new AtomParser(new \XMLReader());
        TestHelper::invoke($atomParser, 'processFeedEntry', $feedEntryMock, $xmlElement);
    }
}
