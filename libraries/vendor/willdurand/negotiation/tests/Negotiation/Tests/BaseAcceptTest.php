<?php

namespace Negotiation\Tests;

use Negotiation\BaseAccept;

class BaseAcceptTest extends TestCase
{
    /**
     * @dataProvider dataProviderForParseParameters
     */
    public function testParseParameters($value, $expected)
    {
        $accept     = new DummyAccept($value);
        $parameters = $accept->getParameters();

        // TODO: hack-ish... this is needed because logic in BaseAccept
        //constructor drops the quality from the parameter set.
        if (false !== strpos($value, 'q')) {
            $parameters['q'] = $accept->getQuality();
        }

        $this->assertCount(count($expected), $parameters);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $parameters);
            $this->assertEquals($value, $parameters[$key]);
        }
    }

    public static function dataProviderForParseParameters()
    {
        return array(
            array(
                'application/json ;q=1.0; level=2;foo= bar',
                array(
                    'q' => 1.0,
                    'level' => 2,
                    'foo'   => 'bar',
                ),
            ),
            array(
                'application/json ;q = 1.0; level = 2;     FOO  = bAr',
                array(
                    'q' => 1.0,
                    'level' => 2,
                    'foo'   => 'bAr',
                ),
            ),
            array(
                'application/json;q=1.0',
                array(
                    'q' => 1.0,
                ),
            ),
            array(
                'application/json;foo',
                array(),
            ),
        );
    }

    /**
     * @dataProvider dataProviderBuildParametersString
     */

    public function testBuildParametersString($value, $expected)
    {
        $accept = new DummyAccept($value);

        $this->assertEquals($expected, $accept->getNormalizedValue());
    }

    public static function dataProviderBuildParametersString()
    {
        return array(
            array('media/type; xxx = 1.0;level=2;foo=bar', 'media/type; foo=bar; level=2; xxx=1.0'),
        );
    }
}

class DummyAccept extends BaseAccept
{
}
