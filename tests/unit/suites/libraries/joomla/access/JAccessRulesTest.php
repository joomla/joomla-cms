<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for \Joomla\CMS\Access\Rules.
 *
 * @package     Joomla.Platform
 * @subpackage  Access
 * @since       1.7.0
 */
class JAccessRulesTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * This method tests both the constructor and the __toString magic method.
	 *
	 * The input for this class could come from a posted form, or from a JSON string
	 * stored in the database.  We need to ensure that the resulting JSON is the same
	 * as the input.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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
		$rules = new \Joomla\CMS\Access\Rules($string);
		$this->assertThat(
			(string) $rules,
			$this->equalTo($string),
			'Checks input as a string.'
		);
	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules::getData method.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
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

		$rule = new \Joomla\CMS\Access\Rules($array);

		$data = $rule->getData();

		$this->assertArrayHasKey(
			'edit',
			$data
		);

		$this->assertInstanceOf(
			'\Joomla\CMS\Access\Rule',
			$data['edit']
		);
	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules constructor
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		$rules = new \Joomla\CMS\Access\Rules($array);
		$this->assertThat(
			(string) $rules,
			$this->equalTo($string),
			'Checks input as an array.'
		);
	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules constructor
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		$rules = new \Joomla\CMS\Access\Rules($object);
		$this->assertThat(
			(string) $rules,
			$this->equalTo($string),
			'Checks input as an object.'
		);
	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules::mergeAction method.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		// Construct and empty \Joomla\CMS\Access\Rules.
		$rules = new \Joomla\CMS\Access\Rules('');
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
	 * Tests the \Joomla\CMS\Access\Rules::merge method.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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
		$rules1 = new \Joomla\CMS\Access\Rules($string1);
		$rules2 = new \Joomla\CMS\Access\Rules($array2);
		$rules1->merge($rules2);

		$this->assertThat(
			(string) $rules1,
			$this->equalTo(json_encode($result2)),
			'Input as a string'
		);
	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules::merge method
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		// Test merge by \Joomla\CMS\Access\Rules.
		$rules1 = new \Joomla\CMS\Access\Rules($array1);
		$rules2 = new \Joomla\CMS\Access\Rules('');
		$rules2->merge($rules1);
		$this->assertThat(
			(string) $rules2,
			$this->equalTo($string1),
			'Merge by \Joomla\CMS\Access\Rules where second rules are empty'
		);
	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules::merge method
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		$rules1 = new \Joomla\CMS\Access\Rules($array1);
		$rules1->merge($array2);
		$this->assertThat(
			(string) $rules1,
			$this->equalTo(json_encode($result2)),
			'Input as a \Joomla\CMS\Access\Rules'
		);

	}

	/**
	 * Tests the \Joomla\CMS\Access\Rules::allow method.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		$rules = new \Joomla\CMS\Access\Rules($array1);

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
	 * Tests the \Joomla\CMS\Access\Rules::getAllowed method.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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

		$rules = new \Joomla\CMS\Access\Rules($array1);
		$allowed = $rules->getAllowed(-42);

		$this->assertThat(
			$result,
			$this->equalTo($allowed)
		);
	}
}
