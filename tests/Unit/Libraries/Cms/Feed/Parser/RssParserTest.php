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
use Joomla\CMS\Feed\FeedPerson;
use Joomla\CMS\Feed\Parser\RssParser;
use Joomla\Test\TestHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for RssParser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class RssParserTest extends UnitTestCase
{
    /**
     * Tests RssParser::handleCategory()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleCategory()
    {
        $category = 'IT/Internet/Web development';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement("<category>$category</category>");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('addCategory')
            ->with($category, '');

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleCategory', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleCloud()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleCloud()
    {
        $cloud = [
            'domain'            => 'domain.com',
            'port'              => '80',
            'path'              => '/RPC',
            'registerProcedure' => 'autoNotify',
            'protocol'          => 'xml-rpc',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<cloud domain="' . $cloud['domain'] . '" port="' . $cloud['port'] .
            '" path="' . $cloud['path'] . '" registerProcedure="' . $cloud['registerProcedure'] .
            '" protocol="' . $cloud['protocol'] . '" />');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'cloud',
                $this->callback(
                    function ($value) use ($cloud): bool {
                        $this->assertInstanceOf(\stdClass::class, $value);
                        $this->assertSame($cloud['domain'], $value->domain);
                        $this->assertSame($cloud['port'], $value->port);
                        $this->assertSame($cloud['path'], $value->path);
                        $this->assertSame($cloud['registerProcedure'], $value->registerProcedure);
                        $this->assertSame($cloud['protocol'], $value->protocol);
                        return true;
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleCloud', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleCopyright()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleCopyright()
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
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleCopyright', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleDescription()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleDescription()
    {
        $subtitle = 'Lorem Ipsum ...';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<description>' . $subtitle . '</description>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('description', $subtitle);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleDescription', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleGenerator()
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
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleGenerator', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleImage()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleImage()
    {
        $image = [
            'url'         => 'http://www.w3schools.com/images/logo.gif',
            'title'       => 'W3Schools.com',
            'link'        => 'http://www.w3schools.com',
            'description' => 'Some description',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<image><url>' . $image['url'] . '</url><title>' . $image['title'] .
            '</title><link>' . $image['link'] . '</link><description>' . $image['description'] .
            '</description></image>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'image',
                $this->callback(
                    function ($value) use ($image): bool {
                        $this->assertInstanceOf(FeedLink::class, $value);
                        $this->assertSame($image['url'], $value->uri);
                        $this->assertNull($value->relation);
                        $this->assertSame('logo', $value->type);
                        $this->assertNull($value->language);
                        $this->assertSame($image['title'], $value->title);
                        $this->assertSame($image['description'], $value->description);
                        $this->assertSame('', $value->height);
                        $this->assertSame('', $value->width);
                        return true;
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleImage', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleLanguage()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleLanguage()
    {
        $language = 'en-US';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<language>' . $language . '</language>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('language', $language);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleLanguage', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleLastBuildDate()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleLastBuildDate()
    {
        $buildDate = 'Sat, 01 Jan 2011 00:00:00 UTC';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<lastBuildDate>' . $buildDate . '</lastBuildDate>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('updatedDate', $buildDate);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleLastBuildDate', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleLink()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleLink()
    {
        $link = 'http://domain.com/path/to/resource';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement("<link href='$link' />");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'link',
                $this->callback(
                    function ($value) use ($link): bool {
                        $this->assertInstanceOf(FeedLink::class, $value);
                        $this->assertSame($link, $value->uri);
                        return true;
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleLink', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleManagingEditor()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleManagingEditor()
    {
        $editor = [
            'name'  => 'The Editor',
            'email' => 'editor@domain.com',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<managingEditor>' . $editor['email'] . ' ' . $editor['name'] . '</managingEditor>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'author',
                $this->callback(
                    function ($value) use ($editor): bool {
                        $this->assertInstanceOf(FeedPerson::class, $value);
                        $this->assertSame($editor['name'], $value->name);
                        $this->assertSame($editor['email'], $value->email);
                        return true;
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleManagingEditor', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handlePubDate()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandlePubDate()
    {
        $pubDate = 'Sat, 01 Jan 2011 00:00:00 GMT';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<pubDate>' . $pubDate . '</pubDate>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('publishedDate', $pubDate);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handlePubDate', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleSkipDays()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleSkipDays()
    {
        $skipDays = ['Saturday', 'Sunday'];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<skipDays><day>' . $skipDays[0] . '</day><day>' . $skipDays[1] . '</day></skipDays>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('skipDays', $skipDays);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleSkipDays', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleSkipHours()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleSkipHours()
    {
        $skipHours = ['0', '10'];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<skipHours><hour>' . $skipHours[0] . '</hour><hour>' . $skipHours[1] . '</hour></skipHours>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('skipHours', $skipHours);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleSkipHours', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleTitle()
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
        $xmlElement = new \SimpleXMLElement("<title>$title</title>");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('title', $title);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleTitle', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleTtl()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleTtl()
    {
        $ttl = '45';

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement("<ttl>$ttl</ttl>");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('ttl', (int) $ttl);

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleTtl', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleWebmaster()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testHandleWebmaster()
    {
        $webmaster = [
            'name'  => 'The Webmaster',
            'email' => 'webmaster@domain.com',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement('<webmaster>' . $webmaster['email'] . ' ' . $webmaster['name'] . '</webmaster>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('addContributor')
            ->with($webmaster['name'], $webmaster['email'], null, 'webmaster');

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'handleWebmaster', $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::parse()
     *
     * @return void
     * @since         3.1.4
     */
    public function testParseSetsVersion()
    {
        $dummyXml  = '<?xml version="1.0" encoding="utf-8"?>
<!-- generator="Joomla! Unit Test" -->
<rss version="2.0">
	<channel>
		<title>Test Channel</title>
	</channel>
</rss>';
        $reader = new \XMLReader();
        $reader->xml($dummyXml);
        $rssParser = new RssParser($reader);

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

        $rssParser->parse();

        $this->assertSame('2.0', TestHelper::getValue($rssParser, 'version'));
    }

    /**
     * Tests RssParser::processFeedEntry()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testProcessFeedEntry()
    {
        $entry = [
            'link'            => 'http://example.com/id',
            'title'           => 'title',
            'pubDate'         => 'August 25, 1991',
            'description'     => 'description',
            'category'        => 'category',
            'authorName'      => 'Webmaster',
            'authorEmail'     => 'admin@domain.com',
            'enclosureUrl'    => 'http://www.w3schools.com/media/3d.wmv',
            'enclosureLength' => '78645',
            'enclosureType'   => 'video/wmv',
        ];

        // It's currently not possible to mock simple xml element
        // @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new \SimpleXMLElement(
            '<entry>
				<link>' . $entry['link'] . '</link>
				<title>' . $entry['title'] . '</title>
				<pubDate>' . $entry['pubDate'] . '</pubDate>
				<description>' . $entry['description'] . '</description>
				<category>' . $entry['category'] . '</category>
				<author>' . $entry['authorEmail'] . ' (' . $entry['authorName'] . ')</author>
				<enclosure url="' . $entry['enclosureUrl'] . '" length="' . $entry['enclosureLength'] .
            '" type="' . $entry['enclosureType'] . '" />
			</entry>'
        );

        $feedEntryMock = $this->createMock(FeedEntry::class);
        $matcher       = $this->exactly(9);

        $feedEntryMock
            ->expects($matcher)
            ->method('__set')
            ->willReturnCallback(function (...$parameters) use ($matcher, $entry) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('uri', $parameters[0]);
                    $this->assertSame($entry['link'], $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('title', $parameters[0]);
                    $this->assertSame($entry['title'], $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('publishedDate', $parameters[0]);
                    $this->assertSame($entry['pubDate'], $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('updatedDate', $parameters[0]);
                    $this->assertSame($entry['pubDate'], $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame('content', $parameters[0]);
                    $this->assertSame($entry['description'], $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('guid', $parameters[0]);
                    $this->assertSame('', $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame('isPermaLink', $parameters[0]);
                    $this->assertTrue($parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 8) {
                    $this->assertSame('comments', $parameters[0]);
                    $this->assertSame('', $parameters[1]);
                } elseif ($matcher->numberOfInvocations() === 9) {
                    $this->assertSame('author', $parameters[0]);
                    $this->assertInstanceOf(FeedPerson::class, $parameters[1]);
                    $this->assertSame($entry['authorName'], $parameters[1]->name);
                    $this->assertSame($entry['authorEmail'], $parameters[1]->email);
                }
            });

        $feedEntryMock
            ->expects($this->once())
            ->method('addCategory')
            ->with($entry['category'], '');

        $feedEntryMock
            ->expects($this->once())
            ->method('addLink')
            ->with(
                $this->callback(
                    function ($value) use ($entry): bool {
                        $this->assertInstanceOf(FeedLink::class, $value);
                        $this->assertSame($entry['enclosureUrl'], $value->uri);
                        $this->assertSame($entry['enclosureType'], $value->type);
                        $this->assertSame((int) $entry['enclosureLength'], $value->length);
                        return true;
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser = new RssParser(new \XMLReader());
        TestHelper::invoke($rssParser, 'processFeedEntry', $feedEntryMock, $xmlElement);
    }
}
