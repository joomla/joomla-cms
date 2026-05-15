<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\ColorRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for ColorRule.
 *
 * @since  5.4.3
 */
class ColorRuleTest extends UnitTestCase
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
			type="text"
			validate="color"
		/>');

        return [
            [true, $xml, ''],
            [true, $xml, '#000000'],
            [true, $xml, '#FFFFFF'],
            [true, $xml, '#FF0000'],
            [true, $xml, '#00FF00'],
            [true, $xml, '#0000ff'],
            [true, $xml, '#c0a'],
            [true, $xml, '#4FF'],
            [false, $xml, 'test'],
            [false, $xml, '451'],
            [false, $xml, '123456'],
            [false, $xml, '#12345G'],
            [false, $xml, '#4H0'],
            [false, $xml, '#FFFF'],
        ];
    }

    /**
     * Tests the ColorRule::test method.
     *
     * @param   bool               $expected  The expected test result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   string             $value     The form field value to validate.
     *
     * @return  void
     *
     * @since   5.4.3
     * @dataProvider dataTest
     */
    public function testRule(bool $expected, \SimpleXMLElement $element, string $value): void
    {
        $this->assertEquals($expected, (new ColorRule())->test($element, $value));
    }
}
