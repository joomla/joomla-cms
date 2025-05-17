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
            'db' => [
                // Database required settings.
                'JOOMLA_DB_TYPE'     => 'db_type',
                'JOOMLA_DB_HOST'     => 'db_host',
                'JOOMLA_DB_USER'     => 'db_user',
                'JOOMLA_DB_PASSWORD' => 'db_pass',
                'JOOMLA_DB_NAME'     => 'db_name',
                'JOOMLA_DB_PREFIX'   => 'db_prefix',
            ],
            'db_extra' => [
                // Database optional settings.
                'JOOMLA_DB_ENCRYPTION'             => 'db_encryption',
                'JOOMLA_DB_SSL_VERIFY_SERVER_CERT' => 'db_sslverifyservercert',
                'JOOMLA_DB_SSL_KEY'                => 'db_sslkey',
                'JOOMLA_DB_SSL_CERT'               => 'db_sslcert',
                'JOOMLA_DB_SSL_CA'                 => 'db_sslca',
                'JOOMLA_DB_SSL_CIPHER'             => 'db_sslcipher',
            ]
        ];

        if ($active) {
            foreach ($envMap as $groupName => $group) {
                $activeVars = array_intersect_key($group, $_ENV);

                if ($activeVars) {
                    $envMap[$groupName] = $activeVars;
                } else {
                    unset($envMap[$groupName]);
                }
            }
        }

        return $envMap;
    }

    /**
     * Get options form environment variables
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
        foreach ($envMap as $group) {
            foreach ($group as $envName => $setupName) {
                $envValue = $_ENV[$envName] ?? '';

                if ($envName === 'JOOMLA_LOG_PRIORITIES') {
                    $envValue = json_decode($envValue, true) ?: [];
                }

                $config[$setupName] = match ($envValue) {
                    'true', '(true)' => true,
                    'false', '(false)' => false,
                    default => $envValue,
                };
            }
        }

        return $config;
    }

    /**
     * Get the current setup options from the session merged with environment options
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEnvironmentMergedOptions(): array
    {
        $options    = $this->getOptions();
        $envOptions = $this->getEnvironmentOptions();

        // Few tweaks
        if (!empty($envOptions['db_pass'])) {
            $envOptions['db_pass_plain'] = $envOptions['db_pass'];
        }

        $options = array_merge($options, $envOptions);

        return $options;
    }
}
