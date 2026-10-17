<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Form\Filter\RulesFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for RulesFilter.
 *
 * The filter turns the permissions grid posted by the rules form field into the nested array of
 * booleans a Rules object is built from. The element, group, input registry and form are not read,
 * so they are all passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The rules form filter')]
class RulesFilterTest extends UnitTestCase
{
    /**
     * Test data for the testPermissionIsCastToBoolean method.
     *
     * Note that the check is a loose `$p == '1' || $p === 'true'`, so anything which loosely equals
     * 1 is an allow and everything else which is not an empty string is a deny.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataPermissions(): array
    {
        return [
            'string one is allow'      => [true, '1'],
            'string true is allow'     => [true, 'true'],
            'integer one is allow'     => [true, 1],
            'boolean true is allow'    => [true, true],
            'leading zero is allow'    => [true, '01'],
            'string zero is deny'      => [false, '0'],
            'string false is deny'     => [false, 'false'],
            'arbitrary string is deny' => [false, 'nonsense'],
            'minus one is deny'        => [false, '-1'],
        ];
    }

    /**
     * Tests how a single posted permission value is cast.
     *
     * @param   boolean  $expected  The boolean the value must be cast to
     * @param   mixed    $value     The posted permission value
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataPermissions')]
    #[TestDox('casts a posted permission to a boolean')]
    public function testPermissionIsCastToBoolean(bool $expected, $value): void
    {
        $filter  = new RulesFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="rules" filter="rules" />');

        $result = $filter->filter($element, ['core.edit' => ['6' => $value]], null, null, null);

        $this->assertSame(['core.edit' => [6 => $expected]], $result);
    }

    /**
     * Tests that an inherited permission, posted as an empty string, is dropped.
     *
     * The identity is left out of the action entirely rather than being stored as false, which is
     * what makes the difference between "inherit" and "deny" in the saved rules.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('drops an identity whose permission was left as inherited')]
    public function testInheritedPermissionIsDropped(): void
    {
        $filter  = new RulesFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="rules" filter="rules" />');

        $result = $filter->filter(
            $element,
            ['core.edit' => ['6' => '1', '7' => '', '8' => '0']],
            null,
            null,
            null
        );

        $this->assertSame(['core.edit' => [6 => true, 8 => false]], $result);
    }

    /**
     * Tests that an action all of whose identities are inherited survives as an empty array.
     *
     * The action key itself is created before the identities are walked, so it is kept even when
     * nothing is stored under it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps an action whose identities were all inherited, as an empty array')]
    public function testActionWithOnlyInheritedPermissionsSurvivesEmpty(): void
    {
        $filter  = new RulesFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="rules" filter="rules" />');

        $result = $filter->filter($element, ['core.edit' => ['6' => '', '7' => '']], null, null, null);

        $this->assertSame(['core.edit' => []], $result);
    }

    /**
     * Tests that several actions are all filtered.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('filters every action in the grid')]
    public function testEveryActionIsFiltered(): void
    {
        $filter  = new RulesFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="rules" filter="rules" />');

        $result = $filter->filter(
            $element,
            [
                'core.edit'       => ['6' => '1'],
                'core.delete'     => ['6' => '0'],
                'core.edit.state' => ['6' => 'true', '7' => 'false'],
            ],
            null,
            null,
            null
        );

        $this->assertSame(
            [
                'core.edit'       => [6 => true],
                'core.delete'     => [6 => false],
                'core.edit.state' => [6 => true, 7 => false],
            ],
            $result
        );
    }

    /**
     * Tests that an empty value produces an empty rules array.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('returns an empty array when nothing was posted')]
    public function testEmptyValueProducesAnEmptyArray(): void
    {
        $filter  = new RulesFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="rules" filter="rules" />');

        $this->assertSame([], $filter->filter($element, null, null, null, null));
        $this->assertSame([], $filter->filter($element, [], null, null, null));
    }

    /**
     * Tests that an object is accepted, since the value is cast to an array first.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('accepts the grid as an object')]
    public function testObjectIsAccepted(): void
    {
        $filter  = new RulesFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="rules" filter="rules" />');

        $result = $filter->filter($element, (object) ['core.edit' => ['6' => '1']], null, null, null);

        $this->assertSame(['core.edit' => [6 => true]], $result);
    }
}
