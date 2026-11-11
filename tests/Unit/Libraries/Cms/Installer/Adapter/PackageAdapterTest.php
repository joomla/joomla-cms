<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     Installer
 *
 * @copyright      (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Installer\Adapter;

use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * PackageAdapterTest
 *
 * @since   __DEPLOY_VERSION__
 */
class PackageAdapterTest extends UnitTestCase
{
    /**
     * The installer reuses one package adapter for every package installed or updated in a request,
     * so a package must not start with the extensions and results of the package before it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testInstallStartsWithoutTheExtensionsOfThePreviousPackage()
    {
        $adapter = new class ($this->createStub(Installer::class)) extends PackageAdapter {
            /**
             * What the adapter carried into the install: [installedIds, results]
             *
             * @var array
             */
            public $carried;

            public function __construct(Installer $parent)
            {
                $this->parent = $parent;
            }

            public function leftOverFromThePreviousPackage(array $installedIds, array $results)
            {
                $this->installedIds = $installedIds;
                $this->results      = $results;
            }

            protected function setupInstallPaths()
            {
                $this->carried = [$this->installedIds, $this->results];

                // Stop the install at its first step; nothing past this point is under test
                throw new \RuntimeException('Stopped by the test');
            }
        };

        $adapter->setManifest(
            simplexml_load_string('<extension type="package"><name>Second</name><packagename>second</packagename></extension>')
        );
        $adapter->leftOverFromThePreviousPackage([101, 102], [['name' => 'A child of the first package', 'result' => 101]]);

        $this->assertFalse($adapter->install());
        $this->assertSame([[], []], $adapter->carried);
    }
}
