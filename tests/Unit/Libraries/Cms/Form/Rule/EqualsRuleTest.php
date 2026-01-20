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
use Joomla\CMS\Form\Rule\EqualsRule;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for EqualsRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class EqualsRuleTest extends UnitTestCase
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
			validate="equals"
		/>');
        $xml2 = new \SimpleXMLElement('<field
			name="unittest"
			type="text"
			validate="equals"
		/>');
        $form = $this->createMock(Form::class);

        return [
            [true, $xml, 'testvalue', null, new Registry(['testfield' => 'testvalue']), $form],
            [true, $xml, 'testvalue', '', new Registry(['testfield' => 'testvalue']), $form],
            [true, $xml, 'testvaluegroup', 'user', new Registry(['user' => ['testfield' => 'testvaluegroup']]), $form],
            [true, $xml, '', null, new Registry(), $form],
            [true, $xml, '1', null, new Registry(['testfield' => '1']), $form],
            [true, $xml, '2', null, new Registry(['testfield' => '02']), $form],
            [true, $xml, '3', null, new Registry(['testfield' => 3]), $form],
            [true, $xml, '04', null, new Registry(['testfield' => '4']), $form],
            [true, $xml, '5', null, new Registry(['testfield' => 5]), $form],
            [true, $xml, 6, null, new Registry(['testfield' => '6']), $form],
            [true, $xml, '0', null, new Registry(['testfield' => false]), $form],
            [true, $xml, 0, null, new Registry(['testfield' => false]), $form],
            [true, $xml, 4, null, new Registry(['testfield' => true]), $form],
            [false, $xml, 'testvalue', null, new Registry(), $form],
            [false, $xml, 'testvalue', null, new Registry(['testfield' => '']), $form],
            [false, $xml, 'TESTVALUE', null, new Registry(['testfield' => 'testvalue']), $form],
            [false, $xml, 'testvalue2', null, new Registry(['testfield' => 'testvalue']), $form],
            [false, $xml, '', null, new Registry(['testfield' => 'testvalue']), $form],
            [false, $xml, 'testvaluegroup', 'a', new Registry(['user' => ['testfield' => 'testvaluegroup']]), $form],
            [false, $xml, 'a', null, new Registry(['testfield' => 0]), $form],
            [\InvalidArgumentException::class, $xml, '', null, null, null],
            [\InvalidArgumentException::class, $xml, '', null, new Registry(), null],
            [\InvalidArgumentException::class, $xml, '', null, null, $form],
            [\UnexpectedValueException::class, $xml2, '', null, null, null],
        ];
    }

    /**
     * Tests the EqualsRule::test method.
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
            (new EqualsRule())->test($element, $value, $group, $input, $form);
        }

        $this->assertEquals($expected, (new EqualsRule())->test($element, $value, $group, $input, $form));
    }
}
