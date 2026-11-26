<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Fields\Administrator\Helper;

use Joomla\CMS\MVC\Model\State;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Mysqli\MysqliQuery;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests custom-field list filter values and query predicates.
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsHelperTest extends UnitTestCase
{
    /**
     * Tests that FieldsHelper::getListFilterValues() drops empty and non-scalar values but keeps zero.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testListFilterValuesIgnoreEmptyAndNonScalarInputButKeepZero(): void
    {
        foreach ([new State(), new Registry()] as $state) {
            $state->set('filter.customfield_7', ['', '0', ['nested'], null, 'blue']);
            $state->set('filter.customfield_8', 'small');
            $state->set('filter.customfield_9', '');

            $this->assertSame(['0', 'blue'], FieldsHelper::getListFilterValues($state->get('filter.customfield_7')));
            $this->assertSame(['small'], FieldsHelper::getListFilterValues($state->get('filter.customfield_8')));
            $this->assertSame([], FieldsHelper::getListFilterValues($state->get('filter.customfield_9')));
        }
    }

    /**
     * Tests that FieldsHelper::applyListFilter() binds the field id and the selected values.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testApplyListFilterBindsEachFieldAndItsSelectedValues(): void
    {
        $db    = $this->getDatabase();
        $query = $this->getWorkingQuery($db);

        FieldsHelper::applyListFilter($db, $query, 7, ['0', "O'Reilly"], 'CAST(`a`.`id` AS CHAR)');
        FieldsHelper::applyListFilter($db, $query, 8, ['B'], 'CAST(`a`.`id` AS CHAR)');

        $sql = (string) $query;
        $this->assertSame(2, substr_count($sql, 'EXISTS ('));
        $this->assertSame(2, substr_count($sql, '`#__fields_values` AS `fv`'));
        $this->assertSame(2, substr_count($sql, '`fv`.`field_id` = '));
        $this->assertStringContainsString('`fv`.`item_id` = CAST(`a`.`id` AS CHAR)', $sql);
        $this->assertSame(2, substr_count($sql, '`fv`.`value` IN ('));
        $this->assertStringNotContainsString("O'Reilly", $sql);
        $this->assertSame([7, '0', "O'Reilly", 8, 'B'], array_values(array_map(static fn ($bound) => $bound->value, $query->getBounded())));
        $this->assertSame([
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::STRING,
        ], array_values(array_map(static fn ($bound) => $bound->dataType, $query->getBounded())));
    }

    /**
     * Tests that FieldsHelper::applyListFilter() does not change the query without selected values.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmptySelectionDoesNotChangeTheQuery(): void
    {
        $db    = $this->getDatabase();
        $query = $this->getWorkingQuery($db);
        $sql   = (string) $query;

        FieldsHelper::applyListFilter($db, $query, 7, [], 'CAST(`a`.`id` AS CHAR)');

        $this->assertSame($sql, (string) $query);
        $this->assertSame([], $query->getBounded());
    }

    /**
     * Returns a database stub which quotes names and creates queries.
     *
     * @return  DatabaseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getDatabase(): DatabaseInterface
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('quoteName')->willReturnCallback(static function ($name, $alias = null): string {
            $quoted = '`' . str_replace('.', '`.`', $name) . '`';

            return $alias === null ? $quoted : $quoted . ' AS `' . $alias . '`';
        });
        $db->method('createQuery')->willReturnCallback(static fn () => new MysqliQuery($db));

        return $db;
    }

    /**
     * Returns a query for a list of items.
     *
     * @param   DatabaseInterface  $db  The database stub.
     *
     * @return  MysqliQuery
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getWorkingQuery(DatabaseInterface $db): MysqliQuery
    {
        return (new MysqliQuery($db))->select('a.id')->from('items AS a');
    }
}
