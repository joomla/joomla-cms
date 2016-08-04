<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require JPATH_BASE . '/plugins/content/emailcloak/emailcloak.php';

/**
 * Test class for Email cloaking plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 * @since       3.4
 */
class PlgContentEmailcloakTest extends TestCaseDatabase
{
    /**
     * An instance of the class to test.
     *
     * @var    JApplicationCmsInspector
     * @since  3.2
     */
    protected $class;

    /**
     * Setup for testing.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function setup()
    {
        JFactory::$application = $this->getMockCmsApp();
        JFactory::$session = $this->getMockSession();

        // Create a mock dispatcher instance
        $dispatcher = TestCaseDatabase::getMockDispatcher();

        $plugin = array();
        $plugin['name'] = 'emailcloak';
        $plugin['type'] = 'Content';
        $plugin['params'] = new JRegistry;

        $this->class = new PlgContentEmailcloak($dispatcher, (array)($plugin));
    }

    /**
     * Provides the data to test the constructor method.
     *
     * @return  array
     *
     * @since   3.4
     */
    public function dataTestOnContentPrepare()
    {
        return [
            [
                '<a href="http://mce_host/ourdirectory/email@example.org">anytext</a>', '<span>'
            ],
            [
                '<p><a href="mailto:joe@nowhere.com"><span style="font-style: 8pt;">Joe_fontsize8</span></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nowhere13.com?subject= A text"><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nowhere14.com"><span style="font-style: 14pt;">joe@nowhere14.com</span></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nowhere16.com?subject= A text"><span style="font-size: 16pt;">joe@nowhere16.com</span></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nowhere.com"><strong>something</strong></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nobody.com"><strong>mymail@mysite.com</strong></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nowhere.com?subject= A text"><strong><span style="font-size: 16px;">joe@nowhere.com</span></strong></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nobody.com"><strong><span style="font-size: 14px;">mymail@mysite.com</span></strong></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nobody.com"><strong><span style="font-size: 9px;">Joe Nobody</span></strong></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<p><a href="mailto:joe@nobody.com"><strong><span>strong and span</span></strong></a></p>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<a href="mailto:email@amail.com?subject=Text"><img src="path/to/something.jpg">email@amail.com</img></a>',
                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
            ],
            [
                '<a href="http://mce_host/ourdirectory/email@example.org">email@example.org</a>',
                '<a href="http://mce_host/ourdirectory/email@example.org">email@example.org</a>'
            ],
            [
                '<a href="mailto:email@example.org">email@example.org</a>',
                '<a href="mailto:email@example.org">email@example.org</a>'
            ],
        ];
    }

    /**
     * Tests PlgContentEmailcloakTest::_cloak()
     *
     * @param   string $text The text to test.
     * @param   string $result The result of the filtering.
     *
     * @return  void
     *
     * @dataProvider  dataTestOnContentPrepare
     * @since         3.4
     */
    public function testOnContentPrepareWithRowNoFinder($text, $result)
    {
        $row = new stdClass;
        $row->text = $text;
        $params = new JRegistry;
        $this->class->onContentPrepare('com_content.article', $row, $params);

        $this->assertRegExp('/\<span\sid\=\"cloak[0-9a-z]{32}\"\>JLIB_HTML_CLOAKING\<\/span\>/', $row->text);
    }
}
