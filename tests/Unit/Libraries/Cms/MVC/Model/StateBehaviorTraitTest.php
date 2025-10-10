<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Model;

use Joomla\CMS\MVC\Model\State;
use Joomla\CMS\MVC\Model\StateBehaviorTrait;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\StateBehaviorTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @testdox     The StateBehaviorTrait
 *
 * @since       4.2.0
 */
class StateBehaviorTraitTest extends UnitTestCase
{
    /**
     * @testdox  can fetch an empty state
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetEmptyState()
    {
        $trait = new class () {
            use StateBehaviorTrait;
        };

        $this->assertInstanceOf(State::class, $trait->getState());
    }

    /**
     * @testdox  does populate the state when a state is requested
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testStatePopulation()
    {
        $trait = new class () {
            use StateBehaviorTrait;

            protected function populateState()
            {
                $this->setState('state.set', true);
            }
        };

        $this->assertTrue($trait->getState('state.set', false));
    }

    /**
     * @testdox  does not populated the state when already set
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testStatePopulationIgnored()
    {
        $trait = new class () {
            use StateBehaviorTrait;

            public function __construct()
            {
                $this->__state_set = true;
            }

            protected function populateState()
            {
                $this->setState('state.set', true);
            }
        };

        $this->assertFalse($trait->getState('state.set', false));
    }

    /**
     * @testdox  sets the state correctly
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetState()
    {
        $trait = new class () {
            use StateBehaviorTrait;
        };
        $trait->setState('state.set', true);

        $this->assertTrue($trait->getState('state.set', false));
    }

    /**
     * @testdox  overwrites the state when it is not populated
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetStateWithPopulation()
    {
        $trait = new class () {
            use StateBehaviorTrait;

            protected function populateState()
            {
                $this->setState('state.status', 1);
            }
        };
        $trait->setState('state.status', 2);

        $this->assertEquals(1, $trait->getState('state.status'));
    }

    /**
     * @testdox  does not overwrite the state when it is already populated
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetStateWithPrePopulation()
    {
        $trait = new class () {
            use StateBehaviorTrait;

            protected function populateState()
            {
                $this->setState('state.status', 1);
            }
        };
        $trait->getState();
        $trait->setState('state.status', 2);

        $this->assertEquals(2, $trait->getState('state.status'));
    }
}
