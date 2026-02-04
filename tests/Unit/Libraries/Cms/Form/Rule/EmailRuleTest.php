<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\EmailRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for EmailRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class EmailRuleTest extends UnitTestCase
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
			validate="email"
		/>');

        return [
            [true, $xml, 'user@example.com'],
            [true, $xml, 'user@example'],
            [true, $xml, 'firstname.name@example.de'],
            [true, $xml, 'user+alias@example-mail.de'],
            [true, $xml, 'john_doe@example123.net'],
            [true, $xml, 'special.chars!#$%&\'*+/=?^_{|}~-user@example.org'],
            [true, $xml, 'user@sub.domain.co.uk'],
            [false, $xml, 'userexample.com'],
            [false, $xml, '@example.com'],
            [false, $xml, 'user@'],
            [false, $xml, 'user@.example.com'],
            [false, $xml, 'user@example..com'],
            [false, $xml, 'user@@example.com'],
            [false, $xml, 'user@ex!ample.com'],
            [false, $xml, 'user@exam_ple.com'],
            [false, $xml, 'user@example.com.'],
            [false, $xml, 'user name@example.com'],
        ];
    }

    /**
     * Tests the EmailRule::test method.
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
        if ($expected) {
            $this->assertTrue((new EmailRule())->test($element, $value));
        } else {
            $this->expectException(\UnexpectedValueException::class);
            (new EmailRule())->test($element, $value);
        }
    }
}
