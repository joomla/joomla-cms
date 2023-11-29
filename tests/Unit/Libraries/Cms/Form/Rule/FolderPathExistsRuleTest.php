<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Rule
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\FolderPathExistsRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for FolderPathExistsRule.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       4.0.0
 */
class FolderPathExistsRuleTest extends UnitTestCase
{
    /**
     * Test data for the testRule method
     *
     * @return  array
     *
     * @since   4.0.0
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
			validate="folderPathExists"
			exclude="administrator|media"
		/>');

        return [
            [true, $xml, ''],
            [false, $xml, JPATH_ROOT],
            [true, $xml, 'images'],
            [true, $xml, 'images/headers'],
            [false, $xml, 'images/notexisting'],
            [false, $xml, '.images'],
            [false, $xml, './images'],
            [false, $xml, '.\images'],
            [false, $xml, '../images'],
            [false, $xml, '.../images'],
            [false, $xml, 'c:\images'],
            [false, $xml, '\\images'], // Means \images
            [false, $xml, 'ftp://images'],
            [false, $xml, 'http://images'],
            [false, $xml, 'media'],
            [false, $xml, 'administrator'],
            [false, $xml, '/4711images'],
            [false, $xml, '4711images'],
            [false, $xml, '1'],
            [false, $xml, '_'],
            [false, $xml, '*'],
            [false, $xml, '%'],
            [false, $xml, '://foo'],
            [false, $xml, '/4711i/images'],
            [false, $xml, '../4711i/images'],
            [false, $xml, 'Εικόνες'],
            [false, $xml, 'Изображений'],
        ];
    }

    /**
     * Tests the FolderPathExistsRule::test method.
     *
     * @param   string             $expected  The expected test result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value     The form field value to validate.
     *
     * @return  void
     *
     * @since   4.0.0
     * @dataProvider dataTest
     */
    public function testRule($expected, $element, $value)
    {
        $this->assertEquals($expected, (new FolderPathExistsRule())->test($element, $value));
    }
}
