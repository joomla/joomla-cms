<?php

/**
 * @package    Joomla.UnitTest
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

namespace Joomla\Tests\Integration;

use PHPUnit\Framework\TestCase;
/**
 * Base Integration Test case for common behaviour across integration tests
 *
 * @since   4.0.0
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     *
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        if ($this instanceof DBTestInterface) {
            DBTestHelper::setupTest($this);
        }
    }
}
