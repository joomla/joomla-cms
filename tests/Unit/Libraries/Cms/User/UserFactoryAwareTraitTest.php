<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\User;

use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\User\UserFactoryAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       4.4.0
 */
class UserFactoryAwareTraitTest extends UnitTestCase
{
    /**
     * @testdox  The user factory can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testGetSetUserFactory()
    {
        $userFactory = new class () implements UserFactoryInterface {
            public function loadUserById(int $id): User
            {
                return new User();
            }

            public function loadUserByUsername(string $username): User
            {
                return new User();
            }
        };

        $trait = new class () {
            use UserFactoryAwareTrait;

            public function getFactory(): UserFactoryInterface
            {
                return $this->getUserFactory();
            }
        };

        $trait->setUserFactory($userFactory);

        $this->assertEquals($userFactory, $trait->getFactory());
    }

    /**
     * @testdox  The user factory can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testGetUserFactoryThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $trait = new class () {
            use UserFactoryAwareTrait;

            public function getFactory(): UserFactoryInterface
            {
                return $this->getUserFactory();
            }
        };

        $trait->getFactory();
    }
}
