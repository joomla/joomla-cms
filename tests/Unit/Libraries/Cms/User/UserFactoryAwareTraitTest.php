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
use PHPUnit\Framework\Attributes\TestDox;

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
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The user factory can be set and accessed by the trait')]
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

        $this->assertSame($userFactory, $trait->getFactory());
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The user factory can be set and accessed by the trait')]
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
