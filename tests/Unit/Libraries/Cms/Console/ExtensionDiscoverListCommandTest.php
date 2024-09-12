<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Console
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Console;

use Joomla\CMS\Console\ExtensionDiscoverListCommand;
use Joomla\Database\DatabaseInterface;

/**
 * Test class for Joomla\CMS\Console\ExtensionDiscoverCommand.
 *
 * @since   4.0.0
 */
class ExtensionDiscoverListCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the constructor
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testIsConstructable()
    {
        $this->assertInstanceOf(ExtensionDiscoverListCommand::class, $this->createExtensionDiscoverListCommand());
    }

    /**
     * Tests the filterExtensionsBasedOnState method
     * Ensure that the return value is an array and the filter works correctly.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testFilterExtensions()
    {
        $command = $this->createExtensionDiscoverListCommand();

        $state = -1;

        $extensions0    = [];
        $extensions0[0] = ['state' => 0];
        $extensions0[1] = ['state' => 0];

        $extensions1    = [];
        $extensions1[0] = ['state' => 0];
        $extensions1[1] = ['state' => -1];

        $extensions2    = [];
        $extensions2[0] = ['state' => -1];
        $extensions2[1] = ['state' => -1];

        $filteredextensionsArray0 = $command->filterExtensionsBasedOnState($extensions0, $state);
        $filteredextensionsArray1 = $command->filterExtensionsBasedOnState($extensions1, $state);
        $filteredextensionsArray2 = $command->filterExtensionsBasedOnState($extensions2, $state);

        $size0 = \sizeof($filteredextensionsArray0);
        $size1 = \sizeof($filteredextensionsArray1);
        $size2 = \sizeof($filteredextensionsArray2);

        $this->assertSame($size0, 0);
        $this->assertSame($size1, 1);
        $this->assertSame($size2, 2);

        $this->assertIsArray($filteredextensionsArray0);
        $this->assertIsArray($filteredextensionsArray1);
        $this->assertIsArray($filteredextensionsArray2);
    }

    /**
     * Helper function to create a ExtensionDiscoverCommand
     *
     * @return  ExtensionDiscoverListCommand
     *
     * @since   4.0.0
     */
    protected function createExtensionDiscoverListCommand(): ExtensionDiscoverListCommand
    {
        $db = $this->createMock(DatabaseInterface::class);
        return new ExtensionDiscoverListCommand($db);
    }
}
