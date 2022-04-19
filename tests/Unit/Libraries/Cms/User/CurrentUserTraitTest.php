<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\User;

use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\User\User;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\BaseDatabaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class CurrentUserTraitTest extends UnitTestCase
{
	/**
	 * @testdox  Test that the current user can be set with setCurrentUser()
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetCurrentUser()
	{
		$user = new User;

		$trait = new class
		{
			use CurrentUserTrait;

			public function getUser(): User
			{
				return $this->getCurrentUser();
			}
		};

		$trait->setCurrentUser($user);

		$this->assertEquals($user, $trait->getUser());
	}

	/**
	 * @testdox  That getCurrentUser() returns an empty user object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetEmptyUser(): void
	{
		$trait = new class {
			use CurrentUserTrait;

			public function getUser(): User
			{
				return $this->getCurrentUser();
			}
		};

		$this->assertEmpty($trait->getUser()->id);
	}
}
