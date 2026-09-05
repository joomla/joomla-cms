<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Plugin;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Plugin\CMSPlugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 *
 * @testdox     The CMSPlugin
 *
 * @since       4.2.0
 */
class CMSPluginTest extends UnitTestCase
{
    /**
     * @testdox  has the correct dispatcher
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedApplication()
    {
        $app        = $this->createStub(CMSApplicationInterface::class);

        $plugin = new class extends CMSPlugin {
            public function getApplication(): CMSApplicationInterface
            {
                return parent::getApplication();
            }
        };
        $plugin->setApplication($app);

        $this->assertEquals($app, $plugin->getApplication());
    }

    /**
     * @testdox  has null params when not set
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testEmptyParams()
    {
        $plugin = new class extends CMSPlugin {
        };

        $this->assertNull($plugin->params);
    }

    /**
     * @testdox  gets the injected params from a registry object
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedRegistryParams()
    {
        $registry   = new Registry();

        $plugin = new class (['params' => $registry]) extends CMSPlugin {
        };

        $this->assertEquals($registry, $plugin->params);
    }

    /**
     * @testdox  gets the injected params from array
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedArrayParams()
    {
        $plugin = new class (['params' => ['test' => 'unit']]) extends CMSPlugin {
        };

        $this->assertEquals('unit', $plugin->params->get('test'));
    }

    /**
     * @testdox  gets the injected name
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedName()
    {
        $plugin = new class (['name' => 'test']) extends CMSPlugin {
            public function getName()
            {
                return $this->_name;
            }
        };

        $this->assertEquals('test', $plugin->getName());
    }

    /**
     * @testdox  gets the injected type
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedType()
    {
        $plugin = new class (['type' => 'test']) extends CMSPlugin {
            public function getType()
            {
                return $this->_type;
            }
        };

        $this->assertEquals('test', $plugin->getType());
    }

    /**
     * @testdox  can load the language
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testLoadLanguage()
    {
        $language   = $this->createMock(Language::class);
        $language->expects($this->once())->method('load')->with($this->equalTo('plg__'), JPATH_ADMINISTRATOR)->willReturn(true);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class extends CMSPlugin {
        };
        $plugin->setApplication($app);
        $plugin->loadLanguage();
    }

    /**
     * @testdox  can load the language for a custom extension and path
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testLoadLanguageWithExtensionAndPath()
    {
        $language   = $this->createMock(Language::class);
        $language->expects($this->once())->method('load')->with($this->equalTo('test'), __DIR__)->willReturn(true);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class extends CMSPlugin {
        };
        $plugin->setApplication($app);
        $plugin->loadLanguage('test', __DIR__);
    }

    /**
     * @testdox  does not load the language when the path exists
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testNotLoadLanguageWhenExists()
    {
        $language   = $this->createMock(Language::class);
        $language->method('getPaths')->willReturn(true);
        $language->expects($this->never())->method('load');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class extends CMSPlugin {
        };
        $plugin->setApplication($app);
        $plugin->loadLanguage();
    }
}
