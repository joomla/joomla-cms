<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Helper
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Helper;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Module;
use Exception;

/**
 * Helper class for Acceptance.
 * Here you can define custom actions.
 * All public methods declared in helper class will be available in $I.
 *
 * @package  Codeception\Module
 *
 * @since    3.7.3
 */
class Acceptance extends Module
{
    /**
     * Array of the configuration settings.
     *
     * @var      array
     * @since    3.7.3
     */
    protected static $acceptanceSuiteConfiguration = [];

    /**
     * Function to get Configuration from the acceptance.suite.yml to be used by a test
     *
     * @return  array
     *
     * @since   3.7.3
     *
     * @throws ConfigurationException
     * @throws Exception
     */
    public function getSuiteConfiguration()
    {
        if (empty(self::$acceptanceSuiteConfiguration)) {
            self::$acceptanceSuiteConfiguration = Configuration::suiteSettings('acceptance', Configuration::config());
        }

        return self::$acceptanceSuiteConfiguration;
    }
}
