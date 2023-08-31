<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Console
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Console;

use Joomla\CMS\Console\ExtensionDiscoverInstallCommand;
use Joomla\Database\DatabaseInterface;

/**
 * Test class for Joomla\CMS\Console\ExtensionDiscoverInstallCommand.
 *
 * @since   4.0.0
 */
class ExtensionDiscoverInstallCommandTest extends \PHPUnit\Framework\TestCase
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
        $this->assertInstanceOf(ExtensionDiscoverInstallCommand::class, $this->createExtensionDiscoverInstallCommand());
    }

    /**
     * Tests the processDiscover method
     * Ensure that the return value is an integer.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testProcessDiscoverReturnIsInt()
    {
        $command = $this->createMock(ExtensionDiscoverInstallCommand::class);

        $countOfDiscoveredExtensions1   = $command->processDiscover(-1);
        $countOfDiscoveredExtensions0   = $command->processDiscover(0);
        $countOfDiscoveredExtensions245 = $command->processDiscover(245);

        $this->assertIsInt($countOfDiscoveredExtensions1);
        $this->assertIsInt($countOfDiscoveredExtensions0);
        $this->assertIsInt($countOfDiscoveredExtensions245);
    }

    /**
     * Tests the getNote method
     * Ensure that the note is correct.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testGetNote()
    {
        $command = $this->createExtensionDiscoverInstallCommand();

        $note0 = $command->getNote(-1, 1);
        $note1 = $command->getNote(-1, -1);
        $note2 = $command->getNote(0, 1);
        $note3 = $command->getNote(1, 1);
        $note4 = $command->getNote(1, -1);
        $note5 = $command->getNote(2, -1);
        $note6 = $command->getNote(2, 1);

        $this->assertSame($note0, 'Unable to install the extension with ID 1');
        $this->assertSame($note1, 'Unable to install discovered extensions.');
        $this->assertSame($note2, 'There are no pending discovered extensions for install. Perhaps you need to run extension:discover first?');
        $this->assertSame($note3, 'Extension with ID 1 installed successfully.');
        $this->assertSame($note4, '1 discovered extension has been installed.');
        $this->assertSame($note5, '2 discovered extensions have been installed.');
        $this->assertSame($note6, 'The return value is not possible and has to be checked.');
    }

    /**
     * Helper function to create a ExtensionDiscoverInstallCommand
     *
     * @return  ExtensionDiscoverInstallCommand
     *
     * @since   4.0.0
     */
    protected function createExtensionDiscoverInstallCommand(): ExtensionDiscoverInstallCommand
    {
        $db = $this->createMock(DatabaseInterface::class);

        return new ExtensionDiscoverInstallCommand($db);
    }
}
