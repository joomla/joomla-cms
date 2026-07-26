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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for EqualsRule.
 *
 * @since  5.4.3
 */
class EqualsRuleTest extends UnitTestCase
{
    /**
     * Test data for the testRule method
     *
     * @return  array
     *
     * @since   5.4.3
     */
    public static function dataTest(): array
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

        return [
            [true, $xml, 'testvalue', null, new Registry(['testfield' => 'testvalue']), Form::class],
            [true, $xml, 'testvalue', '', new Registry(['testfield'     => 'testvalue']), Form::class],
            [true, $xml, 'testvaluegroup', 'user', new Registry(['user' => ['testfield' => 'testvaluegroup']]), Form::class],
            [true, $xml, '', null, new Registry(), Form::class],
            [true, $xml, '1', null, new Registry(['testfield'  => '1']), Form::class],
            [true, $xml, '2', null, new Registry(['testfield'  => '02']), Form::class],
            [true, $xml, '3', null, new Registry(['testfield'  => 3]), Form::class],
            [true, $xml, '04', null, new Registry(['testfield' => '4']), Form::class],
            [true, $xml, '5', null, new Registry(['testfield'  => 5]), Form::class],
            [true, $xml, 6, null, new Registry(['testfield'    => '6']), Form::class],
            [true, $xml, '0', null, new Registry(['testfield'  => false]), Form::class],
            [true, $xml, 0, null, new Registry(['testfield'    => false]), Form::class],
            [true, $xml, 4, null, new Registry(['testfield'    => true]), Form::class],
            [false, $xml, 'testvalue', null, new Registry(), Form::class],
            [false, $xml, 'testvalue', null, new Registry(['testfield'  => '']), Form::class],
            [false, $xml, 'TESTVALUE', null, new Registry(['testfield'  => 'testvalue']), Form::class],
            [false, $xml, 'testvalue2', null, new Registry(['testfield' => 'testvalue']), Form::class],
            [false, $xml, '', null, new Registry(['testfield'           => 'testvalue']), Form::class],
            [false, $xml, 'testvaluegroup', 'a', new Registry(['user'   => ['testfield' => 'testvaluegroup']]), Form::class],
            [false, $xml, 'a', null, new Registry(['testfield'          => 0]), Form::class],
            [\InvalidArgumentException::class, $xml, '', null, null, null],
            [\InvalidArgumentException::class, $xml, '', null, new Registry(), null],
            [\InvalidArgumentException::class, $xml, '', null, null, Form::class],
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
     * @param   ?string            $form      Class name of form object
     *
     * @return  void
     *
     * @since   5.4.3
     */
    #[DataProvider('dataTest')]
    public function testRule(bool|string $expected, \SimpleXMLElement $element, string|int $value, ?string $group, ?Registry $input, ?string $form): void
    {
        if (\is_string($form) && class_exists($form)) {
            $form = $this->createStub($form);
        }

        if (\is_string($expected) && class_exists($expected)) {
            $this->expectException($expected);
            (new EqualsRule())->test($element, $value, $group, $input, $form);
        }

        $this->assertEquals($expected, (new EqualsRule())->test($element, $value, $group, $input, $form));
    }
}
