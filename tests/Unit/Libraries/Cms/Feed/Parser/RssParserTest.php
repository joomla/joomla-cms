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
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;
use SimpleXMLElement;
use XMLReader;

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
     * @throws \ReflectionException
     */
    public function testHandleCategory()
    {
        $category = 'IT/Internet/Web development';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement("<category>$category</category>");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('addCategory')
            ->with($category, '');

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleCategory');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleCloud()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
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
        // @see https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<cloud domain="' . $cloud['domain'] . '" port="' . $cloud['port'] .
            '" path="' . $cloud['path'] . '" registerProcedure="' . $cloud['registerProcedure'] .
            '" protocol="' . $cloud['protocol'] . '" />');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'cloud',
                $this->callback(
                    function ($value) use ($cloud) {
                        return is_object($value)
                            && $value->domain === $cloud['domain']
                            && $value->port === $cloud['port']
                            && $value->path === $cloud['path']
                            && $value->registerProcedure === $cloud['registerProcedure']
                            && $value->protocol === $cloud['protocol'];
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleCloud');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleCopyright()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleCopyright()
    {
        $copyright = 'All Rights Reserved.';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<rights>' . $copyright . '</rights>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('copyright', $copyright);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleCopyright');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleDescription()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleDescription()
    {
        $subtitle = 'Lorem Ipsum ...';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<description>' . $subtitle . '</description>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('description', $subtitle);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleDescription');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleGenerator()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleGenerator()
    {
        $generator = 'Joomla';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<generator>' . $generator . '</generator>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('generator', $generator);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleGenerator');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleImage()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
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
        // @see https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<image><url>' . $image['url'] . '</url><title>' . $image['title'] .
            '</title><link>' . $image['link'] . '</link><description>' . $image['description'] .
            '</description></image>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'image',
                $this->callback(
                    function ($value) use ($image) {
                        return $value instanceof FeedLink
                            && $value->uri === $image['url']
                            && $value->relation === null
                            && $value->type === 'logo'
                            && $value->language === null
                            && $value->title === $image['title']
                            && $value->description === $image['description']
                            && $value->height === ''
                            && $value->width === '';
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleImage');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleLanguage()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleLanguage()
    {
        $language = 'en-US';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<language>' . $language . '</language>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('language', $language);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleLanguage');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleLastBuildDate()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleLastBuildDate()
    {
        $buildDate = 'Sat, 01 Jan 2011 00:00:00 UTC';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<lastBuildDate>' . $buildDate . '</lastBuildDate>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('updatedDate', $buildDate);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleLastBuildDate');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleLink()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleLink()
    {
        $link = 'http://domain.com/path/to/resource';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement("<link href='$link' />");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'link',
                $this->callback(
                    function ($value) use ($link) {
                        return $value instanceof FeedLink && $value->uri === $link;
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleLink');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleManagingEditor()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleManagingEditor()
    {
        $editor = [
            'name'  => 'The Editor',
            'email' => 'editor@domain.com',
        ];

        // It's currently not possible to mock simple xml element
        // @see https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<managingEditor>' . $editor['email'] . ' ' . $editor['name'] . '</managingEditor>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                'author',
                $this->callback(
                    function ($value) use ($editor) {
                        return $value instanceof FeedPerson
                            && $value->name === $editor['name']
                            && $value->email === $editor['email'];
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleManagingEditor');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handlePubDate()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandlePubDate()
    {
        $pubDate = 'Sat, 01 Jan 2011 00:00:00 GMT';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<pubDate>' . $pubDate . '</pubDate>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('publishedDate', $pubDate);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handlePubDate');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleSkipDays()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleSkipDays()
    {
        $skipDays = ['Saturday', 'Sunday'];

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<skipDays><day>' . $skipDays[0] . '</day><day>' . $skipDays[1] . '</day></skipDays>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('skipDays', $skipDays);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleSkipDays');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleSkipHours()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleSkipHours()
    {
        $skipHours = ['0', '10'];

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<skipHours><hour>' . $skipHours[0] . '</hour><hour>' . $skipHours[1] . '</hour></skipHours>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('skipHours', $skipHours);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleSkipHours');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleTitle()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleTitle()
    {
        $title = 'My Title.';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement("<title>$title</title>");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('title', $title);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleTitle');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleTtl()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleTtl()
    {
        $ttl = '45';

        // It's currently not possible to mock simple xml element
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement("<ttl>$ttl</ttl>");

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('__set')
            ->with('ttl', (int) $ttl);

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleTtl');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::handleWebmaster()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testHandleWebmaster()
    {
        $webmaster = [
            'name'  => 'The Webmaster',
            'email' => 'webmaster@domain.com',
        ];

        // It's currently not possible to mock simple xml element
        // @see https://github.com/se3bastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement('<webmaster>' . $webmaster['email'] . ' ' . $webmaster['name'] . '</webmaster>');

        $feedMock = $this->createMock(Feed::class);
        $feedMock
            ->expects($this->once())
            ->method('addContributor')
            ->with($webmaster['name'], $webmaster['email'], null, 'webmaster');

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('handleWebmaster');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedMock, $xmlElement);
    }

    /**
     * Tests RssParser::parse()
     *
     * @return void
     * @since         3.1.4
     * @throws \ReflectionException
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
        $reader = new XMLReader();
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

        // Use reflection to check the value
        $reflectionClass = new ReflectionClass($rssParser);
        $attribute       = $reflectionClass->getProperty('version');

        $attribute->setAccessible(true);
        $this->assertEquals('2.0', $attribute->getValue($rssParser));
    }

    /**
     * Tests RssParser::processFeedEntry()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
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
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
        $xmlElement = new SimpleXMLElement(
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

        $feedEntryMock
            ->expects($this->any())
            ->method('__set')
            ->withConsecutive(
                ['uri', $entry['link']],
                ['title', $entry['title']],
                ['publishedDate', $entry['pubDate']],
                ['updatedDate', $entry['pubDate']],
                ['content', $entry['description']],
                ['guid', ''],
                ['isPermaLink', true],
                ['comments', ''],
                ['author', $this->callback(
                    function ($value) use ($entry) {
                        return $value instanceof FeedPerson
                            && $value->name === $entry['authorName']
                            && $value->email === $entry['authorEmail'];
                    }
                ),
                ]
            );

        $feedEntryMock
            ->expects($this->once())
            ->method('addCategory')
            ->with($entry['category'], '');

        $feedEntryMock
            ->expects($this->once())
            ->method('addLink')
            ->with(
                $this->callback(
                    function ($value) use ($entry) {
                        return $value instanceof FeedLink
                            && $value->uri === $entry['enclosureUrl']
                            && $value->type === $entry['enclosureType']
                            && $value->length === (int) $entry['enclosureLength'];
                    }
                )
            );

        // Use reflection to test protected method
        $rssParser       = new RssParser(new \XMLReader());
        $reflectionClass = new ReflectionClass($rssParser);
        $method          = $reflectionClass->getMethod('processFeedEntry');
        $method->setAccessible(true);
        $method->invoke($rssParser, $feedEntryMock, $xmlElement);
    }
}
