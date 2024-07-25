<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     Installer
 *
 * @copyright      (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Installer\Adapter;

use Joomla\CMS\Installer\Adapter\ModuleAdapter;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * ModuleAdapterTest
 *
 * @since   4.0.0
 */
class ModuleAdapterTest extends UnitTestCase
{
    /**
     * @var ModuleAdapter
     *
     * @since   4.0.0
     */
    protected $moduleAdapter;

    /**
     * @return void
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        $this->moduleAdapter = $this->getMockBuilder(ModuleAdapter::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    /**
     * This method is called after a test is executed.
     *
     * @return void
     * @since   4.0.0
     */
    protected function tearDown(): void
    {
        unset($this->moduleAdapter);

        parent::tearDown();
    }

    /**
     * @return void
     *
     * @since    4.0.0
     */
    public function testInit()
    {
        $this->assertInstanceOf(ModuleAdapter::class, $this->moduleAdapter);
    }

    /**
     * Tests the legacy way of specifying the element in module XML
     *
     * @return void
     *
     * @since    4.0.0
     */
    public function testgetElement1()
    {
        $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/modules/mod_quickicon/mod_quickicon.xml');
        $this->moduleAdapter->setManifest($xml);

        $this->assertNotNull($this->moduleAdapter->manifest);

        $this->assertEquals('mod_quickicon', $this->moduleAdapter->getElement());
        $this->assertEquals('somethingElse', $this->moduleAdapter->getElement('somethingElse'));
    }

    /**
     * Tests the legacy way of specifying the element in module XML
     *
     * @return void
     *
     * @since    4.0.0
     */
    public function testgetElement2()
    {
        $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/modules/mod_sampledata/mod_sampledata.xml');
        $this->moduleAdapter->setManifest($xml);

        $this->assertNotNull($this->moduleAdapter->manifest);

        $this->assertEquals('mod_sampledata', $this->moduleAdapter->getElement());
        $this->assertEquals('somethingElse', $this->moduleAdapter->getElement('somethingElse'));
    }

    /**
     * Tests the new <element/> tag in Joomla 4 modules introduced in https://github.com/joomla/joomla-cms/pull/33182
     *
     * @return void
     *
     * @since    4.0.0
     */
    public function testgetElementFromElementTag()
    {
        $xml = file_get_contents(JPATH_ADMINISTRATOR . '/modules/mod_quickicon/mod_quickicon.xml');

        // Insert a Joomla 4 <module/> tag
        $xml = str_replace('<name>mod_quickicon</name>', '<name>mod_quickicon</name><element>mod_quickicon</element>', $xml);

        $xml = simplexml_load_string($xml);
        $this->moduleAdapter->setManifest($xml);

        $this->assertNotNull($this->moduleAdapter->manifest);

        $this->assertEquals('mod_quickicon', $this->moduleAdapter->getElement());
        $this->assertEquals('somethingElse', $this->moduleAdapter->getElement('somethingElse'));
    }
}
