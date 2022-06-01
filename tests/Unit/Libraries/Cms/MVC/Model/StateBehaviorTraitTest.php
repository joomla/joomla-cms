<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\MVC\Model;

use Joomla\CMS\MVC\Model\StateBehaviorTrait;
use Joomla\CMS\Object\CMSObject;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\StateBehaviorTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class StateBehaviorTraitTest extends UnitTestCase
{
	/**
	 * @testdox  The full empty state can be fetched
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetEmptyState()
	{
		$trait = new class
		{
			use StateBehaviorTrait;
		};

		$this->assertInstanceOf(CMSObject::class, $trait->getState());
	}

	/**
	 * @testdox  The state is populated
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testStatePopulation()
	{
		$trait = new class
		{
			use StateBehaviorTrait;

			protected function populateState()
			{
				$this->setState('state.set', true);
			}
		};

		$this->assertTrue($trait->getState('state.set', false));
	}

	/**
	 * @testdox  The state is not populated when already set
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testStatePopulationIgnored()
	{
		$trait = new class
		{
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
	 * @testdox  The state can be set correctly
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetState()
	{
		$trait = new class
		{
			use StateBehaviorTrait;
		};
		$trait->setState('state.set', true);

		$this->assertTrue($trait->getState('state.set', false));
	}

	/**
	 * @testdox  The state gets overwritten when it is not populated
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetStateWithPopulation()
	{
		$trait = new class
		{
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
	 * @testdox  The state can be overwritten when it is already populated
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetStateWithPrePopulation()
	{
		$trait = new class
		{
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
