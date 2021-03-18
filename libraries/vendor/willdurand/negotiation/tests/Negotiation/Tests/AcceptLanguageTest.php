<?php

namespace Negotiation\Tests;

use Negotiation\AcceptLanguage;

class AcceptLanguageTest extends TestCase
{

    /**
     * @dataProvider dataProviderForGetType
     */
    public function testGetType($header, $expected)
    {
        $accept = new AcceptLanguage($header);
        $actual = $accept->getType();
        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderForGetType()
    {
        return array(
           array('en;q=0.7', 'en'),
           array('en-GB;q=0.8', 'en-gb'),
           array('da', 'da'),
           array('en-gb;q=0.8', 'en-gb'),
           array('es;q=0.7', 'es'),
           array('fr ; q= 0.1', 'fr'),
           array('', null),
           array(null, null),
       );
    }

    /**
     * @dataProvider dataProviderForGetValue
     */
    public function testGetValue($header, $expected)
    {
        $accept = new AcceptLanguage($header);
        $actual = $accept->getValue();
        $this->assertEquals($expected, $actual);

    }

    public static function dataProviderForGetValue()
    {
        return array(
           array('en;q=0.7', 'en;q=0.7'),
           array('en-GB;q=0.8', 'en-GB;q=0.8'),
        );
    }
}
