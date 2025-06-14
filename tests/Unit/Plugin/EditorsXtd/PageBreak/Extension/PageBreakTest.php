<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\EditorsXtd\PageBreak\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Editor\Button\ButtonsRegistry;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Language;
use Joomla\CMS\User\User;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\EditorsXtd\PageBreak\Extension\PageBreak;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for PageBreak editor button plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  PageBreak
 *
 * @testdox     The PageBreak plugin
 *
 * @since       4.4.0
 */
class PageBreakTest extends UnitTestCase
{
    /**
     * @testdox  that the button is properly loaded
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testButton()
    {
        $user = $this->createStub(User::class);
        $user->method('authorise')->willReturn(true);
        $user->method('getAuthorisedCategories')->willReturn([]);

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));
        $app->method('getIdentity')->willReturn($user);
        $app->method('getDocument')->willReturn(new Document());

        $btnsReg = new ButtonsRegistry();
        $event   = new EditorButtonsSetupEvent('onEditorButtonsSetup', [
            'subject'         => $btnsReg,
            'editorType'      => 'none',
            'disabledButtons' => [],
        ]);

        $dispatcher = new Dispatcher();
        $plugin     = new PageBreak($dispatcher, ['name' => 'pagebreak', 'type' => 'editors-xtd', 'params' => []]);
        $plugin->setApplication($app);
        $plugin->onEditorButtonsSetup($event);

        $button = $btnsReg->getAll()[0] ?? false;

        $this->assertInstanceOf(Button::class, $button);
        $this->assertEquals('pagebreak', $button->getButtonName());
        $this->assertEquals('modal', $button->get('action'));
        $this->assertNotEmpty($button->get('link'));
        $this->assertNotEmpty($button->get('text'));
        $this->assertNotEmpty($button->get('icon'));
        $this->assertNotEmpty($button->get('iconSVG'));
    }

    /**
     * @testdox  that the user has not enough permission
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testInvalidPermissions()
    {
        $user = $this->createStub(User::class);
        $user->method('authorise')->willReturn(false);
        $user->method('getAuthorisedCategories')->willReturn([]);

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getIdentity')->willReturn($user);

        $btnsReg = new ButtonsRegistry();
        $event   = new EditorButtonsSetupEvent('onEditorButtonsSetup', [
            'subject'         => $btnsReg,
            'editorType'      => 'none',
            'disabledButtons' => [],
        ]);

        $dispatcher = new Dispatcher();
        $plugin     = new PageBreak($dispatcher, ['name' => 'pagebreak', 'type' => 'editors-xtd', 'params' => []]);
        $plugin->setApplication($app);
        $plugin->onEditorButtonsSetup($event);

        $button = $btnsReg->getAll()[0] ?? false;

        $this->assertFalse($button);
    }

    /**
     * @testdox  that the button is not returned when a none web CMS application is used
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testInvalidApplication()
    {
        $btnsReg = new ButtonsRegistry();
        $event   = new EditorButtonsSetupEvent('onEditorButtonsSetup', [
            'subject'         => $btnsReg,
            'editorType'      => 'none',
            'disabledButtons' => [],
        ]);

        $dispatcher = new Dispatcher();
        $plugin     = new PageBreak($dispatcher, ['name' => 'pagebreak', 'type' => 'editors-xtd', 'params' => []]);
        $plugin->setApplication($this->createStub(CMSApplicationInterface::class));
        $plugin->onEditorButtonsSetup($event);

        $button = $btnsReg->getAll()[0] ?? false;

        $this->assertFalse($button);
    }
}
