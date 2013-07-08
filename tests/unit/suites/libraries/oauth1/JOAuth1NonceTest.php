<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Message.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1NonceTest extends TestCase
{
	/**
	 * The test object.
	 * @var JOAuth1Message
	 */
	protected $object;

	/**
	 * The DB object.
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * The options object.
	 * @var JDatabaseDriver
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->getMockBuilder('JDatabaseDriverSqlite')
			->disableOriginalConstructor()
			->getMock();

		$this->options = new JRegistry;

		$this->object = new JOAuth1Nonce($this->options, $this->db);
	}

	/**
	 * Data for testValidate
	 *
	 * @return  array  Provided data.
	 *
	 * @since   12.3
	 */
	public function dataValidate()
	{
		return array(
			'valid nonce' => array(
				time(),
				true,
				0,
				true
			),
			'old nonce' => array(
				time() - 60,
				false,
				0,
				false
			),
			'used nonce' => array(
				time(),
				true,
				1,
				false
			)
		);
	}

	/**
	 * Tests the sign() method.
	 *
	 * @param   integer  $timestamp       The timestamp to validate.
	 * @param   boolean  $timestampValid  Whether the timestamp is valid.
	 * @param   integer  $selectReturn    The value the count select should return.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @dataProvider  dataValidate
	 */
	public function testValidate($timestamp, $timestampValid, $selectReturn)
	{
		$consumerKey = 'consumerkey';
		$nonce = 'nonce';
		$token = 'TokenString';

		if (!$timestampValid)
		{
			$this->assertFalse($this->object->validate($nonce, $consumerKey, $timestamp, $token));

			return;
		}

		$query1 = $this->getMockBuilder('JDatabaseQuerySqlite')
			->disableOriginalConstructor()
			->getMock();

		$query2 = $this->getMockBuilder('JDatabaseQuerySqlite')
			->disableOriginalConstructor()
			->getMock();

		$this->db->expects($this->at(0))
			->method('getQuery')
			->will($this->returnValue($query1));

		$this->db->expects($this->at(1))
			->method('quote')
			->will($this->returnArgument(0));

		$this->db->expects($this->at(2))
			->method('quote')
			->will($this->returnArgument(0));

		$query1->expects($this->at(0))
			->method('select')
			->with('count(nonce_id)')
			->will($this->returnSelf());

		$query1->expects($this->at(1))
			->method('from')
			->with('#__oauth_nonce')
			->will($this->returnSelf());

		$query1->expects($this->at(2))
			->method('where')
			->with('consumer_key = ' . $consumerKey)
			->will($this->returnSelf());

		$query1->expects($this->at(3))
			->method('where')
			->with('timestamp = ' . $timestamp)
			->will($this->returnSelf());

		$query1->expects($this->at(4))
			->method('where')
			->with('nonce = ' . $nonce)
			->will($this->returnSelf());

		$this->db->expects($this->at(3))
			->method('setQuery')
			->with($query1);

		$this->db->expects($this->at(4))
			->method('loadResult')
			->will($this->returnValue($selectReturn));

		if ($selectReturn > 0)
		{
			$this->assertFalse($this->object->validate($nonce, $consumerKey, $timestamp, $token));

			return;
		}

		$nonceObject = (object) array('consumer_key' => $consumerKey, 'timestamp' => $timestamp, 'nonce' => $nonce);

		$this->db->expects($this->at(5))
			->method('insertObject')
			->with('#__oauth_nonce', $nonceObject, 'nonce_id');

		$this->db->expects($this->at(6))
		->method('getQuery')
		->with(true)
		->will($this->returnValue($query2));

		$query2->expects($this->once())
			->method('delete')
			->with('#__oauth_nonce')
			->will($this->returnSelf());

		$query2->expects($this->once())
			->method('where')
			->will($this->returnSelf());

		$this->db->expects($this->at(7))
			->method('setQuery')
			->with($query2);

		$this->db->expects($this->at(8))
			->method('execute');

		$this->assertTrue($this->object->validate($nonce, $consumerKey, $timestamp, $token));
	}
}
