<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\BooleanRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for BooleanRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class BooleanRuleTest extends UnitTestCase
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
			validate="boolean"
		/>');

        return [
            [true, $xml, 'true'],
            [true, $xml, 'false'],
            [true, $xml, '0'],
            [true, $xml, '1'],
            [true, $xml, 'TRUE'],
            [true, $xml, 'FALSE'],
            [true, $xml, 'True'],
            [true, $xml, 'False'],
            [false, $xml, ''],
            [false, $xml, 'tu re'],
            [false, $xml, 'false1'],
            [false, $xml, 'none'],
        ];
    }

    /**
     * Tests the BooleanRule::test method.
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
        $this->assertEquals($expected, (new BooleanRule())->test($element, $value));
    }
}
