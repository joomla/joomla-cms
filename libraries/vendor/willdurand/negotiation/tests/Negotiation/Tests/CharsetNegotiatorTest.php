<?php

namespace Negotiation\Tests;

use Negotiation\CharsetNegotiator;

class CharsetNegotiatorTest extends TestCase
{

    /**
     * @var CharsetNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new CharsetNegotiator();
    }

    public function testGetBestReturnsNullWithUnmatchedHeader()
    {
        $this->assertNull($this->negotiator->getBest('foo, bar, yo', array('baz')));
    }

    /**
     * 'bu' has the highest quality rating, but is non-existent,
     * so we expect the next highest rated 'fr' content to be returned.
     *
     * See: http://svn.apache.org/repos/asf/httpd/test/framework/trunk/t/modules/negotiation.t
     */
    public function testGetBestIgnoresNonExistentContent()
    {
        $acceptCharset = 'en; q=0.1, fr; q=0.4, bu; q=1.0';
        $accept        = $this->negotiator->getBest($acceptCharset, array('en', 'fr'));

        $this->assertInstanceOf('Negotiation\AcceptCharset', $accept);
        $this->assertEquals('fr', $accept->getValue());
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($accept, $priorities, $expected)
    {
        if (is_null($expected))
            $this->setExpectedException('Negotiation\Exception\InvalidArgument');

        $accept = $this->negotiator->getBest($accept, $priorities);
        if (null === $accept) {
            $this->assertNull($expected);
        } else {
            $this->assertInstanceOf('Negotiation\AcceptCharset', $accept);
            $this->assertSame($expected, $accept->getValue());
        }
    }

    public static function dataProviderForTestGetBest()
    {
        $pearCharset  = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7, *;q=0.5';
        $pearCharset2 = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7';

        return array(
            array($pearCharset, array( 'utf-8', 'big5', 'iso-8859-1', 'shift-jis',), 'iso-8859-1'),
            array($pearCharset, array( 'utf-8', 'big5', 'shift-jis',), 'utf-8'),
            array($pearCharset, array( 'Big5', 'shift-jis',), 'Big5'),
            array($pearCharset, array( 'shift-jis',), 'shift-jis'),
            array($pearCharset2, array( 'utf-8', 'big5', 'iso-8859-1', 'shift-jis',), 'iso-8859-1'),
            array($pearCharset2, array( 'utf-8', 'big5', 'shift-jis',), 'utf-8'),
            array($pearCharset2, array( 'Big5', 'shift-jis',), 'Big5'),
            array('utf-8;q=0.6,iso-8859-5;q=0.9', array( 'iso-8859-5', 'utf-8',), 'iso-8859-5'),
            array('', array( 'iso-8859-5', 'utf-8',), null),
            array('en, *;q=0.9', array('fr'), 'fr'),
            # Quality of source factors
            array($pearCharset, array('iso-8859-1;q=0.5', 'utf-8', 'utf-16;q=1.0'), 'utf-8'),
            array($pearCharset, array('iso-8859-1;q=0.8', 'utf-8', 'utf-16;q=1.0'), 'iso-8859-1;q=0.8'),
        );
    }

    public function testGetBestRespectsPriorities()
    {
        $accept = $this->negotiator->getBest('foo, bar, yo', array('yo'));

        $this->assertInstanceOf('Negotiation\AcceptCharset', $accept);
        $this->assertEquals('yo', $accept->getValue());
    }

    public function testGetBestDoesNotMatchPriorities()
    {
        $acceptCharset = 'en, de';
        $priorities           = array('fr');

        $this->assertNull($this->negotiator->getBest($acceptCharset, $priorities));
    }

    public function testGetBestRespectsQualityOfSource()
    {
        $accept = $this->negotiator->getBest('utf-8;q=0.5,iso-8859-1', array('iso-8859-1;q=0.3', 'utf-8;q=0.9', 'utf-16;q=1.0'));
        $this->assertInstanceOf('Negotiation\AcceptCharset', $accept);
        $this->assertEquals('utf-8', $accept->getType());
    }

    /**
     * @dataProvider dataProviderForTestParseHeader
     */
    public function testParseHeader($header, $expected)
    {
        $accepts = $this->call_private_method('Negotiation\CharsetNegotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertSame($expected, $accepts);
    }

    public static function dataProviderForTestParseHeader()
    {
        return array(
            array('*;q=0.3,ISO-8859-1,utf-8;q=0.7', array('*;q=0.3', 'ISO-8859-1', 'utf-8;q=0.7')),
            array('*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7', array('*;q=0.3', 'ISO-8859-1;q=0.7', 'utf-8;q=0.7')),
            array('*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7', array('*;q=0.3', 'utf-8;q=0.7', 'ISO-8859-1;q=0.7')),
            array('iso-8859-5, unicode-1-1;q=0.8', array('iso-8859-5', 'unicode-1-1;q=0.8')),
        );
    }
}
