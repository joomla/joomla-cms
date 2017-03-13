<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/stemmer/fr.php';

/**
 * Test class for FinderIndexerStemmerFr.
 */
class FinderIndexerStemmerFrTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var FinderIndexerStemmerFr
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new FinderIndexerStemmerFr;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->object);
	}

	/**
	 * Tests the stem method of the French language stemmer
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStem()
	{
		$this->assertEquals(
			'mais',
			$this->object->stem('maisons', 'fr')
		);
	}

	/**
	 * Tests the stem method of the French language stemmer to ensure it doesn't stem short words
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStemShort()
	{
		$this->assertEquals(
			'ab',
			$this->object->stem('ab', 'fr')
		);
	}

	/**
	 * Tests the stem method of the French language stemmer to ensure it only stems French
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStemWrongLanguage()
	{
		$this->assertEquals(
			'maisons',
			$this->object->stem('maisons', 'en')
		);
	}
}
