<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Cache;

use Joomla\CMS\Cache\CacheControllerFactory;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Cache\CacheControllerFactoryAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @testdoc     The CacheControllerFactoryAwareTrait
 *
 * @since       4.2.0
 */
class CacheControllerFactoryAwareTraitTest extends UnitTestCase
{
    /**
     * @testdox  can set a cache controller factory
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetCacheControllerFactory()
    {
        $cacheControllerFactory = new CacheControllerFactory();

        $trait = new class () {
            use CacheControllerFactoryAwareTrait;

            public function getFactory(): CacheControllerFactory
            {
                return $this->getCacheControllerFactory();
            }
        };

        $trait->setCacheControllerFactory($cacheControllerFactory);

        $this->assertEquals($cacheControllerFactory, $trait->getFactory());
    }
}
