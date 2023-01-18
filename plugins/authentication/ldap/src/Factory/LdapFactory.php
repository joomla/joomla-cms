<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Plugin\Authentication\Ldap\Factory;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory to create Ldap clients.
 *
 * @since  __DEPLOY_VERSION__
 */
class LdapFactory implements LdapFactoryInterface
{
    /**
     * Method to load and return an Ldap client.
     *
     * @param   array  $config  The configuration array for the ldap client
     *
     * @return  LdapInterface
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \Exception
     */
    public function createLdap(array $config): LdapInterface
    {
        return Ldap::create('ext_ldap', $config);
    }
}
