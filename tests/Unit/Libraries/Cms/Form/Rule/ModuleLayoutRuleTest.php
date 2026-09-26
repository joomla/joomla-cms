<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\ModuleLayoutRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for ModuleLayoutRule.
 *
 * @since  5.4.3
 */
class ModuleLayoutRuleTest extends UnitTestCase
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
			validate="modulelayout"
		/>');

        return [
            [true, $xml, 'abc'],
            [true, $xml, 'abc-def'],
            [true, $xml, 'abc.def'],
            [true, $xml, 'abc-123.def'],
            [true, $xml, 'prefix:abc'],
            [true, $xml, 'pre_fix-1:abc.def'],
            [true, $xml, 'a'],
            [true, $xml, 'A123'],
            [true, $xml, 'Z-9.test'],
            [true, $xml, 'x_y-z:abc.def'],
            [true, $xml, 'abc.def.ghi'],
            [true, $xml, 'abc-123.456-def'],
            [true, $xml, 'prefix:abc-123.def'],
            [true, $xml, 'PREFIX:Value'],
            [true, $xml, 'x:abc'],
            [true, $xml, 'abc123'],
            [true, $xml, 'abc.def123'],
            [true, $xml, 'abc-def.ghi-jkl'],
            [true, $xml, 'abc.def-ghi.jkl'],
            [true, $xml, '-abc'],
            [true, $xml, '_:abc'],
            [false, $xml, ''],
            [false, $xml, '.abc'],
            [false, $xml, 'abc def'],
            [false, $xml, 'pre fix:abc'],
            [false, $xml, 'abc::def'],
            [false, $xml, 'abc@def'],
            [false, $xml, 'abc/def'],
            [false, $xml, 'prefix:.abc'],
            [false, $xml, 'prefix:'],
            [false, $xml, '.'],
            [false, $xml, 'äbc'],
            [false, $xml, 'prefix:..abc'],
            [false, $xml, 'abc,def'],
            [false, $xml, 'abc!def'],
            [false, $xml, 'abc?def'],
            [false, $xml, '$abc'],
            [false, $xml, 'abc$def'],
            [false, $xml, 'prefix:äöü'],
        ];
    }

    /**
     * Tests the ModuleLayoutRule::test method.
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
        $this->assertEquals($expected, (new ModuleLayoutRule())->test($element, $value));
    }
}
