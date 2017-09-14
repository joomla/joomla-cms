<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *
 * @copyright Copyright (c) 2015, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ReCaptcha;

class RequestParametersTest extends \PHPUnit_Framework_TestCase
{

    public function provideValidData()
    {
        return array(
            array('SECRET', 'RESPONSE', 'REMOTEIP', 'VERSION',
                array('secret' => 'SECRET', 'response' => 'RESPONSE', 'remoteip' => 'REMOTEIP', 'version' => 'VERSION'),
                'secret=SECRET&response=RESPONSE&remoteip=REMOTEIP&version=VERSION'),
            array('SECRET', 'RESPONSE', null, null,
                array('secret' => 'SECRET', 'response' => 'RESPONSE'),
                'secret=SECRET&response=RESPONSE'),
        );
    }

    /**
     * @dataProvider provideValidData
     */
    public function testToArray($secret, $response, $remoteIp, $version, $expectedArray, $expectedQuery)
    {
        $params = new RequestParameters($secret, $response, $remoteIp, $version);
        $this->assertEquals($params->toArray(), $expectedArray);
    }

    /**
     * @dataProvider provideValidData
     */
    public function testToQueryString($secret, $response, $remoteIp, $version, $expectedArray, $expectedQuery)
    {
        $params = new RequestParameters($secret, $response, $remoteIp, $version);
        $this->assertEquals($params->toQueryString(), $expectedQuery);
    }
}
