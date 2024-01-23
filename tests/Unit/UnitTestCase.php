<?php

/**
 * @package    Joomla.UnitTest
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

namespace Joomla\Tests\Unit;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\QueryInterface;

/**
 * Base Unit Test case for common behaviour across unit tests
 *
 * @since   4.0.0
 */
abstract class UnitTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Returns a database query instance.
     *
     * @param   DatabaseInterface  $db  The database
     *
     * @return  QueryInterface
     *
     * @since   4.2.0
     */
    protected function getQueryStub(DatabaseInterface $db): QueryInterface
    {
        return new class ($db) extends DatabaseQuery {
            public function groupConcat($expression, $separator = ',')
            {
            }

            public function processLimit($query, $limit, $offset = 0)
            {
            }
        };
    }
}
