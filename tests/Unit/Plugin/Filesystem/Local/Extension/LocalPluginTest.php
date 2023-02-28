<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Filesystem\Local\Extension;

use InvalidArgumentException;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Language;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\Filesystem\Local\Extension\Local;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Local plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Local
 *
 * @testdox     The Local plugin
 *
 * @since       4.3.0
 */
class LocalPluginTest extends UnitTestCase
{
    /**
     * @testdox  has the correct id
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testID()
    {
        $dispatcher = new Dispatcher();

        $plugin = new Local($dispatcher, ['name' => 'test'], __DIR__);

        $this->assertEquals('test', $plugin->getID());
    }

    /**
     * @testdox  has the correct display name
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testDisplayName()
    {
        $dispatcher = new Dispatcher();

        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Local($dispatcher, [], __DIR__);
        $plugin->setApplication($app);

        $this->assertEquals('test', $plugin->getDisplayName());
    }

    /**
     * @testdox  can setup providers
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testSetupProviders()
    {
        $dispatcher = new Dispatcher();

        $manager = new ProviderManager();

        $event = new MediaProviderEvent('test');
        $event->setProviderManager($manager);

        $plugin = new Local($dispatcher, ['name' => 'test'], __DIR__);
        $plugin->onSetupProviders($event);

        $this->assertEquals(['test' => $plugin], $manager->getProviders());
        $this->assertEquals($plugin, $manager->getProvider('test'));
    }

    /**
     * @testdox  can deliver adapters
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testAdapters()
    {
        $dispatcher = new Dispatcher();

        $plugin   = new Local($dispatcher, ['params' => ['directories' => '[{"directory": "tests"}]']], JPATH_ROOT);
        $adapters = $plugin->getAdapters();

        $this->assertCount(1, $adapters);
        $this->assertEquals('tests', $adapters['tests']->getAdapterName());
    }

    /**
     * @testdox  throws an Exception when an invalid directory
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testAdaptersInvalidDirectoy()
    {
        $this->expectException(InvalidArgumentException::class);
        $dispatcher = new Dispatcher();

        $plugin = new Local($dispatcher, ['params' => ['directories' => '[{"directory": "invalid"}]']], __DIR__);
        $plugin->getAdapters();
    }
}
