<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 */
class JCacheStorageTest extends JoomlaTestCase
{
	/**
	 * @var	JCacheStorage
	 * @access protected
	 */
	protected $object;

	/**
	 * @var errors
	 * @access protected
	 */
	protected static $errors;
	protected $savedErrorState;

		/**
	 * @var actualError
	 */
	protected static $actualError;

	/**
	 * @var  bool
	 */
	protected $apcAvailable;

	/**
	 * @var  bool
	 */
	protected $eacceleratorAvailable;

	/**
	 * @var  bool
	 */
	protected $memcacheAvailable;

	/**
	 * @var  bool
	 */
	protected $xcacheAvailable;

	/**
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param	JException	The JException object from JError
	 *
	 * @return	bool	To not continue with JError processing
	 */
	static function errorCallback( &$error )
	{
		JCacheStorageTest::$actualError['code'] = $error->get('code');
		JCacheStorageTest::$actualError['msg'] = $error->get('message');
		JCacheStorageTest::$actualError['info'] = $error->get('info');
		return false;
	}

	protected function setUp()
	{
		include_once JPATH_PLATFORM.'/joomla/cache/cache.php';
		include_once JPATH_PLATFORM.'/joomla/cache/storage.php';

		$this->saveErrorHandlers();
		$this->setErrorCallback('JCacheStorageTest');
		JCacheStorageTest::$actualError = array();

		$this->object = new JCacheStorage;

		$this->apcAvailable = extension_loaded('apc');
		$this->eacceleratorAvailable = extension_loaded('eaccelerator') && function_exists('eaccelerator_get');
		$this->memcacheAvailable = (extension_loaded('memcache') && class_exists('Memcache')) != true;
		$this->xcacheAvailable = extension_loaded('xcache');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 * @access protected
	 */
	protected function tearDown()
	{
		$this->setErrorhandlers($this->savedErrorState);
	}

	/**
	 * Test Cases for getInstance
	 *
	 * @return array
	 */
	function casesGetInstance()
	{
		return array(
			'defaultfile' => array(
				'file',
				array(
					'application'	=> null,
					'language'		=> 'en-GB',
					'locking'		=> true,
					'lifetime'		=> null,
					'cachebase'		=> JPATH_BASE.'/cache',
					'now'		=> time(),
				),
				'JCacheStorageFile',
			),
			'defaultapc' => array(
				'apc',
				array(
					'application'	=> null,
					'language'		=> 'en-GB',
					'locking'		=> true,
					'lifetime'		=> null,
					'now'		=> time(),
				),
				$this->apcAvailable ? 'JCacheStorageApc' : false,
			),
			'defaulteaccelerator' => array(
				'eaccelerator',
				array(
					'application'	=> null,
					'language'		=> 'en-GB',
					'locking'		=> true,
					'lifetime'		=> null,
					'now'		=> time(),
				),
				$this->eacceleratorAvailable ? 'JCacheStorageEaccelerator' : false,
			),
			'defaultmemcache' => array(
				'memcache',
				array(
					'application'	=> null,
					'language'		=> 'en-GB',
					'locking'		=> true,
					'lifetime'		=> null,
					'now'		=> time(),
				),
				$this->memcacheAvailable ? 'JCacheStorageMemcache' : false,
			),
			'defaultxcache' => array(
				'xcache',
				array(
					'application'	=> null,
					'language'		=> 'en-GB',
					'locking'		=> true,
					'lifetime'		=> null,
					'now'		=> time(),
				),
				$this->xcacheAvailable ? 'JCacheStorageXcache' : false,
			),
		);
	}

	/**
	 * Testing getInstance
	 *
	 * @param	string	cache storage
	 * @param	array	options for cache storage
	 * @param	string	name of expected cache storage class
	 *
	 * @return void
	 * @dataProvider casesGetInstance
	 */
	public function testGetInstance($handler, $options, $expClass)
	{
		if (is_bool($expClass)) {
			$this->markTestSkipped('The caching method '.$handler.' is not supported on this system.');
		}

		$this->object = JCacheStorage::getInstance($handler, $options);
		if (class_exists('JTestConfig')) {
			$config = new JTestConfig;
		}

		$this->assertThat(
			$this->object,
			$this->isInstanceOf($expClass),
			'The wrong class was received.'
		);

		$this->assertThat(
			$this->object->_application,
			$this->equalTo($options['application']),
			'Unexpected value for _application.'
		);

		$this->assertThat(
			$this->object->_language,
			$this->equalTo($options['language']),
			'Unexpected value for _language.'
		);

		$this->assertThat(
			$this->object->_locking,
			$this->equalTo($options['locking']),
			'Unexpected value for _locking.'
		);

		$this->assertThat(
			$this->object->_lifetime,
//			$this->equalTo(empty($options['lifetime']) ? $config->get('cachetime')*60 : $options['lifetime']*60),
			$this->equalTo(60),
			'Unexpected value for _lifetime.'
		);

		$this->assertLessThan(
			isset($config->cachetime) ? $config->cachetime : 900,
			abs($this->object->_now - time()),
			'Unexpected value for configuration lifetime.'
		);
	}

	/**
	 * Testing get().
	 *
	 * @return void
	 */
	public function testGet()
	{
		$this->assertThat(
			$this->object->get(1, '', time()),
			$this->equalTo(false)
		);
	}

	/**
	 * Testing store().
	 *
	 * @return void
	 */
	public function testStore()
	{
		$this->assertThat(
			$this->object->store(1, '', 'Cached'),
			$this->isTrue()
		);
	}

	/**
	 * Testing remove().
	 *
	 * @return void
	 */
	public function testRemove()
	{
		$this->assertThat(
			$this->object->remove(1, ''),
			$this->isTrue()
		);
	}

	/**
	 * Testing clean().
	 *
	 * @return void
	 */
	public function testClean()
	{
		$this->assertThat(
			$this->object->clean('', 'group'),
			$this->isTrue()
		);
	}

	/**
	 * Testing gc().
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->assertThat(
			$this->object->gc(),
			$this->isTrue()
		);
	}

	/**
	 * Testing test().
	 *
	 * @return void
	 */
	public function testTest()
	{
		$this->assertThat(
			$this->object->test(),
			$this->isTrue()
		);
	}
}
?>
