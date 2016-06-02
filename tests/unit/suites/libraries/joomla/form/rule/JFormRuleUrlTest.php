<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleUrl.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleUrlTest extends TestCase
{
	/**
	 * Test the JFormRuleUrl::test method.
	 *
	 * @param   string  $xmlfield  @todo
	 * @param   string  $url       @todo
	 * @param   string  $expected  @todo
	 *
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testUrl($xmlfield, $url, $expected)
	{
		$rule = new JFormRuleUrl;

		// The field allows you to optionally limit the accepted schemes to a specific list.
		// Url1 tests without a list, Url2 tests with a list.
		$xml = simplexml_load_string('<form><field name="url1" /><field name="url2" schemes="gopher" /></form>');

		if ($xmlfield == '0')
		{
			if ($expected == 'false')
			{
				// Test fail conditions.
				$this->assertThat(
					$rule->test($xml->field[0], $url),
					$this->isFalse(),
					'Line:' . __LINE__ . ' The rule should return' . $expected . '.'
				);
			}
			if ($expected == 'true')
			{
				// Test pass conditions.
				$this->assertThat(
					$rule->test($xml->field[0], $url),
					$this->isTrue(),
					'Line:' . __LINE__ . ' The rule should return' . $expected . '.'
				);
			}
		}
		if ($xmlfield == '1')
		{
			if ($expected == 'false')
			{
				// Test fail conditions.
				$this->assertThat(
					$rule->test($xml->field[1], $url),
					$this->isFalse(),
					'Line:' . __LINE__ . ' The rule should return' . $expected . '.'
				);
			}
			if ($expected == 'true')
			{
				// Test pass conditions.
				$this->assertThat(
					$rule->test($xml->field[1], $url),
					$this->isTrue(),
					'Line:' . __LINE__ . ' The rule should return' . $expected . '.'
				);
			}
		}
	}

	/**
	 * Test...
	 *
	 * @return array
	 */
	public function provider()
	{
		// Most test urls are directly from or based on the RFCs noted in the rule.
		return
			array(
				array('Simple String' => '0', 'bogus', 'false'),
				array('No scheme' => '0', 'mydomain.com', 'false'),
				array('No ://' => '0', 'httpmydomain.com', 'false'),
				array('Three slashes' => '0', 'http:///mydomain.com', 'false'),
				array('No :' => '0', 'http//mydomain.com', 'false'),
				array('Port only' => '0', 'http://:80', 'false'),
				array('Improper @' => '0', 'http://user@:80', 'false'),
				array('array(http with one slash' => '0', 'http:/mydomain.com', 'false'),
				array('Scheme not in options list' => '1', 'http://mydomain.com', 'false'),
				array('http' => '0', 'http://mydomain.com', 'true'),
				array('Upper case scheme' => '0', 'HTTP://mydomain.com', 'true'),
				array('FTP' => '0', 'ftp://ftp.is.co.za/rfc/rfc1808.txt', 'true'),
				array('Path with slash' => '0', 'http://www.ietf.org/rfc/rfc2396.txt', 'true'),
				array('LDAP' => '0', 'ldap://[2001:db8::7]/c=GB?objectClass?one', 'true'),
				array('Mailto' => '0', 'mailto:John.Doe@example.com', 'true'),
				array('News' => '0', 'news:comp.infosystems.www.servers.unix', 'true'),
				array('Tel with +' => '0', 'tel:+1-816-555-1212', 'true'),
				array('Telnet to IP with port' => '0', 'telnet://192.0.2.16:80/', 'true'),
				array('File with no slashes' => '0', 'file:document.extension', 'true'),
				array('File with 3 slashes' => '0', 'file:///document.extension', 'true'),
				array('Only gopher allowed' => '1', 'gopher://gopher.mydomain.com', 'true'),
				array('URN' => '0', 'urn:oasis:names:specification:docbook:dtd:xml:4.1.2', 'true'),
				array('Space in path' => '0', 'http://mydomain.com/Laguna%20Beach.htm', 'true'),
				array('UTF-8 in path' => '0', 'http://mydomain.com/объектов', 'true'),
				array('Puny code in domain' => '0', 'http://www.österreich.at', 'true'),
			);
	}
}
