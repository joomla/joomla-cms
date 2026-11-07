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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\Plugin\CMSPlugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 *
 * @since       4.2.0
 */
#[TestDox('The CMSPlugin')]
class CMSPluginTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('has the correct dispatcher')]
    public function testInjectedDispatcher()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
        };

        $this->assertSame($dispatcher, $plugin->getDispatcher());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('has the correct dispatcher')]
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

        $this->assertSame($app, $plugin->getApplication());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('has null params when not set')]
    public function testEmptyParams()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
        };

        $this->assertNull($plugin->params);
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected params from a registry object')]
    public function testInjectedRegistryParams()
    {
        $dispatcher = new Dispatcher();
        $registry   = new Registry();

        $plugin = new class ($dispatcher, ['params' => $registry]) extends CMSPlugin {
        };

        $this->assertSame($registry, $plugin->params);
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected params from array')]
    public function testInjectedArrayParams()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, ['params' => ['test' => 'unit']]) extends CMSPlugin {
        };

        $this->assertSame('unit', $plugin->params->get('test'));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected name')]
    public function testInjectedName()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, ['name' => 'test']) extends CMSPlugin {
            public function getName()
            {
                return $this->_name;
            }
        };

        $this->assertSame('test', $plugin->getName());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected type')]
    public function testInjectedType()
    {
        $dispatcher = new Dispatcher();

        $plugin = new class ($dispatcher, ['type' => 'test']) extends CMSPlugin {
            public function getType()
            {
                return $this->_type;
            }
        };

        $this->assertSame('test', $plugin->getType());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can load the language')]
    public function testLoadLanguage()
    {
        $dispatcher = new Dispatcher();
        $language   = $this->createMock(Language::class);
        $language->expects($this->once())->method('load')->with('plg__', JPATH_ADMINISTRATOR)->willReturn(true);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
        };
        $plugin->setApplication($app);
        $plugin->loadLanguage();
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can load the language for a custom extension and path')]
    public function testLoadLanguageWithExtensionAndPath()
    {
        $dispatcher = new Dispatcher();
        $language   = $this->createMock(Language::class);
        $language->expects($this->once())->method('load')->with('test', __DIR__)->willReturn(true);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new class ($dispatcher, []) extends CMSPlugin {
        };
        $plugin->setApplication($app);
        $plugin->loadLanguage('test', __DIR__);
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('does not load the language when the path exists')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can register the listeners when is SubscriberInterface')]
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

        $this->assertSame([[$plugin, 'unit']], $dispatcher->getListeners('test'));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can register the listeners when is legacy')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can register the listeners with event interface')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('must register the listeners with event interface')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can register the listeners when has typed arguments')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can register the listeners when has untyped arguments')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can register the listeners when has nullable arguments')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can dispatch a legacy listener')]
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

        $this->assertSame(['unit'], $event->getArgument('result'));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can dispatch a legacy listener with null result')]
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

        $this->assertSame(null, $event->getArgument('result'));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can dispatch a legacy listener and contains the result from the event and the plugin')]
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

        $this->assertSame(['test', 'unit'], $event->getArgument('result'));
    }
}
