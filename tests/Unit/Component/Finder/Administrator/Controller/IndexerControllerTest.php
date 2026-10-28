<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Finder\Administrator\Controller;

use Joomla\Component\Finder\Administrator\Controller\IndexerController;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for \Joomla\Component\Finder\Administrator\Controller\IndexerController
 *
 * @since  6.2.1
 */
#[CoversMethod(IndexerController::class, 'getHttpStatus')]
class IndexerControllerTest extends UnitTestCase
{
    /**
     * Exception codes which are not HTTP error statuses, e.g. 0 for database errors, must not be
     * sent as status header, as the web server rejects them.
     *
     * @param   integer  $code      The exception code
     * @param   integer  $expected  The expected HTTP status
     *
     * @return void
     *
     * @since   6.2.1
     */
    #[DataProvider('statusProvider')]
    public function testGetHttpStatus(int $code, int $expected): void
    {
        $this->assertSame($expected, IndexerController::getHttpStatus($code));
    }

    /**
     * @return array
     *
     * @since   6.2.1
     */
    public static function statusProvider(): array
    {
        return [
            'database error'     => [0, 500],
            'driver error code'  => [1045, 500],
            'success status'     => [200, 500],
            'negative'           => [-1, 500],
            'lowest client error' => [400, 400],
            'forbidden'          => [403, 403],
            'not found'          => [404, 404],
            'server error'       => [500, 500],
            'highest server error' => [599, 599],
            'out of range'       => [600, 500],
        ];
    }
}
