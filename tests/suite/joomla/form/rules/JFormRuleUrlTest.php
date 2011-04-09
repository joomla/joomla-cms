<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Form
 *
 */
class JFormRuleUrlTest extends JoomlaTestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		jimport('joomla.form.formrule');
		jimport('joomla.utilities.xmlelement');
		require_once JPATH_PLATFORM.'/joomla/form/rules/url.php';
	}

	/**
	 * Test the JFormRuleUrl::test method.
	 */
	public function testUrl()
	{
		// Initialise variables.

		$rule = new JFormRuleUrl;
		$xml = simplexml_load_string('<form><field name="url1" />
		<field name="url2" schemes="gopher" /></form>', 'JXMLElement');

		// Test fail conditions.
		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'mydomain.com'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);		
		$this->assertThat(
			$rule->test($xml->field[0], 'httpmydomain.com'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);				
			$this->assertThat(
			$rule->test($xml->field[0], 'http:///mydomain.com'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'http//mydomain.com'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);				

		$this->assertThat(
			$rule->test($xml->field[0], 'http://:80'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);				

		$this->assertThat(
			$rule->test($xml->field[0], 'http://user@:80'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);				
		
		$this->assertThat(
			$rule->test($xml->field[0], 'http:/mydomain.com'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);				

		$this->assertThat(
			$rule->test($xml->field[1], 'http://mydomain.com'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);		
		
		// Test pass conditions.		
		$this->assertThat(
			$rule->test($xml->field[0], 'http://mydomain.com'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);	
		
		$this->assertThat(
			$rule->test($xml->field[0], 'HTTP://mydomain.com'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);			
		$this->assertThat(
			$rule->test($xml->field[0], 'ftp://ftp.is.co.za/rfc/rfc1808.txt'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'http://www.ietf.org/rfc/rfc2396.txt'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'ldap://[2001:db8::7]/c=GB?objectClass?one'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);	
		$this->assertThat(
			$rule->test($xml->field[0], 'mailto:John.Doe@example.com'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);				
		$this->assertThat(
			$rule->test($xml->field[0], 'news:comp.infosystems.www.servers.unix'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'tel:+1-816-555-1212'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);		
		$this->assertThat(
			$rule->test($xml->field[0], 'telnet://192.0.2.16:80/'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'file:document.extension'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], 'gopher://gopher.mydomain.com'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);		
		
		$this->assertThat(
			$rule->test($xml->field[0], 'urn:oasis:names:specification:docbook:dtd:xml:4.1.2'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);		
		$this->assertThat(
			$rule->test($xml->field[0], 'http://mydomain.com/Laguna%20Beach.htm'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);		
		$this->assertThat(
			$rule->test($xml->field[0], 'http://mydomain.com/объектов'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);		
		
		
	}
}