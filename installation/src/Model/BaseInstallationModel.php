<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base Model for the installation model classes
 *
 * @since  4.0.0
 */
class BaseInstallationModel extends BaseDatabaseModel
{
    /**
     * Constructor
     *
     * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        // @TODO remove me when the base model is db free
        $config['dbo'] = null;

        parent::__construct($config, $factory);
    }

    /**
     * Get the current setup options from the session.
     *
     * @return  array  An array of options from the session.
     *
     * @since   3.1
     */
    public function getOptions()
    {
        return Factory::getSession()->get('setup.options', []);
    }

    /**
     * Get grouped list of environment variable names and their config counterpart.
     *
     * @param  boolean  $active   Whether return only active elements in $_ENV or whole map.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEnvironmentMap(bool $active = true): array
    {
        // Use own map instead of Config 'config.env-map' (from Container) because installation setup has different field names.
        $envMap = [
            // Site configuration
            'JOOMLA_SITE_NAME' => 'site_name',

            // Database settings.
            'JOOMLA_DB_TYPE'     => 'db_type',
            'JOOMLA_DB_HOST'     => 'db_host',
            'JOOMLA_DB_USER'     => 'db_user',
            'JOOMLA_DB_PASSWORD' => 'db_pass',
            'JOOMLA_DB_NAME'     => 'db_name',
            'JOOMLA_DB_PREFIX'   => 'db_prefix',

            'JOOMLA_DB_ENCRYPTION'             => 'db_encryption',
            'JOOMLA_DB_SSL_VERIFY_SERVER_CERT' => 'db_sslverifyservercert',
            'JOOMLA_DB_SSL_KEY'                => 'db_sslkey',
            'JOOMLA_DB_SSL_CERT'               => 'db_sslcert',
            'JOOMLA_DB_SSL_CA'                 => 'db_sslca',
            'JOOMLA_DB_SSL_CIPHER'             => 'db_sslcipher',

            'JOOMLA_PUBLIC_FOLDER' => 'public_folder',

            // Admin user settings
            'JOOMLA_ADMIN_USER'     => 'admin_user',
            'JOOMLA_ADMIN_USERNAME' => 'admin_username',
            'JOOMLA_ADMIN_PASSWORD' => 'admin_password',
            'JOOMLA_ADMIN_EMAIL'    => 'admin_email',
        ];

        if ($active) {
            $envMap = array_intersect_key($envMap, $_ENV);
        }

        return $envMap;
    }

    /**
     * Get options from environment variables
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEnvironmentOptions(): array
    {
        $envMap = $this->getEnvironmentMap();
        $config = [];

        // Load environment variables
        foreach ($envMap as $envName => $setupName) {
            // Read form $_ENV not getenv() (!!!)
            $envValue = $_ENV[$envName] ?? '';

            if ($envName === 'JOOMLA_DEBUG') {
                $envValue = !!$envValue;
            }

            $config[$setupName] = match ($envValue) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                default => $envValue,
            };
        }

        // Few tweaks
        if (!empty($config['db_host'])) {
            $config['db_encryption']       = (int) ($config['db_encryption'] ?? 0);
            $config['db_from_environment'] = true;
        }
        if (!empty($config['db_pass'])) {
            $config['db_pass_plain'] = $config['db_pass'];
        }

        if (!empty($config['admin_password'])) {
            $config['admin_password_plain'] = $config['admin_password'];
        }

        return $config;
    }
}
