<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JAccessRules.
 *
 * @package     Joomla.Platform
 * @subpackage  Access
 * @since       11.1
 */
class JAccessRulesTest extends PHPUnit_Framework_TestCase
{
	/**
	 * This method tests both the contructor and the __toString magic method.
	 *
	 * The input for this class could come from a posted form, or from a JSON string
	 * stored in the database.  We need to ensure that the resulting JSON is the same
	 * as the input.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test__constructString()
	{
		$array = array(
			'edit' => array(
				-42 => 1,
				2 => 1,
				3 => 0
			)
		);

		$string = json_encode($array);

		// Test input as string.
		$rules = new JAccessRules($string);
		$this->assertThat(
			(string) $rules,
			$this->equalTo($string),
			'Checks input as an string.'
		);
	}

	/**
	 * Tests the JAccessRules::getData method.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetData()
	{
		$array = array(
			'edit' => array(
				-42 => 1,
				2 => 1,
				3 => 0
			)
		);

		$rule = new JAccessRules($array);

		$data = $rule->getData();

		$this->assertArrayHasKey(
			'edit',
			$data
		);

		$this->assertInstanceOf(
			'JAccessRule',
			$data['edit']
		);
	}

	/**
	 * Tests the JAccessRules constructor
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test__constructArray()
	{
		$array = array(
			'edit' => array(
				-42 => 1,
				2 => 1,
				3 => 0
			)
		);

		$string = json_encode($array);

		$rules = new JAccessRules($array);
		$this->assertThat(
			(string) $rules,
			$this->equalTo($string),
			'Checks input as an array.'
		);
	}

	/**
	 * Tests the JAccessRules constructor
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test__constructObject()
	{
		$array = array(
			'edit' => array(
				-42 => 1,
				2 => 1,
				3 => 0
			)
		);

		$string = json_encode($array);

		$object = (object) $array;

		$rules = new JAccessRules($object);
		$this->assertThat(
			(string) $rules,
			$this->equalTo($string),
			'Checks input as an object.'
		);
	}

	/**
	 * Tests the JAccessRules::mergeAction method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testMergeRule()
	{
		$identities = array(
			-42 => 1,
			2 => 1,
			3 => 0
		);

		$result = array(
			'edit' => array(
				-42 => 1,
				2 => 1,
				3 => 0
			)
		);

		// Construct and empty JAccessRules.
		$rules = new JAccessRules('');
		$rules->mergeAction('edit', $identities);

		$this->assertThat(
			(string) $rules,
			$this->equalTo(json_encode($result))
		);

		// Merge a new set, flipping some bits.
		$identities = array(
			-42 => 0,
			2 => 1,
			3 => 1,
			4 => 1
		);

		// Ident 3 should remain false, 4 should be added.
		$result = array(
			'edit' => array(
				-42 => 0,
				2 => 1,
				3 => 0,
				4 => 1
			)
		);

		$rules->mergeAction('edit', $identities);

		$this->assertThat(
			(string) $rules,
			$this->equalTo(json_encode($result))
		);
	}

	/**
	 * Tests the JAccessRules::merge method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testMerge()
	{
		$array1 = array(
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0
			)
		);
		$string1 = json_encode($array1);

		$array2 = array(
			'create' => array(
				2 => 1
			),
			'delete' => array(
				2 => 0
			)
		);

		$result2 = array(
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0,
				2 => 0
			),
			'create' => array(
				2 => 1
			),
		);

		// Test construction by string
		$rules1 = new JAccessRules($string1);
		$rules2 = new JAccessRules($array2);
		$rules1->merge($rules2);

		$this->assertThat(
			(string) $rules1,
			$this->equalTo(json_encode($result2)),
			'Input as a string'
		);
	}

	/**
	 * Tests the JAccessRules::merge method
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testMergeRulesNull()
	{
		$array1 = array(
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0
			)
		);
		$string1 = json_encode($array1);

		// Test merge by JAccessRules.
		$rules1 = new JAccessRules($array1);
		$rules2 = new JAccessRules('');
		$rules2->merge($rules1);
		$this->assertThat(
			(string) $rules2,
			$this->equalTo($string1),
			'Merge by JAccessRules where second rules are empty'
		);
	}

	/**
	 * Tests the JAccessRules::merge method
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testMergeRules()
	{
		$array1 = array(
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0
			)
		);

		$array2 = array(
			'create' => array(
				2 => 1
			),
			'delete' => array(
				2 => 0
			)
		);

		$result2 = array(
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0,
				2 => 0
			),
			'create' => array(
				2 => 1
			),
		);

		$rules1 = new JAccessRules($array1);
		$rules1->merge($array2);
		$this->assertThat(
			(string) $rules1,
			$this->equalTo(json_encode($result2)),
			'Input as a JAccessRules'
		);

	}

	/**
	 * Tests the JAccessRules::allow method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAllow()
	{
		$array1 = array(
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0,
				2 => 1
			)
		);

		$rules = new JAccessRules($array1);

		// Explicit allow.
		$this->assertTrue(
			$rules->allow('edit', -42)
		);

		// Check string or int works.
		$this->assertTrue(
			$rules->allow('edit', '-42')
		);

		// Implicit deny
		$this->assertNull(
			$rules->allow('edit', 999)
		);

		// Explicit deny
		$this->assertFalse(
			$rules->allow('delete', -42)
		);

		// This should be true, implicit deny does not win.
		$this->assertTrue(
			$rules->allow('edit', array(-42, 999))
		);

		// This should be false, explicit deny wins.
		$this->assertFalse(
			$rules->allow('delete', array(-42, 2))
		);

		// This should be null, the action doesn't exist.
		$this->assertNull(
			$rules->allow('unknown', array(-42, 2))
		);
	}

	/**
	 * Tests the JAccessRules::getAllowed method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAllowed()
	{
		$array1 = array(
			'create' => array(
				-42 => 1
			),
			'edit' => array(
				-42 => 1
			),
			'delete' => array(
				-42 => 0,
				2 => 1
			)
		);

		$result = new JObject;
		$result->set('create', true);
		$result->set('edit', true);

		$rules = new JAccessRules($array1);
		$allowed = $rules->getAllowed(-42);

		$this->assertThat(
			$result,
			$this->equalTo($allowed)
		);
	}
}
