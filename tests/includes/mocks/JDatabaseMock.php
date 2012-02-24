<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JDatabase.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class JDatabaseGlobalMock
{
	/**
	 * A query string or object.
	 *
	 * @var    mixed
	 * @since  11.3
	 */
	public static $lastQuery = null;

	/**
	 * Creates and instance of the mock JDatabase object.
	 *
	 * @param   object  $test   A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'connect',
			'connected',
			'dropTable',
			'escape',
			'execute',
			'fetchArray',
			'fetchAssoc',
			'fetchObject',
			'freeResult',
			'getAffectedRows',
			'getCollation',
			'getConnectors',
			'getDateFormat',
			'getErrorMsg',
			'getErrorNum',
			'getInstance',
			'getLog',
			'getNullDate',
			'getNumRows',
			'getPrefix',
			'getQuery',
			'getTableColumns',
			'getTableCreate',
			'getTableKeys',
			'getTableList',
			'getUtfSupport',
			'getVersion',
			'insertId',
			'insertObject',
			'loadAssoc',
			'loadAssocList',
			'loadColumn',
			'loadObject',
			'loadObjectList',
			'loadResult',
			'loadRow',
			'loadRowList',
			'lockTable',
			'query',
			'quote',
			'quoteName',
			'renameTable',
			'replacePrefix',
			'select',
			'setQuery',
			'setUTF',
			'splitSql',
			'test',
			'transactionCommit',
			'transactionRollback',
			'transactionStart',
			'unlockTables',
			'updateObject',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JDatabaseDriver',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		// Mock selected methods.
		$test->assignMockReturns(
			$mockObject, array(
				'getNullDate' => '0000-00-00 00:00:00',
				'getDateFormat' => 'Y-m-d H:i:s'
			)
		);

		$test->assignMockCallbacks(
			$mockObject,
			array(
				'getQuery' => array(get_called_class(), 'mockGetQuery'),
				'quote' => array(get_called_class(), 'mockQuote'),
				'quoteName' => array(get_called_class(), 'mockQuoteName'),
				'setQuery' => array(get_called_class(), 'mockSetQuery'),
			)
		);

		return $mockObject;
	}

	/**
	 * Callback for the dbo setQuery method.
	 *
	 * @param  string  $new  True to get a new query, false to get the last query.
	 *
	 * @return void
	 *
	 * @since  11.3
	 */
	public function mockGetQuery($new = false)
	{
		if ($new)
		{
			require_once __DIR__.'/JDatabaseQueryDummy.php';

			return new JDatabaseQueryDummy;
		}
		else
		{
			return self::$lastQuery;
		}
	}

	/**
	 * Mocking the quote method.
	 *
	 * @param  string  $value  The value to be quoted.
	 *
	 * @return string  The value passed wrapped in MySQL quotes.
	 *
	 * @since  11.3
	 */
	public function mockQuote($value)
	{
		return "'$value'";
	}

	/**
	 * Mock quoteName method.
	 *
	 * @param  string  $value  The value to be quoted.
	 *
	 * @return string  The value passed wrapped in MySQL quotes.
	 *
	 * @since  11.3
	 */
	public function mockQuoteName($value)
	{
		return "`$value`";
	}

	/**
	 * Callback for the dbo setQuery method.
	 *
	 * @param  string  $query  The query.
	 *
	 * @return void
	 *
	 * @since  11.3
	 */
	public function mockSetQuery($query)
	{
		self::$lastQuery = $query;
	}
}
