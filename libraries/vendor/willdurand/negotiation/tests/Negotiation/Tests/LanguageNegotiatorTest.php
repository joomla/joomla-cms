<?php

namespace Negotiation\Tests;

use Negotiation\Exception\InvalidArgument;
use Negotiation\LanguageNegotiator;

class LanguageNegotiatorTest extends TestCase
{

    /**
     * @var LanguageNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new LanguageNegotiator();
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($accept, $priorities, $expected)
    {
        try {
            $accept = $this->negotiator->getBest($accept, $priorities);

            if (null === $accept) {
                $this->assertNull($expected);
            } else {
                $this->assertInstanceOf('Negotiation\AcceptLanguage', $accept);
                $this->assertEquals($expected, $accept->getValue());
            }
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e);
        }
    }

    public static function dataProviderForTestGetBest()
    {
        return array(
            array('en, de', array('fr'), null),
            array('foo, bar, yo', array('baz', 'biz'), null),
            array('fr-FR, en;q=0.8', array('en-US', 'de-DE'), 'en-US'),
            array('en, *;q=0.9', array('fr'), 'fr'),
            array('foo, bar, yo', array('yo'), 'yo'),
            array('en; q=0.1, fr; q=0.4, bu; q=1.0', array('en', 'fr'), 'fr'),
            array('en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2', array('en', 'fu'), 'fu'),
            array('', array('en', 'fu'), new InvalidArgument('The header string should not be empty.')),
            array('fr, zh-Hans-CN;q=0.3', array('fr'), 'fr'),
            # Quality of source factors
            array('en;q=0.5,de', array('de;q=0.3', 'en;q=0.9'), 'en;q=0.9'),
        );
    }

    public function testGetBestRespectsQualityOfSource()
    {
        $accept = $this->negotiator->getBest('en;q=0.5,de', array('de;q=0.3', 'en;q=0.9'));
        $this->assertInstanceOf('Negotiation\AcceptLanguage', $accept);
        $this->assertEquals('en', $accept->getType());
    }

    /**
     * @dataProvider dataProviderForTestParseHeader
     */
    public function testParseHeader($header, $expected)
    {
        $accepts = $this->call_private_method('Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertSame($expected, $accepts);
    }

    public static function dataProviderForTestParseHeader()
    {
        return array(
            array('en; q=0.1, fr; q=0.4, bu; q=1.0', array('en; q=0.1', 'fr; q=0.4', 'bu; q=1.0')),
            array('en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2', array('en; q=0.1', 'fr; q=0.4', 'fu; q=0.9', 'de; q=0.2')),
        );
    }
}
