<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\CalendarRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for CalendarRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class CalendarRuleTest extends UnitTestCase
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
			validate="calendar"
		/>');

        return [
            [true, $xml, ''],
            [true, $xml, 'now'],
            [true, $xml, 'NOW'],
            [true, $xml, 'Now'],
            [true, $xml, '2026-05-01'],
            [true, $xml, '2026-05-01 00:00:00'],
            [true, $xml, '2028-10-13 14:15:16'],
            [false, $xml, 'test'],
            [false, $xml, '2026-05-01 12:65:00'],
            [false, $xml, '2026-05-01 26:30:00'],
            [false, $xml, '2026-13-01'],
        ];
    }

    /**
     * Tests the CalendarRule::test method.
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
        $this->assertEquals($expected, (new CalendarRule())->test($element, $value));
    }
}
