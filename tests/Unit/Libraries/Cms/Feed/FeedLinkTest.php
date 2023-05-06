<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Feed;

use InvalidArgumentException;
use Joomla\CMS\Feed\FeedLink;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for FeedLink.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class FeedLinkTest extends UnitTestCase
{
    /**
     * Tests the FeedLink::__construct() method with invalid length.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testConstructWithInvalidLength()
    {
        $this->expectException(InvalidArgumentException::class);
        new FeedLink('URI', 'self', 'application/x-pdf', 'en-GB', 'My Link', 'foobar');
    }
}
