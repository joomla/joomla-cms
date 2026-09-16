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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\Model\StateBehaviorTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       4.2.0
 */
#[TestDox('The StateBehaviorTrait')]
class StateBehaviorTraitTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can fetch an empty state')]
    public function testGetEmptyState()
    {
        $trait = new class () {
            use StateBehaviorTrait;
        };

        $this->assertInstanceOf(State::class, $trait->getState());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('does populate the state when a state is requested')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('does not populated the state when already set')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('sets the state correctly')]
    public function testSetState()
    {
        $trait = new class () {
            use StateBehaviorTrait;
        };
        $trait->setState('state.set', true);

        $this->assertTrue($trait->getState('state.set', false));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('overwrites the state when it is not populated')]
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

        $this->assertSame(1, $trait->getState('state.status'));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('does not overwrite the state when it is already populated')]
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

        $this->assertSame(2, $trait->getState('state.status'));
    }
}
