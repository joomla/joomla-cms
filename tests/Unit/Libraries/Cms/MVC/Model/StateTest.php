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

/**
 * Test class for \Joomla\CMS\MVC\Model\State
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @testdox     The State
 *
 * @since       5.0.0
 */
class StateTest extends UnitTestCase
{
    /**
     * @testdox  can set and get a property
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testGetProperties()
    {
        $state = new State();
        $state->set('unit', 'test');

        $this->assertCount(1, $state->getProperties());
        $this->assertEquals('test', $state->getProperties()['unit']);
    }

    /**
     * @testdox  can access property
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testGetDirectPropertyAccess()
    {
        $state = new State();
        $state->set('unit', 'test');

        $this->assertEquals('test', $state->unit);
        $this->assertEquals('test', $state->get('unit'));
    }

    /**
     * @testdox  can set a value through the direct property
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testSetDirectPropertyAccess()
    {
        $state       = new State();
        $state->unit = 'test';

        $this->assertEquals('test', $state->unit);
        $this->assertEquals('test', $state->get('unit'));
    }

    /**
     * @testdox  can return if a property is set
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testIsSet()
    {
        $state       = new State();
        $state->unit = 'test';

        $this->assertTrue(isset($state->unit));
    }

    /**
     * @testdox  can return if a property is not set
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testIsNotSet()
    {
        $state = new State();

        $this->assertFalse(isset($state->unit));
    }

    /**
     * @testdox  can set and get an empty value
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testEmptyValue()
    {
        $state = new State();
        $state->set('unit', '');

        $this->assertEquals('', $state->get('unit', 'test'));
    }
}
