<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1MessageSigner*.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1MessageSignerTest extends TestCase
{
	/**
	 * The test objects.
	 * @var array
	 */
	protected $objects = array();

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->objects['HMAC'] = new JOAuth1MessageSignerHMAC;
		$this->objects['PLAINTEXT'] = new JOAuth1MessageSignerPlaintext;
		$this->objects['RSA'] = new JOAuth1MessageSignerRSA(
'-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDBNki8uI5XHOfZ+rRXgn8lr/n1lLrUv3gpbdcbFW3Qo1aQLEE7
tSZQ11frL3mK8m74RYaHDeLv/INx1TKpuOs/7MVtjtLnk+TJ+HCAXNCKrPdpTcEr
K5QujnXIeNJxFTQxfb0HoQFbV3uY0BEO9oKcBSUCXLQrZmP5BRYrE2JuEwIDAQAB
AoGALTiyX5Fmp1b5oRg/m3fMoJqGi4crD15dFn1B6nHiiQGh4g6pmfr1I9izGDW9
YdsKRAupx/RlGDxm237F49mHHcAGcf8vU1s53DIbM32Rw/sdGoRcpIsgqbYpyg1+
AZ2J6HBScVfKRGxNF90NHNuTjqgDzxXueuNkOv9b2MzYtUkCQQDecPn385LBojlf
ca1pWzlkhJuZEzY6rXKBfneb7HlEGQlV94IcAd12paAsA6j/qCeT7k8d/zoE4cA9
BX/wJq6FAkEA3lxtwRMgX67fX2BuTPFpTRp45hPl0v2EthJ/2xQDNOaEwu+I2oE3
LBA7Q0l/p5JE2dlWZ9qH1reJyE0mBwUJtwJABIVfTz7QGMdnSx1QXPfRrs1TLEVL
sN2dmiwr2itwO4YWvnyNVGxlR+gN3VcltCyCwWryiLWLRFYiRhs6gnMG0QJBAJKy
/gHkQXZ+44WEFCEVH/irX1nDhNuHQFfyqoF5mYf8EVieOXaWyzR53O9OfTarrFrh
JT1NElNZMUCBjXYSH88CQQC2ewzfe/RzWJht9Rkk6wo3utgaA8DestgbZ1ji17Qc
K5YDttux/1/oK2MOfmYPRn9rwq6eyCYS8xoEN+wwQ9Hh
-----END RSA PRIVATE KEY-----'
		);
	}

	/**
	 * Data for sign method.
	 *
	 * @return  array  Array of data for testSign
	 *
	 * @since   12.3
	 */
	public function dataSign()
	{
		return array(
			'Dataset 1' => array(
				'POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q'
					. '%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_'
					. 'key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_m'
					. 'ethod%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk'
					. '9d7dh3k39sjv7',
				'This is the client secret - do not tell anybody.',
				'This is the credential secret - do not tell anybody.',
				array(
					'plaintext' => 'This is the client secret - do not tell anybody.&This is the credential secret - do not tell anybody.',
					'hmac' => '30A/st+OP9i/NPxC0bs4iaT41Fk=',
					'rsa' => 'dcGbEPzdkYGQe6DkwO6R9oozHwOEDUfC7ARIIvD2CXwKjQ3gqqaGhq/c5qmcbl'
						. 'KURm2GLdAsyCzBU1UK8BDjwJeGHjJk1MTiAsJOvzGT9gdD5O+/H9vXIr4HRhy4'
						. 'XHX86k2m5FUI8ZVLgwu3l2PYgnL+azoTRCLW87NEc8rFG8U='
				)
			)
		);
	}

	/**
	 * Test the plaintext sign method.
	 *
	 * @param   string  $baseString        The base string.
	 * @param   string  $clientSecret      The client secret.
	 * @param   string  $credentialSecret  The credential secret.
	 * @param   array   $expected          Expected values.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @dataProvider  dataSign
	 */
	public function testSignPlaintext($baseString, $clientSecret, $credentialSecret, $expected)
	{
		if (isset($expected['plaintext']))
		{
			$this->assertEquals($expected['plaintext'], $this->objects['PLAINTEXT']->sign($baseString, $clientSecret, $credentialSecret));
		}
	}

	/**
	 * Test the HMAC sign method.
	 *
	 * @param   string  $baseString        The base string.
	 * @param   string  $clientSecret      The client secret.
	 * @param   string  $credentialSecret  The credential secret.
	 * @param   array   $expected          Expected values.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @dataProvider  dataSign
	 */
	public function testSignHMAC($baseString, $clientSecret, $credentialSecret, $expected)
	{
		if (isset($expected['hmac']))
		{
			$this->assertEquals($expected['hmac'], $this->objects['HMAC']->sign($baseString, $clientSecret, $credentialSecret));
		}
	}

	/**
	 * Test the RSA sign method.
	 *
	 * @param   string  $baseString        The base string.
	 * @param   string  $clientSecret      The client secret.
	 * @param   string  $credentialSecret  The credential secret.
	 * @param   array   $expected          Expected values.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @dataProvider  dataSign
	 */
	public function testSignRSA($baseString, $clientSecret, $credentialSecret, $expected)
	{
		if (isset($expected['rsa']))
		{
			$this->assertEquals($expected['rsa'], $this->objects['RSA']->sign($baseString, $clientSecret, $credentialSecret));
		}
	}
}
