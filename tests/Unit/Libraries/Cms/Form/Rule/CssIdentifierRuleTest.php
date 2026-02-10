<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\CssIdentifierRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for CssIdentifierRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class CssIdentifierRuleTest extends UnitTestCase
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
			validate="cssidentifier"
		/>');

        return [
            [true, $xml, ''],
            [true, $xml, 'header'],
            [true, $xml, 'main-content'],
            [true, $xml, 'footer'],
            [true, $xml, '-sidebar'],
            [true, $xml, 'navigation'],
            [true, $xml, 'hero-section'],
            [true, $xml, 'login-form2'],
            [true, $xml, 'user_profile'],
            [true, $xml, '@search-box'],
            [true, $xml, 'contact-form table'],
            [false, $xml, '2footer'],
            [false, $xml, '-5navigation'],
            [false, $xml, '--header'],
            [false, $xml, 'main-content!'],
        ];
    }

    /**
     * Tests the CssIdentifierRule::test method.
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
        $this->assertEquals($expected, (new CssIdentifierRule())->test($element, $value));
    }
}
