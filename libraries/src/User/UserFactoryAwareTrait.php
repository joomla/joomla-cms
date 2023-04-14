<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a UserFactoryInterface Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait UserFactoryAwareTrait
{
    /**
     * UserFactoryInterface
     *
     * @var    UserFactoryInterface
     * @since  __DEPLOY_VERSION__
     */
    private $userFactory;

    /**
     * Get the UserFactoryInterface.
     *
     * @return  UserFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \UnexpectedValueException May be thrown if the UserFactory has not been set.
     */
    protected function getUserFactory(): UserFactoryInterface
    {
        if ($this->userFactory) {
            return $this->userFactory;
        }

        throw new \UnexpectedValueException('UserFactory not set in ' . __CLASS__);
    }

    /**
     * Set the user factory to use.
     *
     * @param   UserFactoryInterface  $userFactory  The user factory to use.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setUserFactory(UserFactoryInterface $userFactory): void
    {
        $this->userFactory = $userFactory;
    }
}
