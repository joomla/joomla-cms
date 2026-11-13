<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Session;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Session\MetadataManager;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Session\SessionInterface;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for Joomla\CMS\Session\MetadataManager.
 *
 * @testdox  The session metadata manager
 *
 * @since    5.4.10
 */
class MetadataManagerTest extends UnitTestCase
{
    /**
     * Database driver mock in use by the manager.
     *
     * @var    DatabaseInterface|MockObject
     * @since  5.4.10
     */
    private $db;

    /**
     * Query mock returned by the database driver.
     *
     * @var    DatabaseQuery|MockObject
     * @since  5.4.10
     */
    private $query;

    /**
     * Sets up the fixture.
     *
     * @return  void
     *
     * @since   5.4.10
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->createMock(DatabaseQuery::class);

        foreach (['select', 'from', 'where', 'bind', 'insert', 'columns', 'values', 'set'] as $fluentMethod) {
            $this->query->method($fluentMethod)->willReturnSelf();
        }

        $this->db = $this->createMock(DatabaseInterface::class);
        $this->db->method('getQuery')->willReturn($this->query);
        $this->db->method('quoteName')->willReturnCallback(
            static fn ($name) => \is_string($name) ? $name : implode(', ', (array) $name)
        );
        $this->db->method('setQuery')->willReturnSelf();
    }

    /**
     * Builds a manager with a database whose existence check returns the given loadResult.
     *
     * @param   string|null|\Throwable  $loadResult  The value loadResult() should return or throw
     *
     * @return  MetadataManager
     *
     * @since   5.4.10
     */
    private function buildManager($loadResult): MetadataManager
    {
        if ($loadResult instanceof \Throwable) {
            $this->db->method('loadResult')->willThrowException($loadResult);
        } else {
            $this->db->method('loadResult')->willReturn($loadResult);
        }

        return new MetadataManager($this->createStub(AbstractApplication::class), $this->db);
    }

    /**
     * Builds a session double returning the given id.
     *
     * @param   string  $sessionId  The session id
     *
     * @return  SessionInterface|MockObject
     *
     * @since   5.4.10
     */
    private function buildSession(string $sessionId)
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('getId')->willReturn($sessionId);
        $session->method('isNew')->willReturn(true);

        return $session;
    }

    /**
     * @testdox  creates the metadata record when no record exists yet
     *
     * @return  void
     *
     * @since   5.4.10
     */
    public function testCreateRecordIfNonExistingInsertsWhenRecordIsMissing()
    {
        $manager = $this->buildManager(null);

        $this->db->expects($this->once())
            ->method('execute');

        $manager->createRecordIfNonExisting($this->buildSession('missing-session-id'), new User());
    }

    /**
     * @testdox  does not touch the database when the metadata record already exists
     *
     * @return  void
     *
     * @since   5.4.10
     */
    public function testCreateRecordIfNonExistingSkipsInsertWhenRecordExists()
    {
        $manager = $this->buildManager('existing-session-id');

        $this->db->expects($this->never())
            ->method('execute');

        $manager->createRecordIfNonExisting($this->buildSession('existing-session-id'), new User());
    }

    /**
     * @testdox  does not touch the database when the record state cannot be determined
     *
     * @return  void
     *
     * @since   5.4.10
     */
    public function testCreateRecordIfNonExistingSkipsInsertWhenRecordStateIsUnknown()
    {
        $manager = $this->buildManager(new ExecutionFailureException('SELECT', 'database offline'));

        $this->db->expects($this->never())
            ->method('execute');

        $manager->createRecordIfNonExisting($this->buildSession('unknown-session-id'), new User());
    }
}
