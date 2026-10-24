<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     MeterField
 *
 * @copyright      (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Field;

use Joomla\CMS\Form\Field\MeterField;

/**
 * Test class for MeterField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class MeterFieldTest extends \PHPUnit\Framework\TestCase
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
        $this->assertInstanceOf(MeterField::class, new MeterField());
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
        $field = new MeterField();

        $this->assertSame('Meter', $field->__get('type'));
        $this->assertSame(0, $field->__get('min'));
        $this->assertSame(100, $field->__get('max'));
        $this->assertFalse($field->__get('active'));
        $this->assertTrue($field->__get('animated'));
        $this->assertNull($field->__get('width'));
        $this->assertNull($field->__get('color'));
    }

    /**
     * Tests setting and getting properties.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetAndGetProperties()
    {
        $field = new MeterField();

        $field->__set('min', 25);
        $field->__set('max', 250);
        $field->__set('width', '200px');
        $field->__set('color', '#ff0000');
        $field->__set('active', 'true');
        $field->__set('animated', 'false');

        $this->assertSame(25, $field->__get('min'));
        $this->assertSame(250, $field->__get('max'));
        $this->assertSame('200px', $field->__get('width'));
        $this->assertSame('#ff0000', $field->__get('color'));
        $this->assertTrue($field->__get('active'));
        $this->assertFalse($field->__get('animated'));
    }

    /**
     * Tests setup method extracts min and max attributes from XML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetupWithCustomMinMaxAttributes()
    {
        $field   = new MeterField();
        $element = new \SimpleXMLElement('<field name="testmeter" min="10" max="50" />');

        $this->assertTrue($field->setup($element, 30, null));
        $this->assertSame(10, $field->__get('min'));
        $this->assertSame(50, $field->__get('max'));
    }

    /**
     * Tests setup method with other custom attributes.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetupWithCustomAttributes()
    {
        $field   = new MeterField();
        $element = new \SimpleXMLElement(
            '<field name="testmeter" min="5" max="95" width="150px" color="blue" active="1" animated="0" />'
        );

        $this->assertTrue($field->setup($element, 50, null));
        $this->assertSame(5, $field->__get('min'));
        $this->assertSame(95, $field->__get('max'));
        $this->assertSame('150px', $field->__get('width'));
        $this->assertSame('blue', $field->__get('color'));
        $this->assertTrue($field->__get('active'));
        $this->assertFalse($field->__get('animated'));
    }

    /**
     * Tests that layout data contains the configured min and max values.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLayoutDataWithCustomMinMax()
    {
        $field   = new MeterField();
        $element = new \SimpleXMLElement('<field name="testmeter" min="20" max="200" width="300px" color="green" />');

        $field->setup($element, 100, null);

        $layoutData = $this->callGetLayoutData($field);

        $this->assertSame(20, $layoutData['min']);
        $this->assertSame(200, $layoutData['max']);
        $this->assertSame('300px', $layoutData['width']);
        $this->assertSame('green', $layoutData['color']);
        $this->assertSame(100, $layoutData['value']);
    }

    /**
     * Helper to invoke protected getLayoutData method.
     *
     * @param   MeterField  $field  Field instance
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function callGetLayoutData(MeterField $field): array
    {
        $reflection = new \ReflectionMethod(MeterField::class, 'getLayoutData');

        return $reflection->invoke($field);
    }
}
