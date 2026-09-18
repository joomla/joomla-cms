<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     CheckboxesField
 *
 * @copyright      (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Field;

use Joomla\CMS\Form\Field\CheckboxesField;

/**
 * Test class for CheckboxesField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class CheckboxesFieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the constructor
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsConstructable()
    {
        $this->assertInstanceOf(CheckboxesField::class, new CheckboxesField());
    }

    /**
     * Tests getting default properties.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetWithDefaultValues()
    {
        $field = new CheckboxesField();

        $this->assertSame('Checkboxes', $field->__get('type'));
        $this->assertTrue($field->__get('forceMultiple'));
        $this->assertNull($field->__get('checkedOptions'));
    }

    /**
     * Tests setting and getting checkedOptions.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetAndGetCheckedOptions()
    {
        $field = new CheckboxesField();
        $field->__set('checkedOptions', '1,2,3');

        $this->assertSame('1,2,3', $field->__get('checkedOptions'));
    }

    /**
     * Tests setup method extracts checked attribute.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetupWithCheckedAttribute()
    {
        $field   = new CheckboxesField();
        $element = new \SimpleXMLElement('<field name="testfield" checked="1,2" />');

        $this->assertTrue($field->setup($element, null, null));
        $this->assertSame('1,2', $field->__get('checkedOptions'));
    }

    /**
     * Tests that a zero string value ('0') is recognized as hasValue and used in layout data,
     * ensuring option '0' is checked and default option '1' is not checked.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLayoutDataWithZeroStringValue()
    {
        $field   = new CheckboxesField();
        $element = new \SimpleXMLElement(
            '<field name="testfield" checked="1">' .
            '<option value="0">Zero</option>' .
            '<option value="1">One</option>' .
            '</field>'
        );
        $field->setup($element, '0', null);

        $layoutData = $this->callGetLayoutData($field);

        $this->assertTrue($layoutData['hasValue']);
        $this->assertSame(['0'], $layoutData['checkedOptions']);
        $this->assertContains('0', $layoutData['checkedOptions']);
        $this->assertNotContains('1', $layoutData['checkedOptions']);
    }

    /**
     * Tests that an integer zero (0) is recognized as hasValue and converted to string in layout data.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLayoutDataWithZeroIntValue()
    {
        $field   = new CheckboxesField();
        $element = new \SimpleXMLElement(
            '<field name="testfield" checked="1">' .
            '<option value="0">Zero</option>' .
            '<option value="1">One</option>' .
            '</field>'
        );
        $field->setup($element, 0, null);

        $layoutData = $this->callGetLayoutData($field);

        $this->assertTrue($layoutData['hasValue']);
        $this->assertSame(['0'], $layoutData['checkedOptions']);
        $this->assertContains('0', $layoutData['checkedOptions']);
        $this->assertNotContains('1', $layoutData['checkedOptions']);
    }

    /**
     * Tests that an array containing zero value (['0']) preserves option '0' and ignores defaults.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLayoutDataWithZeroArrayValue()
    {
        $field   = new CheckboxesField();
        $element = new \SimpleXMLElement(
            '<field name="testfield" checked="1">' .
            '<option value="0">Zero</option>' .
            '<option value="1">One</option>' .
            '</field>'
        );
        $field->setup($element, ['0'], null);

        $layoutData = $this->callGetLayoutData($field);

        $this->assertTrue($layoutData['hasValue']);
        $this->assertSame(['0'], $layoutData['checkedOptions']);
        $this->assertContains('0', $layoutData['checkedOptions']);
        $this->assertNotContains('1', $layoutData['checkedOptions']);
    }

    /**
     * Tests that an array containing multiple integer/string values is properly converted to string options.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLayoutDataWithArrayValues()
    {
        $field   = new CheckboxesField();
        $element = new \SimpleXMLElement(
            '<field name="testfield" checked="1">' .
            '<option value="0">Zero</option>' .
            '<option value="1">One</option>' .
            '<option value="2">Two</option>' .
            '</field>'
        );
        $field->setup($element, [0, 2], null);

        $layoutData = $this->callGetLayoutData($field);

        $this->assertTrue($layoutData['hasValue']);
        $this->assertSame(['0', '2'], $layoutData['checkedOptions']);
    }

    /**
     * Tests that null or empty string value falls back to the default checkedOptions.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLayoutDataWithEmptyValueFallsBackToDefault()
    {
        $field   = new CheckboxesField();
        $element = new \SimpleXMLElement(
            '<field name="testfield" checked="1,2">' .
            '<option value="0">Zero</option>' .
            '<option value="1">One</option>' .
            '<option value="2">Two</option>' .
            '</field>'
        );
        $field->setup($element, '', null);

        $layoutData = $this->callGetLayoutData($field);

        $this->assertFalse($layoutData['hasValue']);
        $this->assertSame(['1', '2'], $layoutData['checkedOptions']);
        $this->assertNotContains('0', $layoutData['checkedOptions']);
        $this->assertContains('1', $layoutData['checkedOptions']);
        $this->assertContains('2', $layoutData['checkedOptions']);
    }

    /**
     * Helper to invoke protected getLayoutData method.
     *
     * @param   CheckboxesField  $field  Field instance
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function callGetLayoutData(CheckboxesField $field): array
    {
        $reflection = new \ReflectionMethod(CheckboxesField::class, 'getLayoutData');

        return $reflection->invoke($field);
    }
}
