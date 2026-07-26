<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Model;

use Joomla\CMS\MVC\Model\State;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\Model\State
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       5.0.0
 */
#[TestDox('The State')]
class StateTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   5.0.0
     */
    #[TestDox('can set and get a property')]
    public function testGetProperties()
    {
        $state = new State();
        $state->set('unit', 'test');

        $this->assertCount(1, $state->getProperties());
        $this->assertEquals('test', $state->getProperties()['unit']);
    }

    /**
     * @return  void
     *
     * @since   5.0.0
     */
    #[TestDox('can access property')]
    public function testGetDirectPropertyAccess()
    {
        $state = new State();
        $state->set('unit', 'test');

        $this->assertEquals('test', $state->unit);
        $this->assertEquals('test', $state->get('unit'));
    }

    /**
     * @return  void
     *
     * @since   5.0.0
     */
    #[TestDox('can set a value through the direct property')]
    public function testSetDirectPropertyAccess()
    {
        $state       = new State();
        $state->unit = 'test';

        $this->assertEquals('test', $state->unit);
        $this->assertEquals('test', $state->get('unit'));
    }

    /**
     * @return  void
     *
     * @since   5.0.0
     */
    #[TestDox('can return if a property is set')]
    public function testIsSet()
    {
        $state       = new State();
        $state->unit = 'test';

        $this->assertTrue(isset($state->unit));
    }

    /**
     * @return  void
     *
     * @since   5.0.0
     */
    #[TestDox('can return if a property is not set')]
    public function testIsNotSet()
    {
        $state = new State();

        $this->assertFalse(isset($state->unit));
    }

    /**
     * @return  void
     *
     * @since   5.0.0
     */
    #[TestDox('can set and get an empty value')]
    public function testEmptyValue()
    {
        $state = new State();
        $state->set('unit', '');

        $this->assertEquals('', $state->get('unit', 'test'));
    }
}
