<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\ShowOnRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for ShowOnRule.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class ShowOnRuleTest extends UnitTestCase
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
			name="showon"
			type="text"
			label="COM_FIELDS_FIELD_SHOWON_LABEL"
			description="COM_FIELDS_FIELD_SHOWON_DESC"
			validate="ShowOn"
		/>');

        return [
            [true, $xml, ''],
            [true, $xml, 'box:value1'],
            [true, $xml, 'box!:value1'],
            [true, $xml, 'box!:'],
            [true, $xml, 'box:value1[OR]square:value1'],
            [true, $xml, 'box:value1[AND]square:value1'],
            [true, $xml, 'box:value1[OR]square!:value1'],
            [true, $xml, 'box:value1[AND]square!:value1'],
            [true, $xml, 'box:value1[AND]square:value1,value2'],
            [true, $xml, 'box:value1[AND]square!:value1,value2'],
            [true, $xml, 'box:value1[AND]square!:value1,value2[OR]square!:value1'],
            [true, $xml, 'box:value1,value2[AND]square:value1'],
            [false, $xml, 'box'],
            [false, $xml, 'box::'],
            [false, $xml, 'box:!'],
            [false, $xml, 'box:3:21'],
            [false, $xml, '[AND]box:value3[OR]square:2:3'],
            [false, $xml, '[AND][OR]'],
            [false, $xml, 'box:value1[AND]square!:value1:value2'],
        ];
    }

    /**
     * Tests the ShowOnRule::test method.
     *
     * @param   boolean            $expected  The expected test result
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
        $this->assertEquals($expected, (new ShowOnRule())->test($element, $value));
    }
}
