<?php
/**
 * @package		Joomla.UnitTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU General Public License
 */


/**
 * @package		Joomla.UnitTest
 * @subpackage	framework.mail
 */
class JMailHelperTest extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
		// Loading the MailHelper class
		jimport('joomla.mail.helper');
	}

	/**
	 * @group	framework.mail
	 * @dataProvider	getCleanLineData
	 */
	public function testCleanLine( $input, $expected )
	{
		$this->assertThat(
			JMailHelper::cleanLine( $input ),
			$this->equalTo( $expected )
		);
	}

	/**
	 * @group	framework.mail
	 * @dataProvider	getCleanTextData
	 */
	public function testCleanText( $input, $expected )
	{
		$this->assertThat(
			JMailHelper::cleanText( $input ),
			$this->equalTo( $expected )
		);
	}

	/**
	 * @group	framework.mail
	 * @dataProvider	getCleanBodyData
	 */
	public function testCleanBody( $input, $expected )
	{
		$this->assertThat(
			JMailHelper::cleanBody( $input ),
			$this->equalTo( $expected )
		);
	}

	/**
	 * @group	framework.mail
	 * @dataProvider	getCleanSubjectData
	 */
	public function testCleanSubject( $input, $expected )
	{
		$this->assertThat(
			JMailHelper::cleanSubject( $input ),
			$this->equalTo( $expected )
		);
	}

	/**
	 * @group	     framework.mail
	 * @dataProvider getCleanAddressData
	 */
	public function testCleanAddress( $input, $expected )
	{
		$this->assertThat(
			JMailHelper::cleanAddress( $input ),
			$this->equalTo( $expected )
		);
	}

	/**
	 * @group	     framework.mail
	 * @dataProvider getIsEmailAddressData
	 */
	public function testIsEmailAddress( $input, $expected )
	{
		$this->assertThat(
			JMailHelper::isEmailAddress( $input ),
			$this->equalTo( $expected )
		);
	}

	//
	// Data Providers
	//

	/**
	 * Test data for cleanLine method
	 *
	 * @return array
	 */
	static public function getCleanLineData()
	{
		return array(
			array( "test\n\nme\r\r", 'testme' ),
			array( "test%0Ame", 'testme' ),
			array( "test%0Dme", 'testme' ),
		);
	}

	/**
	 * Test data for cleanText method
	 *
	 * @return array
	 */
	static public function getCleanTextData()
	{
		return array(
			array( "test\nme", "test\nme" ),

			array( "test%0AconTenT-Type:me", 'testme' ),
			array( "test%0Dcontent-type:me", 'testme' ),
			array( "test\ncontent-type:me", 'testme' ),
			array( "test\n\ncontent-type:me", 'testme' ),
			array( "test\rcontent-type:me", 'testme' ),
			array( "test\r\rcontent-type:me", 'testme' ),
			// @TODO Should this be included array( "test\r\ncoNTent-tYPe:me", 'testme' ),

			array( "test%0Ato:me", 'testme' ),
			array( "test%0DTO:me", 'testme' ),
			array( "test\nTo:me", 'testme' ),
			array( "test\n\ntO:me", 'testme' ),
			array( "test\rto:me", 'testme' ),
			array( "test\r\rto:me", 'testme' ),
			// @TODO Should this be included array( "test\r\nto:me", 'testme' ),

			array( "test%0Acc:me", 'testme' ),
			array( "test%0DCC:me", 'testme' ),
			array( "test\nCc:me", 'testme' ),
			array( "test\n\ncC:me", 'testme' ),
			array( "test\rcc:me", 'testme' ),
			array( "test\r\rcc:me", 'testme' ),
			// @TODO Should this be included array( "test\r\ncc:me", 'testme' ),

			array( "test%0Abcc:me", 'testme' ),
			array( "test%0DBCC:me", 'testme' ),
			array( "test\nBCc:me", 'testme' ),
			array( "test\n\nbcC:me", 'testme' ),
			array( "test\rbcc:me", 'testme' ),
			array( "test\r\rbcc:me", 'testme' ),
			// @TODO Should this be included array( "test\r\nbcc:me", 'testme' ),
		);
	}

	/**
	 * Test data for cleanBody method
	 *
	 * @return array
	 */
	static public function getCleanBodyData()
	{
		return array(
			array( "testFrom: Foobar me", "test me" ),
			array( "testfrom: Foobar me", "testfrom: Foobar me" ),
			array( "testTo: Foobar me", "test me" ),
			array( "testto: Foobar me", "testto: Foobar me" ),
			array( "testCc: Foobar me", "test me" ),
			array( "testcc: Foobar me", "testcc: Foobar me" ),
			array( "testBcc: Foobar me", "test me" ),
			array( "testbcc: Foobar me", "testbcc: Foobar me" ),
			array( "testSubject: Foobar me", "test me" ),
			array( "testsubject: Foobar me", "testsubject: Foobar me" ),
			array( "testContent-type: Foobar me", "test me" ),
			array( "testcontent-type: Foobar me", "testcontent-type: Foobar me" ),
			// @TODO should this be case sensitive
		);
	}

	/**
	 * Test data for cleanBody method
	 *
	 * @return array
	 */
	static public function getCleanSubjectData()
	{
		return array(
			array( "testFrom: Foobar me", "test me" ),
			array( "testfrom: Foobar me", "testfrom: Foobar me" ),
			array( "testTo: Foobar me", "test me" ),
			array( "testto: Foobar me", "testto: Foobar me" ),
			array( "testCc: Foobar me", "test me" ),
			array( "testcc: Foobar me", "testcc: Foobar me" ),
			array( "testBcc: Foobar me", "test me" ),
			array( "testbcc: Foobar me", "testbcc: Foobar me" ),
			array( "testContent-type: Foobar me", "test me" ),
			array( "testcontent-type: Foobar me", "testcontent-type: Foobar me" ),
			// @TODO should this be case sensitive
		);
	}

	/**
	 * Test data for cleanAddress method
	 *
	 * @return array
	 */
	static public function getCleanAddressData()
	{
		return array(
			array( "testme", "testme" ),
			array( "test me", "test me" ),
			array( "test;me", "test;me" ),
			array( "test,me", "test,me" ),
			array( "test ;,me", false ),
		);
	}

	/**
	 * Test data for isEmailAddress method
	 *
	 * @return array
	 */
	static public function getIsEmailAddressData()
	{
		return array(
			array( "joe", false ),
			array( "joe@home", true ),
			array( "a@b.c", true ),
			array( "joe@home.com", true ),
			array( "joe.bob@home.com", true ),
			array("joe-bob[at]home.com", false ),
			array("joe@his.home.com", true ),
			array("joe@his.home.place", true ),
			array("joe@home.org", true ),
			array("joe@joebob.name", true ),
			array("joe.@bob.com", false ),
			array(".joe@bob.com", false ),
			array("joe<>bob@bob.come", false ),
			array("joe&bob@bob.com", true ),
			array("~joe@bob.com", false ),
			array("joe$@bob.com", false ),
			array("joe+bob@bob.com", true ),
			array("o'reilly@there.com", false )
		);
	}
}
