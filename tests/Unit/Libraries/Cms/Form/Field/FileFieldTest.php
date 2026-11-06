<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     FileField
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Field;

use Joomla\CMS\Form\Field\FileField;

/**
 * Test class for FileField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class FileFieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests setting and getting the maxsize property.
     *
     * @return  void
     */
    public function testSetAndGetMaxsize()
    {
        $fileField = $this->createFileField();

        $fileField->__set('maxsize', '20M');

        $this->assertEquals('20M', $fileField->__get('maxsize'));
    }

    /**
     * Tests that the maxsize attribute is read from the field XML.
     *
     * @return  void
     */
    public function testSetupReadsMaxsize()
    {
        $fileField = $this->createFileField();

        $element = new \SimpleXMLElement(
            '<field name="testfield" type="file" maxsize="20M" />'
        );

        $this->assertTrue($fileField->setup($element, '', null));
        $this->assertEquals('20M', $fileField->__get('maxsize'));
    }

    /**
     * Tests that maxsize defaults to an empty string when not specified.
     *
     * @return  void
     */
    public function testSetupWithoutMaxsize()
    {
        $fileField = $this->createFileField();

        $element = new \SimpleXMLElement(
            '<field name="testfield" type="file" />'
        );

        $this->assertTrue($fileField->setup($element, '', null));
        $this->assertEquals('', $fileField->__get('maxsize'));
    }

    /**
     * Helper function to create a FileField.
     *
     * @return  FileField
     */
    protected function createFileField(): FileField
    {
        return new FileField();
    }
}
