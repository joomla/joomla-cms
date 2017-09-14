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

namespace ReCaptcha\RequestMethod;

use ReCaptcha\RequestParameters;

class SocketPostTest extends \PHPUnit_Framework_TestCase
{

    public function testSubmitSuccess()
    {
        $socket = $this->getMock('\\ReCaptcha\\RequestMethod\\Socket', array('fsockopen', 'fwrite', 'fgets', 'feof', 'fclose'));
        $socket->expects($this->once())
                ->method('fsockopen')
                ->willReturn(true);
        $socket->expects($this->once())
                ->method('fwrite');
        $socket->expects($this->once())
                ->method('fgets')
                ->willReturn("HTTP/1.1 200 OK\n\nRESPONSEBODY");
        $socket->expects($this->exactly(2))
                ->method('feof')
                ->will($this->onConsecutiveCalls(false, true));
        $socket->expects($this->once())
                ->method('fclose')
                ->willReturn(true);

        $ps = new SocketPost($socket);
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        $this->assertEquals('RESPONSEBODY', $response);
    }

    public function testSubmitBadResponse()
    {
        $socket = $this->getMock('\\ReCaptcha\\RequestMethod\\Socket', array('fsockopen', 'fwrite', 'fgets', 'feof', 'fclose'));
        $socket->expects($this->once())
                ->method('fsockopen')
                ->willReturn(true);
        $socket->expects($this->once())
                ->method('fwrite');
        $socket->expects($this->once())
                ->method('fgets')
                ->willReturn("HTTP/1.1 500 NOPEn\\nBOBBINS");
        $socket->expects($this->exactly(2))
                ->method('feof')
                ->will($this->onConsecutiveCalls(false, true));
        $socket->expects($this->once())
                ->method('fclose')
                ->willReturn(true);

        $ps = new SocketPost($socket);
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        $this->assertEquals(SocketPost::BAD_RESPONSE, $response);
    }

    public function testSubmitBadRequest()
    {
        $socket = $this->getMock('\\ReCaptcha\\RequestMethod\\Socket', array('fsockopen'));
        $socket->expects($this->once())
                ->method('fsockopen')
                ->willReturn(false);
        $ps = new SocketPost($socket);
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        $this->assertEquals(SocketPost::BAD_REQUEST, $response);
    }
}
