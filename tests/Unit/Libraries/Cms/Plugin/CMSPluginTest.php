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
use Joomla\Event\Dispatcher;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
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
    public function testInjectedDispatcher()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
        };

        $this->assertEquals($dispatcher, $plugin->getDispatcher());
    }

    /**
     * @testdox  has the correct dispatcher
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedApplication()
    {
        $dispatcher = new Dispatcher();
        $app        = $this->createStub(CMSApplicationInterface::class);

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();
        $registry   = new Registry();

        $plugin = new class ($dispatcher, ['params' => $registry]) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, ['params' => ['test' => 'unit']]) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, ['name' => 'test']) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, ['type' => 'test']) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();
        $language   = $this->createMock(Language::class);
        $language->expects($this->once())->method('load')->with($this->equalTo('plg__'), JPATH_ADMINISTRATOR)->willReturn(true);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();
        $language   = $this->createMock(Language::class);
        $language->expects($this->once())->method('load')->with($this->equalTo('test'), __DIR__)->willReturn(true);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
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
        $dispatcher = new Dispatcher();
        $language   = $this->createMock(Language::class);
        $language->method('getPaths')->willReturn(true);
        $language->expects($this->never())->method('load');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
        };
        $plugin->setApplication($app);
        $plugin->loadLanguage();
    }

    /**
     * @testdox  can register the listeners when is SubscriberInterface
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersAsSubscriber()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin implements SubscriberInterface {
            public static function getSubscribedEvents(): array
            {
                return ['test' => 'unit'];
            }

            public function unit()
            {
            }
        };
        $plugin->registerListeners();

        $this->assertEquals([[$plugin, 'unit']], $dispatcher->getListeners('test'));
    }

    /**
     * @testdox  can register the listeners when is legacy
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersAsLegacy()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function onTest()
            {
            }
        };
        $plugin->registerListeners();

        $this->assertCount(1, $dispatcher->getListeners('onTest'));
    }

    /**
     * @testdox  can register the listeners with event interface
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersForEventInterface()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function onTest(EventInterface $event)
            {
            }
        };
        $plugin->registerListeners();

        $this->assertCount(1, $dispatcher->getListeners('onTest'));
    }

    /**
     * @testdox  must register the listeners with event interface
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersWithForcedEventInterface()
    {
        $dispatcher = new Dispatcher();

        $plugin                             = new class ($dispatcher, []) extends CMSPlugin {
            protected $allowLegacyListeners = false;

            public function onTest(EventInterface $event)
            {
            }
        };
        $plugin->registerListeners();

        $this->assertCount(1, $dispatcher->getListeners('onTest'));
    }

    /**
     * @testdox  can register the listeners when has typed arguments
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersForNoEventInterface()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function onTest(string $context)
            {
            }
        };
        $plugin->registerListeners();

        $this->assertCount(1, $dispatcher->getListeners('onTest'));
    }

    /**
     * @testdox  can register the listeners when has untyped arguments
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersNotTyped()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function onTest($event)
            {
            }
        };
        $plugin->registerListeners();

        $this->assertCount(1, $dispatcher->getListeners('onTest'));
    }

    /**
     * @testdox  can register the listeners when has nullable arguments
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testRegisterListenersNullable()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function onTest(?\stdClass $event = null)
            {
            }
        };
        $plugin->registerListeners();

        $this->assertCount(1, $dispatcher->getListeners('onTest'));
    }

    /**
     * @testdox  can dispatch a legacy listener
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testDispatchLegacyListener()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function registerTestListener()
            {
                parent::registerLegacyListener('onTest');
            }

            public function onTest()
            {
                return 'unit';
            }
        };
        $plugin->registerTestListener();
        $event = $dispatcher->dispatch('onTest');

        $this->assertEquals(['unit'], $event->getArgument('result'));
    }

    /**
     * @testdox  can dispatch a legacy listener with null result
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testDispatchLegacyListenerWhenNullIsReturned()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function registerTestListener()
            {
                parent::registerLegacyListener('onTest');
            }

            public function onTest()
            {
            }
        };
        $plugin->registerTestListener();
        $event = $dispatcher->dispatch('onTest');

        $this->assertEquals(null, $event->getArgument('result'));
    }

    /**
     * @testdox  can dispatch a legacy listener and contains the result from the event and the plugin
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testDispatchLegacyListenerWhenEventHasResult()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
            public function registerTestListener()
            {
                parent::registerLegacyListener('onTest');
            }

            public function onTest()
            {
                return 'unit';
            }
        };
        $plugin->registerTestListener();
        $event = $dispatcher->dispatch('onTest', new Event('onTest', ['result' => ['test']]));

        $this->assertEquals(['test', 'unit'], $event->getArgument('result'));
    }
}
