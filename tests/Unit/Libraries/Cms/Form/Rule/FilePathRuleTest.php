<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\FilePathRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for FilePathRule.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.9.26
 */
class FilePathRuleTest extends UnitTestCase
{
    /**
     * Test data for the testRule method
     *
     * @return  array
     *
     * @since   3.9.26
     */
    public function dataTest(): array
    {
        $xml = new \SimpleXMLElement('<field
			name="file_path"
			type="text"
			label="COM_MEDIA_FIELD_PATH_FILE_FOLDER_LABEL"
			description="COM_MEDIA_FIELD_PATH_FILE_FOLDER_DESC"
			size="50"
			default="images"
			validate="filePath"
			exclude="administrator|media"
		/>');

        return [
            [true, $xml, ''],
            [true, $xml, '.images'],
            [false, $xml, './images'],
            [false, $xml, '.\images'],
            [false, $xml, '../images'],
            [false, $xml, '.../images'],
            [true, $xml, 'c:\images'],
            [false, $xml, '\\images'], // Means \images
            [true, $xml, 'ftp://images'],
            [true, $xml, 'http://images'],
            [false, $xml, 'media'],
            [false, $xml, 'administrator'],
            [false, $xml, '/4711images'],
            [true, $xml, '4711images'],
            [true, $xml, '1'],
            [true, $xml, '_'],
            [true, $xml, '*'],
            [true, $xml, '%'],
            [true, $xml, '://foo'],
            [false, $xml, '/4711i/images'],
            [false, $xml, '../4711i/images'],
            [true, $xml, 'Εικόνες'],
            [true, $xml, 'Изображений'],
        ];
    }

    /**
     * Tests the FilePathRule::test method.
     *
     * @param   string             $expected  The expected test result
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     *
     * @return  void
     *
     * @since   3.9.26
     * @dataProvider dataTest
     */
    public function testRule($expected, $element, $value)
    {
        $this->assertEquals($expected, (new FilePathRule())->test($element, $value));
    }
}
