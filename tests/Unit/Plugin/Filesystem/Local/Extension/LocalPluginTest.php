<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Filesystem\Local\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Language;
use Joomla\CMS\User\User;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;
use Joomla\Plugin\Filesystem\Local\Extension\Local;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for Local plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Local
 *
 * @since       4.3.0
 */
#[TestDox('The Local plugin')]
class LocalPluginTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('has the correct id')]
    public function testID()
    {
        $plugin = new Local(['name' => 'test'], __DIR__);

        $this->assertSame('test', $plugin->getID());
    }

    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('has the correct display name')]
    public function testDisplayName()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Local([], __DIR__);
        $plugin->setApplication($app);

        $this->assertSame('test', $plugin->getDisplayName());
    }

    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('can setup providers')]
    public function testSetupProviders()
    {
        $manager = new ProviderManager();

        $event = new MediaProviderEvent('test');
        $event->setProviderManager($manager);

        $plugin = new Local(['name' => 'test'], __DIR__);
        $plugin->onSetupProviders($event);

        $this->assertSame(['test' => $plugin], $manager->getProviders());
        $this->assertSame($plugin, $manager->getProvider('test'));
    }

    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('can deliver adapters')]
    public function testAdapters()
    {
        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getIdentity')->willReturn(new User());
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));

        $plugin   = new Local(['params' => ['directories' => '[{"directory": "tests"}]']], JPATH_ROOT);
        $plugin->setApplication($app);
        $adapters = $plugin->getAdapters();

        $this->assertCount(1, $adapters);
        $this->assertSame('tests', $adapters['tests']->getAdapterName());
    }

    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('throws an Exception when an invalid directory')]
    public function testAdaptersInvalidDirectory()
    {
        $this->expectException(\InvalidArgumentException::class);

        $plugin = new Local(['params' => ['directories' => '[{"directory": "invalid"}]']], __DIR__);
        $plugin->getAdapters();
    }
}
