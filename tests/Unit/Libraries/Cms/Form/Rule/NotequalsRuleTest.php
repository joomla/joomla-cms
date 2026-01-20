<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\Rule\NotequalsRule;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for NotequalsRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class NotequalsRuleTest extends UnitTestCase
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
			field="testfield"
			validate="notequals"
		/>');
        $xml2 = new \SimpleXMLElement('<field
			name="unittest"
			type="text"
			validate="notequals"
		/>');

        return [
            [true, $xml, 'testvalue', null, new Registry(), null],
            [true, $xml, 'testvalue', null, new Registry(['testfield' => '']), null],
            [true, $xml, 'TESTVALUE', null, new Registry(['testfield' => 'testvalue']), null],
            [true, $xml, 'testvalue2', null, new Registry(['testfield' => 'testvalue']), null],
            [true, $xml, '', null, new Registry(['testfield' => 'testvalue']), null],
            [true, $xml, 'testvaluegroup', 'user', new Registry(['user' => ['testfield' => 'testvaluegroup']]), null],
            [true, $xml, 'testvaluegroup', 'a', new Registry(['user' => ['testfield' => 'testvaluegroup']]), null],
            [true, $xml, 'a', null, new Registry(['testfield' => 0]), null],
            [false, $xml, 'testvalue', null, new Registry(['testfield' => 'testvalue']), null],
            [false, $xml, 'testvalue', '', new Registry(['testfield' => 'testvalue']), null],
            [false, $xml, '', null, new Registry(), null],
            [false, $xml, '1', null, new Registry(['testfield' => '1']), null],
            [false, $xml, '2', null, new Registry(['testfield' => '02']), null],
            [false, $xml, '3', null, new Registry(['testfield' => 3]), null],
            [false, $xml, '04', null, new Registry(['testfield' => '4']), null],
            [false, $xml, '5', null, new Registry(['testfield' => 5]), null],
            [false, $xml, 6, null, new Registry(['testfield' => '6']), null],
            [false, $xml, '0', null, new Registry(['testfield' => false]), null],
            [false, $xml, 0, null, new Registry(['testfield' => false]), null],
            [false, $xml, 4, null, new Registry(['testfield' => true]), null],
            [\InvalidArgumentException::class, $xml, '', null, null, null],
            [\UnexpectedValueException::class, $xml2, '', null, null, null],
        ];
    }

    /**
     * Tests the NotequalsRule::test method.
     *
     * @param   bool|string        $expected  The expected test result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   string|int         $value     The form field value to validate.
     * @param   ?string            $group     Group name
     * @param   ?Registry          $input     Input registry
     * @param   ?Form              $form      Form object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @dataProvider dataTest
     */
    public function testRule(bool|string $expected, \SimpleXMLElement $element, string|int $value, ?string $group, ?Registry $input, ?Form $form): void
    {
        if (\is_string($expected) && class_exists($expected)) {
            $this->expectException($expected);
            (new NotequalsRule())->test($element, $value, $group, $input, $form);
        }

        $this->assertEquals($expected, (new NotequalsRule())->test($element, $value, $group, $input, $form));
    }
}
