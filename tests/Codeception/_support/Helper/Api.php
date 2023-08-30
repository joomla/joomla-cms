<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Helper
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Helper;

use Codeception\Module;

/**
 * Helper class for Acceptance.
 * Here you can define custom actions.
 * All public methods declared in helper class will be available in $I.
 *
 * @package  Codeception\Module
 *
 * @since    3.7.3
 */
class Api extends Module
{
    /**
     * Creates a user for API authentication and returns a bearer token.
     *
     * @return  string  The token
     *
     * @since   4.1.0
     */
    public function getBearerToken(): string
    {
        /** @var JoomlaDb $db */
        $db = $this->getModule('Helper\\JoomlaDb');

        $desiredUserId = 3;

        if (!$db->grabFromDatabase('users', 'id', ['id' => $desiredUserId])) {
            $db->haveInDatabase(
                'users',
                [
                    'id'           => $desiredUserId,
                    'name'         => 'API',
                    'email'        => 'api@example.com',
                    'username'     => 'api',
                    'password'     => '123',
                    'block'        => 0,
                    'registerDate' => '2000-01-01',
                    'params'       => '{}',
                ],
                []
            );
            $db->haveInDatabase('user_usergroup_map', ['user_id' => $desiredUserId, 'group_id' => 8]);
            $enabledData = ['user_id' => $desiredUserId, 'profile_key' => 'joomlatoken.enabled', 'profile_value' => 1];
            $tokenData   = ['user_id' => $desiredUserId, 'profile_key' => 'joomlatoken.token', 'profile_value' => 'dOi2m1NRrnBHlhaWK/WWxh3B5tqq1INbdf4DhUmYTI4='];
            $db->haveInDatabase('user_profiles', $enabledData);
            $db->haveInDatabase('user_profiles', $tokenData);
        }

        return 'c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==';
    }

    /**
     * Creates a user for API authentication and returns a bearer token.
     *
     * @param   string  $name     The name of the config key
     * @param   string  $module   The module
     *
     * @return  string  The config key
     *
     * @since   4.1.0
     */
    public function getConfig($name, $module = 'Helper\Api'): string
    {
        return $this->getModule($module)->_getConfig()[$name];
    }
}
