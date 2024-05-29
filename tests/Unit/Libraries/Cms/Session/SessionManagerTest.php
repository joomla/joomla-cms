<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Session;

use Joomla\CMS\Session\SessionManager;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for Joomla\CMS\Session\SessionManager.
 *
 * @since  4.0.0
 */
class SessionManagerTest extends UnitTestCase
{
    /**
     * Session manager being tested.
     *
     * @var    SessionManager
     * @since  4.0.0
     */
    private $manager;

    /**
     * Session handler in use by the manager.
     *
     * @var    \SessionHandlerInterface|MockObject
     * @since  4.0.0
     */
    private $sessionHandler;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        $this->sessionHandler = $this->createMock(\SessionHandlerInterface::class);

        $this->manager = new SessionManager($this->sessionHandler);
    }

    /**
     * Tests the destroySession method
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testDestroySession()
    {
        $sessionId = 'a1b2c3';

        $this->sessionHandler->expects($this->once())
            ->method('destroy')
            ->with($sessionId)
            ->willReturn(true);

        $this->assertTrue($this->manager->destroySession($sessionId));
    }

    /**
     * Tests the destroySessions method with all sessions destroyed
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testDestroySessionsAllDestroyed()
    {
        $sessionIds = [
            'a1b2c3',
            'a2b3c4',
        ];

        $this->sessionHandler->expects($this->exactly(2))
            ->method('destroy')
            ->willReturn(true);

        $this->assertTrue($this->manager->destroySessions($sessionIds));
    }

    /**
     * Tests the destroySessions method with one failure
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testDestroySessionsWithFailure()
    {
        $sessionIds = [
            'a1b2c3',
            'a2b3c4',
        ];

        $this->sessionHandler->expects($this->exactly(2))
            ->method('destroy')
            ->will($this->onConsecutiveCalls(true, false));

        $this->assertFalse($this->manager->destroySessions($sessionIds));
    }
}
