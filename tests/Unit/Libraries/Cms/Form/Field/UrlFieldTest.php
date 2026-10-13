<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Field;

use Joomla\CMS\Form\Field\UrlField;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for UrlField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class UrlFieldTest extends UnitTestCase
{
    /**
     * Tests the constructor
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsConstructable(): void
    {
        $this->assertInstanceOf(UrlField::class, new UrlField());
    }

    /**
     * Tests getting default property values.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetWithDefaultValues(): void
    {
        $field = new UrlField();

        $this->assertEquals('Url', $field->__get('type'));
        $this->assertFalse($field->__get('relative'));
    }

    /**
     * Tests setting and getting properties.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetAndGetShouldBeEquals(): void
    {
        $field = new UrlField();

        $field->__set('relative', true);
        $this->assertTrue($field->__get('relative'));

        $field->__set('relative', 'false');
        $this->assertFalse($field->__get('relative'));

        $field->__set('relative', '1');
        $this->assertTrue($field->__get('relative'));

        $field->__set('relative', '0');
        $this->assertFalse($field->__get('relative'));

        $field->__set('relative', 'relative');
        $this->assertTrue($field->__get('relative'));
    }

    /**
     * Tests setup method parsing the relative attribute.
     *
     * @param   bool    $expected  The expected relative boolean value
     * @param   string  $xml       The XML string
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @dataProvider dataSetupRelative
     */
    public function testSetupRelative(bool $expected, string $xml): void
    {
        $field   = new UrlField();
        $element = new \SimpleXMLElement($xml);

        $this->assertTrue($field->setup($element, ''));
        $this->assertEquals($expected, $field->__get('relative'));
    }

    /**
     * Data provider for testSetupRelative
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataSetupRelative(): array
    {
        return [
            'default'            => [false, '<field name="my_url" type="url" />'],
            'relative_true'      => [true, '<field name="my_url" type="url" relative="true" />'],
            'relative_false'     => [false, '<field name="my_url" type="url" relative="false" />'],
            'relative_1'         => [true, '<field name="my_url" type="url" relative="1" />'],
            'relative_0'         => [false, '<field name="my_url" type="url" relative="0" />'],
            'relative_keyword'   => [true, '<field name="my_url" type="url" relative="relative" />'],
        ];
    }
}
