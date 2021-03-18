<?php

namespace Negotiation\Tests;

use Negotiation\Accept;

class AcceptTest extends TestCase
{
    public function testGetParameter()
    {
        $accept = new Accept('foo/bar; q=1; hello=world');

        $this->assertTrue($accept->hasParameter('hello'));
        $this->assertEquals('world', $accept->getParameter('hello'));
        $this->assertFalse($accept->hasParameter('unknown'));
        $this->assertNull($accept->getParameter('unknown'));
        $this->assertFalse($accept->getParameter('unknown', false));
        $this->assertSame('world', $accept->getParameter('hello', 'goodbye'));
    }

    /**
     * @dataProvider dataProviderForTestGetNormalizedValue
     */
    public function testGetNormalizedValue($header, $expected)
    {
        $accept = new Accept($header);
        $actual = $accept->getNormalizedValue();
        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderForTestGetNormalizedValue()
    {
        return array(
            array('text/html; z=y; a=b; c=d', 'text/html; a=b; c=d; z=y'),
            array('application/pdf; q=1; param=p',  'application/pdf; param=p')
        );
    }

    /**
     * @dataProvider dataProviderForGetType
     */
    public function testGetType($header, $expected)
    {
        $accept = new Accept($header);
        $actual = $accept->getType();
        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderForGetType()
    {
        return array(
            array('text/html;hello=world', 'text/html'),
            array('application/pdf', 'application/pdf'),
            array('application/xhtml+xml;q=0.9', 'application/xhtml+xml'),
            array('text/plain; q=0.5', 'text/plain'),
            array('text/html;level=2;q=0.4', 'text/html'),
            array('text/html ; level = 2   ; q = 0.4', 'text/html'),
            array('text/*', 'text/*'),
            array('text/* ;q=1 ;level=2', 'text/*'),
            array('*/*', '*/*'),
            array('*', '*/*'),
            array('*/* ; param=555', '*/*'),
            array('* ; param=555', '*/*'),
            array('TEXT/hTmL;leVel=2; Q=0.4', 'text/html'),
        );
    }

    /**
     * @dataProvider dataProviderForGetValue
     */
    public function testGetValue($header, $expected)
    {
        $accept = new Accept($header);
        $actual = $accept->getValue();
        $this->assertEquals($expected, $actual);

    }

    public static function dataProviderForGetValue()
    {
        return array(
            array('text/html;hello=world  ;q=0.5', 'text/html;hello=world  ;q=0.5'),
            array('application/pdf', 'application/pdf'),
        );
    }
}
