<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleRules.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleRulesTest extends TestCase
{
	/**
	 * Test the JFormRuleRules::test method.
	 *
	 * @return void
	 */
	public function testItemSectionRules()
	{
		$this->markTestSkipped('Skipped until coupling with application is fixed.');
		$rule = new JFormRuleRules;

		// Get a field with the 'item' permission action group.
		$form = $this->getFieldElement('item');

		/*
		 * Test conditions that should fail.
		 *
		 * Attempt to validate a set of posted permissions that are not valid for the field.
		 */

		// Validate global actions against the item section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('global')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Validate component actions against the item section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('component')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Validate container actions against the item section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('container')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		/*
		 * Test conditions that should pass.
		 *
		 * Attempt to validate a set of posted permissions that are valid for the field.
		 */

		// Validate item actions against the item section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('item')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);
	}

	/**
	 * Test the JFormRuleRules::test method.
	 *
	 * @return void
	 */
	public function testContainerSectionRules()
	{
		$this->markTestSkipped('Skipped until coupling with application is fixed.');

		$rule = new JFormRuleRules;

		// Get a field with the 'container' permission action group.
		$form = $this->getFieldElement('container');

		/*
		 * Test conditions that should fail.
		 *
		 * Attempt to validate a set of posted permissions that are not valid for the field.
		 */

		// Validate global actions against the container section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('global')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Validate component actions against the container section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('component')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		/*
		 * Test conditions that should pass.
		 *
		 * Attempt to validate a set of posted permissions that are valid for the field.
		 */

		// Validate container actions against the container section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('container')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);

		// Validate item actions against the container section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('item')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);
	}

	/**
	 * Test the JFormRuleRules::test method.
	 *
	 * @return void
	 */
	public function testComponentSectionRules()
	{
		$this->markTestSkipped('Skipped until coupling with application is fixed.');

		$rule = new JFormRuleRules;

		// Get a field with the 'component' permission action group.
		$form = $this->getFieldElement('component');

		/*
		 * Test conditions that should fail.
		 *
		 * Attempt to validate a set of posted permissions that are not valid for the field.
		 */

		// Validate global actions against the component section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('global')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		/*
		 * Test conditions that should pass.
		 *
		 * Attempt to validate a set of posted permissions that are valid for the field.
		 */

		// Validate component actions against the component section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('component')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);

		// Validate container actions against the component section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('container')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);

		// Validate item actions against the component section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('item')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);
	}

	/**
	 * Test the JFormRuleRules::test method.
	 *
	 * @return void
	 */
	public function testGlobalSectionRules()
	{
		$this->markTestSkipped('Skipped until coupling with application is fixed.');

		$rule = new JFormRuleRules;

		// Get a field with the 'global' permission action group.
		$form = $this->getFieldElement('global');

		/*
		 * Test conditions that should fail.
		 *
		 * Attempt to validate a set of posted permissions that are not valid for the field.
		 */

		// Validate third party developer actions against the global section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('3pd')),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		/*
		 * Test conditions that should pass.
		 *
		 * Attempt to validate a set of posted permissions that are valid for the field.
		 */

		// Validate global actions against the global section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('global')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);

		// Validate component actions against the global section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('component')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);

		// Validate container actions against the global section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('container')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);

		// Validate item actions against the global section.
		$this->assertThat(
			$rule->test($form->field[0], $this->getRuleData('item')),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The rule should pass and return true.'
		);
	}

	/**
	 * Method to get an XML form field element for a specific permission action group.
	 *
	 * @param   string  $type  The name of the action group for which to get the element.
	 *
	 * @return  array  The form field element.
	 *
	 * @since   11.1
	 */
	public function getFieldElement($type = 'item')
	{
		$form = array();

		switch ($type)
		{
			case 'global':
				$form[] = '<form>';
				$form[] = '<field name="rules">';
				$form[] = '<action name="core.login.site" />';
				$form[] = '<action name="core.login.admin" />';
				$form[] = '<action name="core.admin" />';
				$form[] = '<action name="core.manage" />';
				$form[] = '<action name="core.create" />';
				$form[] = '<action name="core.delete" />';
				$form[] = '<action name="core.edit" />';
				$form[] = '<action name="core.edit.state" />';
				$form[] = '<action name="core.edit.own" />';
				$form[] = '</field>';
				$form[] = '</form>';
				break;

			case 'component':
				$form[] = '<form>';
				$form[] = '<field name="rules">';
				$form[] = '<action name="core.admin" />';
				$form[] = '<action name="core.manage" />';
				$form[] = '<action name="core.create" />';
				$form[] = '<action name="core.delete" />';
				$form[] = '<action name="core.edit" />';
				$form[] = '<action name="core.edit.state" />';
				$form[] = '<action name="core.edit.own" />';
				$form[] = '</field>';
				$form[] = '</form>';
				break;

			case 'container':
				$form[] = '<form>';
				$form[] = '<field name="rules">';
				$form[] = '<action name="core.create" />';
				$form[] = '<action name="core.delete" />';
				$form[] = '<action name="core.edit" />';
				$form[] = '<action name="core.edit.state" />';
				$form[] = '<action name="core.edit.own" />';
				$form[] = '</field>';
				$form[] = '</form>';
				break;

			default:
			case 'item':
				$form[] = '<form>';
				$form[] = '<field name="rules">';
				$form[] = '<action name="core.delete" />';
				$form[] = '<action name="core.edit" />';
				$form[] = '<action name="core.edit.state" />';
				$form[] = '</field>';
				$form[] = '</form>';
				break;
		}

		// Build an XML element out of the form data array.
		$xml = simplexml_load_string(implode($form));

		return $xml;
	}

	/**
	 * Method to get an example data object representing a specific permission action group.
	 *
	 * @param   string  $type  The name of the action group for which to get a data object.
	 *
	 * @return  array  The data object.
	 *
	 * @since   11.1
	 */
	public function getRuleData($type = 'item')
	{
		switch ($type)
		{
			case '3pd':
				$data = (object) array(
					'com_foo.bar' => array(),
					'core.login.site' => array(),
					'core.login.admin' => array(),
					'core.admin' => array(),
					'core.manage' => array(),
					'core.create' => array(),
					'core.delete' => array(),
					'core.edit' => array(),
					'core.edit.state' => array(),
					'core.edit.own' => array()
				);
				break;

			case 'global':
				$data = (object) array(
					'core.login.site' => array(),
					'core.login.admin' => array(),
					'core.admin' => array(),
					'core.manage' => array(),
					'core.create' => array(),
					'core.delete' => array(),
					'core.edit' => array(),
					'core.edit.state' => array(),
					'core.edit.own' => array()
				);
				break;

			case 'component':
				$data = (object) array(
					'core.admin' => array(),
					'core.manage' => array(),
					'core.create' => array(),
					'core.delete' => array(),
					'core.edit' => array(),
					'core.edit.state' => array(),
					'core.edit.own' => array()
				);
				break;

			case 'container':
				$data = (object) array(
					'core.create' => array(),
					'core.delete' => array(),
					'core.edit' => array(),
					'core.edit.state' => array(),
					'core.edit.own' => array()
				);
				break;

			default:
			case 'item':
				$data = (object) array(
					'core.delete' => array(),
					'core.edit' => array(),
					'core.edit.state' => array(),
				);
				break;
		}

		return $data;
	}
}
