<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\OptionsRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for OptionsRule.
 *
 * @since  5.4.3
 */
class OptionsRuleTest extends UnitTestCase
{
    /**
     * Test data for the testRule method
     *
     * @return  array
     *
     * @since   5.4.3
     */
    public function dataTest(): array
    {
        $xml = new \SimpleXMLElement('<field
			name="unittest"
			type="list"
			validate="options"
			required="true"
			>
			<option value="op1">option1</option>
			<option value="op2">option2</option>
			<option value="op3">option3</option>
			<group label="group1">
				<option value="g1op1">option1</option>
				<option value="g1op2">option2</option>
			</group>
			<group label="group2">
				<option value="g2op1">option1</option>
				<option value="g2op2">option2</option>
			</group>
		</field>');

        return [
            [true, $xml, 'op1'],
            [true, $xml, 'op2'],
            [true, $xml, 'op3'],
            [true, $xml, 'g1op1'],
            [true, $xml, 'g2op2'],
            [true, $xml, ['op1', 'op3']],
            [true, $xml, ['op2', 'g2op1']],
            [true, $xml, []],
            [false, $xml, null],
            [false, $xml, ''],
            [false, $xml, 'op4'],
            [false, $xml, 'g1op3'],
            [false, $xml, ['op2', 'op4']],
            [false, $xml, ['op1', 'g3op1']],
        ];
    }

    /**
     * Tests the OptionsRule::test method.
     *
     * @param   bool               $expected  The expected test result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   null|string|array  $value     The form field value to validate.
     *
     * @return  void
     *
     * @since   5.4.3
     * @dataProvider dataTest
     */
    public function testRule(bool $expected, \SimpleXMLElement $element, null|string|array $value): void
    {
        $this->assertEquals($expected, (new OptionsRule())->test($element, $value));
    }
}
