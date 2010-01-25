<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @package		JoomlaFramework
 */

/**
 * @package		JoomlaFramework
 */
class JRuleTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		jimport('joomla.access.rule');
	}

	public function testConstructor()
	{
		$array = array(
			-42	=> 1,
			2	=> 1,
			3	=> 0
		);

		// Get the string representation.
		$string		= json_encode($array);

		// Test constructor with array.
		$rule1	= new JRule($array);

		// Check that import equals export.
		$this->assertEquals(
			$string,
			(string) $rule1
		);

		// Test constructor with string.
		$rule2	= new JRule($string);

		// Check that import equals export.
		$this->assertEquals(
			$string,
			(string) $rule1
		);
	}

	public function testMergeIdentity()
	{
		// Construct an rule with no identities.
		$rule = new JRule('');

		// Add the identity with allow.
		$rule->mergeIdentity(-42, true);
		$this->assertEquals(
			'{"-42":1}',
			(string) $rule
		);

		// Readd the identity, but deny.
		$rule->mergeIdentity(-42, false);
		$this->assertEquals(
			'{"-42":0}',
			(string) $rule
		);

		// Readd the identity with allow (checking deny wins).
		$rule->mergeIdentity(-42, true);
		$this->assertEquals(
			'{"-42":0}',
			(string) $rule
		);
	}

	public function testMergeIdentities()
	{
		$array = array(
			-42	=> 1,
			2	=> 1,
			3	=> 0
		);

		// Construct an rule with no identities.
		$rule = new JRule('');

		$rule->mergeIdentities($array);
		$this->assertEquals(
			json_encode($array),
			(string) $rule
		);

		// Merge a new set, flipping some bits.
		$array = array(
			-42	=> 0,
			2	=> 1,
			3	=> 1,
			4	=> 1
		);

		// Ident 3 should remain false, 4 should be added.
		$result = array(
			-42	=> 0,
			2	=> 1,
			3	=> 0,
			4	=> 1
		);
		$rule->mergeIdentities($array);
		$this->assertEquals(
			json_encode($result),
			(string) $rule
		);
	}

	public function testAllow()
	{
		// Simple allow and deny test.
		$array = array(
			-42	=> 0,
			2	=> 1
		);
		$rule = new JRule($array);

		// This one should be denied.
		$this->assertFalse(
			$rule->allow(-42)
		);

		// This one should be allowed.
		$this->assertTrue(
			$rule->allow(2)
		);

		// This one should be denied.
		$this->assertFalse(
			$rule->allow(array(-42, 2))
		);
	}
}
