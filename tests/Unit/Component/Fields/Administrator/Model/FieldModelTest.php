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
use Joomla\Component\Fields\Administrator\Model\FieldModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for FieldModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  Fields
 * @since       6.2.1
 */
class FieldModelTest extends UnitTestCase
{
    /**
     * Data provider for testBatchMove
     *
     * Fields: 1 = com_content.article, 2 = com_content.article, 3 = com_users.user
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function batchMoveProvider(): array
    {
        $allowed = [
            'com_content.fieldgroup.5',
            'com_content.fieldgroup.0',
            'com_content.field.1',
            'com_content.field.2',
            'com_users.field.3',
            'com_users.fieldgroup.5',
        ];

        return [
            'same context move is allowed' => [
                [1, 2], 5, 'com_content.article', $allowed, true, [1 => 5, 2 => 5],
            ],
            'move to no group is allowed' => [
                [1], 0, null, $allowed, true, [1 => 0],
            ],
            'field of another context is rejected' => [
                [3], 5, 'com_content.article', $allowed, false, [],
            ],
            'whole batch is rejected if one field is of another context' => [
                [1, 3], 5, 'com_content.article', $allowed, false, [],
            ],
            'missing edit permission on the target group is rejected' => [
                [1], 5, 'com_content.article', ['com_content.field.1'], false, [],
            ],
            'missing edit permission on the field is rejected' => [
                [1, 2], 5, 'com_content.article', ['com_content.fieldgroup.5', 'com_content.field.1'], false, [],
            ],
            'not existing target group is rejected' => [
                [1], 99, null, $allowed, false, [],
            ],
            'not existing field is rejected' => [
                [1, 42], 5, 'com_content.article', $allowed, false, [],
            ],
        ];
    }

    /**
     * @param   array        $pks           The field IDs to move
     * @param   int          $groupId       The target group ID
     * @param   string|null  $groupContext  The context of the target group, null if it does not exist
     * @param   array        $allowed       The asset names the user may edit
     * @param   bool         $expected      The expected result of the batch
     * @param   array        $stored        The expected stored group IDs, keyed by field ID
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('batchMove checks the stored context and permissions of every field: $_dataName')]
    #[DataProvider('batchMoveProvider')]
    public function testBatchMove(array $pks, int $groupId, ?string $groupContext, array $allowed, bool $expected, array $stored): void
    {
        $table = new class () {
            public $id;
            public $context;
            public $group_id;
            public array $records = [
                1 => 'com_content.article',
                2 => 'com_content.article',
                3 => 'com_users.user',
            ];
            public array $stored = [];

            public function reset(): void
            {
                $this->id       = null;
                $this->context  = null;
                $this->group_id = null;
            }

            public function load($pk): bool
            {
                if (!isset($this->records[$pk])) {
                    return false;
                }

                $this->id       = $pk;
                $this->context  = $this->records[$pk];
                $this->group_id = 1;

                return true;
            }

            public function store(): bool
            {
                $this->stored[$this->id] = $this->group_id;

                return true;
            }
        };

        $db = $this->createStub(DatabaseInterface::class);
        $db->method('createQuery')->willReturn($this->getQueryStub($db));
        $db->method('setQuery')->willReturnSelf();
        $db->method('loadResult')->willReturn($groupContext);

        $user = $this->createStub(User::class);
        $user->method('authorise')->willReturnCallback(
            static fn ($action, $asset) => $action === 'core.edit' && \in_array($asset, $allowed, true)
        );

        // Skip the constructor, it needs the application, and replace the table and the cache handling
        $model = new class ($table) extends FieldModel {
            public function __construct(private object $testTable)
            {
            }

            public function getTable($name = 'Field', $prefix = 'Administrator', $options = [])
            {
                return $this->testTable;
            }

            protected function cleanCache($group = null)
            {
            }

            public function runBatchMove($value, $pks, $contexts)
            {
                return $this->batchMove($value, $pks, $contexts);
            }
        };
        $model->setDatabase($db);
        $model->setCurrentUser($user);

        $this->assertSame($expected, $model->runBatchMove($groupId, $pks, []));
        $this->assertSame($stored, $table->stored);
    }

    /**
     * Data provider for testCanSaveContext
     *
     * The user manages com_content only. Fields: 1 = com_content.article, 3 = com_users.user
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function canSaveContextProvider(): array
    {
        return [
            'new field of a managed component'           => [['id' => 0, 'context' => 'com_content.article'], 'com_content.article', true],
            'new field of another component'             => [['id' => 0, 'context' => 'com_users.user'], 'com_content.article', false],
            'new field without context, managed request' => [['id' => 0], 'com_content.article', true],
            'new field without context, other request'   => [['id' => 0], 'com_users.user', false],
            'new field with an array as context'         => [['id' => 0, 'context' => ['com_content.article']], 'com_users.user', false],
            'existing field keeps its context'           => [['id' => 1, 'context' => 'com_content.article'], 'com_content.article', true],
            'existing field without posted context'      => [['id' => 1], 'com_content.article', true],
            'existing field moved to another context'    => [['id' => 1, 'context' => 'com_users.user'], 'com_content.article', false],
            'existing field moved to another section'    => [['id' => 1, 'context' => 'com_content.categories'], 'com_content.article', false],
            'existing field of another component'        => [['id' => 3, 'context' => 'com_users.user'], 'com_content.article', false],
            'existing field of another component, none'  => [['id' => 3], 'com_content.article', false],
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
        $model->setState('field.context', $request);

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
            'fields of a managed component'  => [[1, 2], true],
            'field of another component'     => [[3], false],
            'one field of another component' => [[1, 3], false],
            'not existing field'             => [[1, 42], false],
            'ids are cast, zero is ignored'  => [['1', 0], true],
        ];
    }

    /**
     * @param   array  $pks       The field IDs
     * @param   bool   $expected  The expected result
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('canManageRecords requires core.manage on the component of every field: $_dataName')]
    #[DataProvider('canManageRecordsProvider')]
    public function testCanManageRecords(array $pks, bool $expected): void
    {
        $this->assertSame($expected, $this->createManageModel()->runCanManageRecords($pks));
    }

    /**
     * Tests that a batch with a field of a not managed component is rejected before any command runs
     *
     * @return  void
     *
     * @since   6.2.1
     */
    public function testBatchRejectsFieldOfNotManagedComponent(): void
    {
        $model = $this->createManageModel();

        $this->assertFalse($model->batch(['assetgroup_id' => 3], [1, 3], []));
    }

    /**
     * Tests that state changes and deleting require core.manage on the component of the field
     *
     * @return  void
     *
     * @since   6.2.1
     */
    public function testStateAndDeleteRequireManageOnTheFieldComponent(): void
    {
        $model  = $this->createManageModel();
        $record = (object) ['id' => 3, 'context' => 'com_users.user', 'state' => -2];

        $this->assertFalse($model->runCanEditState($record));
        $this->assertFalse($model->runCanDelete($record));
        $this->assertFalse($model->runCanEditState((object) ['id' => 0]));
    }

    /**
     * Creates a field model for a user who may do everything except managing other components than com_content
     *
     * @return  FieldModel
     *
     * @since   6.2.1
     */
    private function createManageModel(): FieldModel
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
        $model = new class ($table) extends FieldModel {
            public function __construct(private object $testTable)
            {
            }

            public function getTable($name = 'Field', $prefix = 'Administrator', $options = [])
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
