<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';

/**
 * Test class for SearchHelperTest.
 */
class SearchHelperTest extends TestCase
{
	public function testRemoveAccents()
	{
		// Test double quotes.
		$this->assertEquals(
			'This "is a" test',
			SearchHelper::remove_accents('This "is a" test')
		);

		// test single quotes.
		$this->assertEquals(
			"The 'Ledger' Contents may settle during",
			SearchHelper::remove_accents("The 'Ledger' Contents may settle during")
		);

		// Test other
		$this->assertEquals(
			"ue => ue, ae => ae, ae => ae, et cetera",
			SearchHelper::remove_accents("ü => ue, ä => ae, æ => ae, et cetera")
		);
	}
}
