<?php

namespace Negotiation\Tests;

use Negotiation\EncodingNegotiator;

class EncodingNegotiatorTest extends TestCase
{

    /**
     * @var EncodingNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new EncodingNegotiator();
    }

    public function testGetBestReturnsNullWithUnmatchedHeader()
    {
        $this->assertNull($this->negotiator->getBest('foo, bar, yo', array('baz')));
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($accept, $priorities, $expected)
    {
        $accept = $this->negotiator->getBest($accept, $priorities);

        if (null === $accept) {
            $this->assertNull($expected);
        } else {
            $this->assertInstanceOf('Negotiation\AcceptEncoding', $accept);
            $this->assertEquals($expected, $accept->getValue());
        }
    }

    public static function dataProviderForTestGetBest()
    {
        return array(
            array('gzip;q=1.0, identity; q=0.5, *;q=0', array('identity'), 'identity'),
            array('gzip;q=0.5, identity; q=0.5, *;q=0.7', array('bzip', 'foo'), 'bzip'),
            array('gzip;q=0.7, identity; q=0.5, *;q=0.7', array('gzip', 'foo'), 'gzip'),
            # Quality of source factors
            array('gzip;q=0.7,identity', array('identity;q=0.5', 'gzip;q=0.9'), 'gzip;q=0.9'),
        );
    }

    public function testGetBestRespectsQualityOfSource()
    {
        $accept = $this->negotiator->getBest('gzip;q=0.7,identity', array('identity;q=0.5', 'gzip;q=0.9'));
        $this->assertInstanceOf('Negotiation\AcceptEncoding', $accept);
        $this->assertEquals('gzip', $accept->getType());
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeader
     */
    public function testParseAcceptHeader($header, $expected)
    {
        $accepts = $this->call_private_method('Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertSame($expected, $accepts);
    }

    public static function dataProviderForTestParseAcceptHeader()
    {
        return array(
            array('gzip,deflate,sdch', array('gzip', 'deflate', 'sdch')),
            array("gzip, deflate\t,sdch", array('gzip', 'deflate', 'sdch')),
            array('gzip;q=1.0, identity; q=0.5, *;q=0', array('gzip;q=1.0', 'identity; q=0.5', '*;q=0')),
        );
    }
}
