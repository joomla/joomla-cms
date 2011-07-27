<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/access/rule.php';

/**
 * @package     Joomla.Platform
 */
class JRuleTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{

	}

	public function testConstructor()
	{
		$array = array(
			-42	=> 1,
			2	=> 1,
			3	=> 0
		);




		// Get the string representation.
		$string		  = json_encode($array);


		// Test constructor with array.
		$rule1	= new JRule($array);

		// Check that import equals export.
		$this->assertEquals(
			$string,
			(string) $rule1
		);

		// Test constructor with string.


		// Check that import equals export.

                //**// Check that import equals not export.

                 $array_A = array(
			-44	=> 1,
			2	=> 1,
			3	=> 0
		);

                $string_A          = json_encode($array_A);
                $rule_A	= new JRule($string_A);
		$this->assertNotEquals(
			$string,
			(string) $rule_A
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

                // Check that import equals export.


                //**Test testMergeIdentities with object

                $rule_A = new JRule($array);
                $rule->mergeIdentities($rule_A);
                $this->assertEquals(
			json_encode($array),
			(string) $rule
		);

                $this->assertEquals(
			(string) $rule_A,
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

                $this->assertEquals(Null,$rule->allow(Null));

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
