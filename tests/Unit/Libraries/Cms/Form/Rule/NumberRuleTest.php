<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\NumberRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for NumberRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class NumberRuleTest extends UnitTestCase
{
    /**
     * Test data for the testRule method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataTest(): array
    {
        $xml = new \SimpleXMLElement('<field
			name="unittest"
			type="text"
			min="5"
			max="40"
			validate="number"
			required="true"
		/>');

        return [
            [true, $xml, '5'],
            [true, $xml, '40'],
            [true, $xml, '40.00'],
            [true, $xml, '26.164842'],
            [true, $xml, '018'],
            [true, $xml, '1e1'],
            [true, $xml, '14 '],
            [true, $xml, '5 4'],
            [true, $xml, '28hi'],
            [true, $xml, '	0018abc'],
            [false, $xml, ''],
            [false, $xml, '0'],
            [false, $xml, '4.99999'],
            [false, $xml, '40.00001'],
            [false, $xml, '-8'],
            [false, $xml, '-48.5'],
            [false, $xml, '115'],
            [false, $xml, '1e2'],
            [false, $xml, 'abc'],
            [false, $xml, 'a8'],
        ];
    }

    /**
     * Tests the NumberRule::test method.
     *
     * @param   bool               $expected  The expected test result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   string             $value     The form field value to validate.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @dataProvider dataTest
     */
    public function testRule(bool $expected, \SimpleXMLElement $element, string $value): void
    {
        $this->assertEquals($expected, (new NumberRule())->test($element, $value));
    }
}
