<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


use Joomla\CMS\Form\Rule\FilePathRule;

/**
 * Test class for FilePathRule.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class FilePathRuleTest extends TestCase
{
	/**
	 * Test data for the testRule method
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dataTestPaths()
	{
		$xml = new \SimpleXMLElement('<field
			name="file_path"
			type="text"
			label="COM_MEDIA_FIELD_PATH_FILE_FOLDER_LABEL"
			description="COM_MEDIA_FIELD_PATH_FILE_FOLDER_DESC"
			size="50"
			default="images"
			validate="filePath"
		/>');

		return array(
			array(false, $xml, '.images'),
			array(false, $xml, './images'),
			array(false, $xml, '.\images'),
			array(false, $xml, '../images'),
			array(false, $xml, '.../images'),
			array(true, $xml, 'c:\images'),
			array(false, $xml, '\\images'), // Means \images
			array(true, $xml, 'ftp://images'),
			array(true, $xml, 'http://images'),
			array(false, $xml, '/media'),
			array(false, $xml, '/administrator'),
			array(false, $xml, '/4711images'),
			array(false, $xml, '4711images'),
			array(false, $xml, '1'),
			array(false, $xml, '_'),
			array(false, $xml, '*'),
			array(false, $xml, '%'),
			array(false, $xml, '://foo'),
			array(false, $xml, '/4711i/images'),
			array(false, $xml, '../4711i/images'),
			array(false, $xml, 'Εικόνες'),
			array(false, $xml, 'Изображений'),
		);
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
	 * @since   __DEPLOY_VERSION__
	 * @dataProvider dataTestPaths
	 */
	public function testRule($expected, $element, $value)
	{
		$obj = new FilePathRule;
		$this->assertEquals($expected, $obj->test($element, $value));
	}
}
