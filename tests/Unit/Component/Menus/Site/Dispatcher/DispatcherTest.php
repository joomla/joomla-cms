<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_menus
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Menus\Site\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Language;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\User\User;
use Joomla\Component\Menus\Site\Dispatcher\Dispatcher;
use Joomla\Input\Input;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for the com_menus site dispatcher
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_menus
 * @since       6.2.0
 */
class DispatcherTest extends UnitTestCase
{
    /**
     * @return  array
     *
     * @since   6.2.0
     */
    public static function accessProvider(): array
    {
        return [
            'modal list, no task'               => ['items', 'modal', null, true, true],
            'modal list, empty task'            => ['items', 'modal', '', true, true],
            'modal list, display task'          => ['items', 'modal', 'display', true, true],
            'modal list without core.create'    => ['items', 'modal', null, false, false],
            'other view'                        => ['item', 'modal', null, true, false],
            'other layout'                      => ['items', 'default', null, true, false],
            'admin task items.setDefault'       => ['items', 'modal', 'items.setDefault', true, false],
            'admin task items.rebuild'          => ['items', 'modal', 'items.rebuild', true, false],
            'admin task item.getParentItem'     => ['items', 'modal', 'item.getParentItem', true, false],
            'admin task ajax.fetchAssociations' => ['items', 'modal', 'ajax.fetchAssociations', true, false],
            'task without controller'           => ['items', 'modal', 'setDefault', true, false],
        ];
    }

    /**
     * @param   string       $view        The requested view
     * @param   string       $layout      The requested layout
     * @param   string|null  $task        The requested task, null for none
     * @param   boolean      $authorised  Whether the user has core.create on com_menus
     * @param   boolean      $allowed     Whether the request is expected to pass
     *
     * @return  void
     *
     * @since   6.2.0
     */
    #[DataProvider('accessProvider')]
    #[TestDox('Test that only the modal display request is admitted')]
    public function testCheckAccess(string $view, string $layout, ?string $task, bool $authorised, bool $allowed)
    {
        $data = ['option' => 'com_menus', 'view' => $view, 'layout' => $layout];

        if ($task !== null) {
            $data['task'] = $task;
        }

        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnArgument(0);

        $user = $this->createStub(User::class);
        $user->method('authorise')->willReturn($authorised);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);
        $app->method('getIdentity')->willReturn($user);
        $app->method('isClient')->willReturn(false);

        $dispatcher = new Dispatcher($app, new Input($data), $this->createStub(MVCFactoryInterface::class));

        if (!$allowed) {
            $this->expectException(NotAllowed::class);
        }

        $dispatcher->checkAccess();

        $this->addToAssertionCount(1);
    }
}
