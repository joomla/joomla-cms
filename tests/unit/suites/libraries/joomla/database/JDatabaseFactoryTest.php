<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       3.2
 */
class JDatabaseFactoryTest extends TestCaseDatabase
{
	/**
	 * Object being tested
	 *
	 * @var    JDatabaseFactory
	 * @since  3.2
	 */
	protected static $instance;

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function setUp()
	{
		parent::setUp();

		static::$instance = JDatabaseFactory::getInstance();
	}

	/**
	 * Test for the JDatabaseFactory::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetInstance()
	{
		$this->assertThat(
			JDatabaseFactory::getInstance(),
			$this->isInstanceOf('JDatabaseFactory'),
			'Tests that getInstance returns an instance of JDatabaseFactory.'
		);
	}

	/**
	 * Test for the JDatabaseFactory::getExporter method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetExporter()
	{
		$object = static::$instance;

		$this->assertThat(
			$object->getExporter('mysqli'),
			$this->isInstanceOf('JDatabaseExporterMysqli'),
			'Tests that getExporter with "mysqli" param returns an instance of JDatabaseExporterMysqli.'
		);

		try
		{
			$object->getExporter('mariadb');
		}
		catch (RuntimeException $e)
		{
			$this->assertThat(
				$e->getMessage(),
				$this->equalTo('Database Exporter not found.'),
				'Tests that getExporter with "mariadb" param throws an exception due to a class not existing.'
			);
		}

		$exporter = $object->getExporter('mysqli', static::$driver);

		$this->assertThat(
			TestReflection::getValue($exporter, 'db'),
			$this->isInstanceOf('JDatabaseDriverSqlite'),
			'Tests that getExporter with the test database driver returns an instance of JDatabaseDriverSqlite.'
		);
	}

	/**
	 * Test for the JDatabaseFactory::getImporter method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetImporter()
	{
		$object = static::$instance;

		$this->assertThat(
			$object->getImporter('mysqli'),
			$this->isInstanceOf('JDatabaseImporterMysqli'),
			'Tests that getImporter with "mysqli" param returns an instance of JDatabaseImporterMysqli.'
		);

		try
		{
			$object->getImporter('mariadb');
		}
		catch (RuntimeException $e)
		{
			$this->assertThat(
				$e->getMessage(),
				$this->equalTo('Database importer not found.'),
				'Tests that getImporter with "mariadb" param throws an exception due to a class not existing.'
			);
		}

		$importer = $object->getImporter('mysqli', static::$driver);

		$this->assertThat(
			TestReflection::getValue($importer, 'db'),
			$this->isInstanceOf('JDatabaseDriverSqlite'),
			'Tests that getImporter with the test database driver returns an instance of JDatabaseDriverSqlite.'
		);
	}

	/**
	 * Test for the JDatabaseFactory::getQuery method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetQuery()
	{
		$object = static::$instance;

		$this->assertThat(
			$object->getQuery('sqlite', static::$driver),
			$this->isInstanceOf('JDatabaseQuerySqlite'),
			'Tests that getQuery with the test database driver and "sqlite" name returns an instance of JDatabaseQuerySqlite.'
		);

		try
		{
			$object->getQuery('mariadb', static::$driver);
		}
		catch (RuntimeException $e)
		{
			$this->assertThat(
				$e->getMessage(),
				$this->equalTo('Database Query class not found'),
				'Tests that getQuery with "mariadb" param throws an exception due to a class not existing.'
			);
		}
	}
}
