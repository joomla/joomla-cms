<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Integrations.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration;

use Joomla\Database\DatabaseFactory;

/**
 * Integration Tests
 *
 * @since   4.0.0
 */
class DBTestHelper
{
    /**
     * Driver
     *
     * @var string
     *
     * @since   4.0.0
     */
    protected static $driver;

    /**
     * Files Loaded
     *
     * @var array
     *
     * @since   4.0.0
     */
    protected static $loadedFiles = [];

    /**
     * @param   mixed   IntegrationTestCase   $test  Test
     *
     * @return void
     * @since   4.0.0
     */
    public static function setupTest(IntegrationTestCase $test): void
    {
        if (!self::$driver) {
            $factory      = new DatabaseFactory();
            self::$driver = $factory->getDriver(
                JTEST_DB_ENGINE,
                [
                    'database' => JTEST_DB_NAME,
                    'host'     => JTEST_DB_HOST,
                    'user'     => JTEST_DB_USER,
                    'password' => JTEST_DB_PASSWORD,
                    'prefix'   => 'jos' . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . '_',
                ]
            );
        }

        $test->setDBDriver(self::$driver);
        $files = $test->getSchemasToLoad();

        foreach ($files as $file) {
            if (in_array($file, self::$loadedFiles)) {
                continue;
            }

            $sql     = file_get_contents(JPATH_ROOT . '/tests/Integration/datasets/' . strtolower(JTEST_DB_ENGINE) . '/' . $file);
            $queries = self::splitQueries($sql);

            if (!count($queries)) {
                continue;
            }

            foreach ($queries as $query) {
                self::$driver->setQuery($query);
                self::$driver->execute();
            }

            self::$loadedFiles[] = $file;
        }
    }

    /**
     * @param   string  $query   Query
     *
     * @return array
     *
     * @since   4.0.0
     */
    protected static function splitQueries($query)
    {
        $buffer    = [];
        $queries   = [];
        $in_string = false;

        // Trim any whitespace.
        $query = trim($query);

        // Remove comment lines.
        $query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);

        // Remove PostgreSQL comment lines.
        $query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);

        // Find function.
        $funct = explode('CREATE OR REPLACE FUNCTION', $query);

        // Save sql before function and parse it.
        $query = $funct[0];

        // Parse the schema file to break up queries.
        for ($i = 0; $i < strlen($query) - 1; $i++) {
            if ($query[$i] == ';' && !$in_string) {
                $queries[] = substr($query, 0, $i);
                $query     = substr($query, $i + 1);
                $i         = 0;
            }

            if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\") {
                $in_string = false;
            } elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $query[$i];
            }

            if (isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }

            $buffer[1] = $query[$i];
        }

        // If the is anything left over, add it to the queries.
        if (!empty($query)) {
            $queries[] = $query;
        }

        // Add function part as is.
        for ($f = 1, $fMax = count($funct); $f < $fMax; $f++) {
            $queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
        }

        return $queries;
    }
}
