<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Fields\Administrator\Model;

use Joomla\CMS\User\User;
use Joomla\Component\Fields\Administrator\Model\GroupModel;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for GroupModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  Fields
 * @since       6.2.1
 */
class GroupModelTest extends UnitTestCase
{
    /**
     * Data provider for testCanSaveContext
     *
     * The user manages com_content only. Groups: 1 = com_content.article, 3 = com_users.user
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function canSaveContextProvider(): array
    {
        return [
            'new group of a managed component'           => [['id' => 0, 'context' => 'com_content.article'], 'com_content.article', true],
            'new group of another component'             => [['id' => 0, 'context' => 'com_users.user'], 'com_content.article', false],
            'new group without context, managed request' => [['id' => 0], 'com_content.article', true],
            'new group without context, other request'   => [['id' => 0], 'com_users.user', false],
            'new group with an array as context'         => [['id' => 0, 'context' => ['com_content.article']], 'com_users.user', false],
            'existing group keeps its context'           => [['id' => 1, 'context' => 'com_content.article'], 'com_content.article', true],
            'existing group without posted context'      => [['id' => 1], 'com_content.article', true],
            'existing group moved to another context'    => [['id' => 1, 'context' => 'com_users.user'], 'com_content.article', false],
            'existing group moved to another section'    => [['id' => 1, 'context' => 'com_content.categories'], 'com_content.article', false],
            'existing group of another component'        => [['id' => 3, 'context' => 'com_users.user'], 'com_content.article', false],
            'existing group of another component, none'  => [['id' => 3], 'com_content.article', false],
        ];
    }

    /**
     * @param   array   $data      The form data
     * @param   string  $request   The context of the request
     * @param   bool    $expected  The expected result
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('canSaveContext checks the stored context and core.manage on its component: $_dataName')]
    #[DataProvider('canSaveContextProvider')]
    public function testCanSaveContext(array $data, string $request, bool $expected): void
    {
        $model = $this->createManageModel();
        $model->setState('filter.context', $request);

        $this->assertSame($expected, $model->runCanSaveContext($data));
    }

    /**
     * Data provider for testCanManageRecords
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function canManageRecordsProvider(): array
    {
        return [
            'groups of a managed component'  => [[1, 2], true],
            'group of another component'     => [[3], false],
            'one group of another component' => [[1, 3], false],
            'not existing group'             => [[1, 42], false],
            'ids are cast, zero is ignored'  => [['1', 0], true],
        ];
    }

    /**
     * @param   array  $pks       The group IDs
     * @param   bool   $expected  The expected result
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('canManageRecords requires core.manage on the component of every group: $_dataName')]
    #[DataProvider('canManageRecordsProvider')]
    public function testCanManageRecords(array $pks, bool $expected): void
    {
        $this->assertSame($expected, $this->createManageModel()->runCanManageRecords($pks));
    }

    /**
     * Tests that a batch with a group of a not managed component is rejected before any command runs
     *
     * @return  void
     *
     * @since   6.2.1
     */
    public function testBatchRejectsGroupOfNotManagedComponent(): void
    {
        $model = $this->createManageModel();

        $this->assertFalse($model->batch(['assetgroup_id' => 3], [1, 3], []));
    }

    /**
     * Tests that state changes and deleting require core.manage on the component of the group
     *
     * @return  void
     *
     * @since   6.2.1
     */
    public function testStateAndDeleteRequireManageOnTheGroupComponent(): void
    {
        $model  = $this->createManageModel();
        $record = (object) ['id' => 3, 'context' => 'com_users.user', 'state' => -2];

        $this->assertFalse($model->runCanEditState($record));
        $this->assertFalse($model->runCanDelete($record));
        $this->assertFalse($model->runCanEditState((object) ['id' => 0]));
    }

    /**
     * Tests that a group of a managed component can still change its state and be deleted
     *
     * @return  void
     *
     * @since   6.2.1
     */
    public function testStateAndDeleteOfManagedComponent(): void
    {
        $record = (object) ['id' => 1, 'context' => 'com_content.article', 'state' => -2];
        $model  = $this->createManageModel();

        $this->assertTrue($model->runCanEditState($record));
        $this->assertTrue($model->runCanDelete($record));
    }

    /**
     * Creates a group model for a user who may do everything except managing other components than com_content
     *
     * @return  GroupModel
     *
     * @since   6.2.1
     */
    private function createManageModel(): GroupModel
    {
        $table = new class () {
            public $id;
            public $context;
            public array $records = [
                1 => 'com_content.article',
                2 => 'com_content.article',
                3 => 'com_users.user',
            ];

            public function reset(): void
            {
                $this->id      = null;
                $this->context = null;
            }

            public function load($pk): bool
            {
                if (!isset($this->records[$pk])) {
                    return false;
                }

                $this->id      = $pk;
                $this->context = $this->records[$pk];

                return true;
            }
        };

        $user = $this->createStub(User::class);
        $user->method('authorise')->willReturnCallback(
            static fn ($action, $asset) => $action !== 'core.manage' || $asset === 'com_content'
        );

        // Skip the constructor and the state population, they need the application
        $model = new class ($table) extends GroupModel {
            public function __construct(private object $testTable)
            {
            }

            public function getTable($name = 'Group', $prefix = 'Administrator', $options = [])
            {
                return $this->testTable;
            }

            protected function populateState()
            {
            }

            public function runCanSaveContext(array $data)
            {
                return $this->canSaveContext($data);
            }

            public function runCanManageRecords(array $pks)
            {
                return $this->canManageRecords($pks);
            }

            public function runCanEditState($record)
            {
                return $this->canEditState($record);
            }

            public function runCanDelete($record)
            {
                return $this->canDelete($record);
            }
        };
        $model->setCurrentUser($user);

        return $model;
    }
}
