<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Client;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Client helper class
 *
 * @since  1.7.0
 */
class ClientHelper
{
    /**
     * Method to return the array of client layer configuration options
     *
     * @param   string   $client  Client name, currently only 'ftp' is supported
     * @param   boolean  $force   Forces re-creation of the login credentials. Set this to
     *                            true if login credentials in the session storage have changed
     *
     * @return  array    Client layer configuration options, consisting of at least
     *                   these fields: enabled, host, port, user, pass, root
     *
     * @since   1.7.0
     */
    public static function getCredentials($client, $force = false)
    {
        static $credentials = [];

        $client = strtolower($client);

        if (!isset($credentials[$client]) || $force) {
            $app = Factory::getApplication();

            // Fetch the client layer configuration options for the specific client
            switch ($client) {
                case 'ftp':
                    $options = [
                        'enabled' => $app->get('ftp_enable'),
                        'host'    => $app->get('ftp_host'),
                        'port'    => $app->get('ftp_port'),
                        'user'    => $app->get('ftp_user'),
                        'pass'    => $app->get('ftp_pass'),
                        'root'    => $app->get('ftp_root'),
                    ];
                    break;

                default:
                    $options = ['enabled' => false, 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'root' => ''];
                    break;
            }

            // If user and pass are not set in global config lets see if they are in the session
            if ($options['enabled'] == true && ($options['user'] == '' || $options['pass'] == '')) {
                $session         = Factory::getSession();
                $options['user'] = $session->get($client . '.user', null, 'JClientHelper');
                $options['pass'] = $session->get($client . '.pass', null, 'JClientHelper');
            }

            // If user or pass are missing, disable this client
            if ($options['user'] == '' || $options['pass'] == '') {
                $options['enabled'] = false;
            }

            // Save the credentials for later use
            $credentials[$client] = $options;
        }

        return $credentials[$client];
    }

    /**
     * Method to set client login credentials
     *
     * @param   string  $client  Client name, currently only 'ftp' is supported
     * @param   string  $user    Username
     * @param   string  $pass    Password
     *
     * @return  boolean  True if the given login credentials have been set and are valid
     *
     * @since   1.7.0
     */
    public static function setCredentials($client, $user, $pass)
    {
        $return = false;
        $client = strtolower($client);

        // Test if the given credentials are valid
        switch ($client) {
            case 'ftp':
                $app     = Factory::getApplication();
                $options = ['enabled' => $app->get('ftp_enable'), 'host' => $app->get('ftp_host'), 'port' => $app->get('ftp_port')];

                if ($options['enabled']) {
                    $ftp = FtpClient::getInstance($options['host'], $options['port']);

                    // Test the connection and try to log in
                    if ($ftp->isConnected()) {
                        if ($ftp->login($user, $pass)) {
                            $return = true;
                        }

                        $ftp->quit();
                    }
                }
                break;

            default:
                break;
        }

        if ($return) {
            // Save valid credentials to the session
            $session = Factory::getSession();
            $session->set($client . '.user', $user, 'JClientHelper');
            $session->set($client . '.pass', $pass, 'JClientHelper');

            // Force re-creation of the data saved within JClientHelper::getCredentials()
            self::getCredentials($client, true);
        }

        return $return;
    }

    /**
     * Method to determine if client login credentials are present
     *
     * @param   string  $client  Client name, currently only 'ftp' is supported
     *
     * @return  boolean  True if login credentials are available
     *
     * @since   1.7.0
     */
    public static function hasCredentials($client)
    {
        $return = false;
        $client = strtolower($client);

        // Get (unmodified) credentials for this client
        switch ($client) {
            case 'ftp':
                $app     = Factory::getApplication();
                $options = ['enabled' => $app->get('ftp_enable'), 'user' => $app->get('ftp_user'), 'pass' => $app->get('ftp_pass')];
                break;

            default:
                $options = ['enabled' => false, 'user' => '', 'pass' => ''];
                break;
        }

        if ($options['enabled'] == false) {
            // The client is disabled in global config, so let's pretend we are OK
            $return = true;
        } elseif ($options['user'] != '' && $options['pass'] != '') {
            // Login credentials are available in global config
            $return = true;
        } else {
            // Check if login credentials are available in the session
            $session = Factory::getSession();
            $user    = $session->get($client . '.user', null, 'JClientHelper');
            $pass    = $session->get($client . '.pass', null, 'JClientHelper');

            if ($user != '' && $pass != '') {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Determine whether input fields for client settings need to be shown
     *
     * If valid credentials were passed along with the request, they are saved to the session.
     * This functions returns an exception if invalid credentials have been given or if the
     * connection to the server failed for some other reason.
     *
     * @param   string  $client  The name of the client.
     *
     * @return  boolean  True if credentials are present
     *
     * @since   1.7.0
     * @throws  \InvalidArgumentException if credentials invalid
     */
    public static function setCredentialsFromRequest($client)
    {
        // Determine whether FTP credentials have been passed along with the current request
        $input = Factory::getApplication()->getInput();
        $user  = $input->post->getString('username', null);
        $pass  = $input->post->getString('password', null);

        if ($user != '' && $pass != '') {
            // Add credentials to the session
            if (!self::setCredentials($client, $user, $pass)) {
                throw new \InvalidArgumentException('Invalid user credentials');
            }

            $return = false;
        } else {
            // Just determine if the FTP input fields need to be shown
            $return = !self::hasCredentials('ftp');
        }

        return $return;
    }
}
