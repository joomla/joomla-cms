<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Console
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Console;

use Joomla\CMS\Console\ExtensionDiscoverCommand;

/**
 * Test class for Joomla\CMS\Console\ExtensionDiscoverCommand.
 *
 * @since   4.0.0
 */
class ExtensionDiscoverCommandTest extends \PHPUnit\Framework\TestCase
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
        $this->assertInstanceOf(ExtensionDiscoverCommand::class, $this->createExtensionDiscoverCommand());
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
        $command = $this->createMock(ExtensionDiscoverCommand::class);

        $countOfDiscoveredExtensions = $command->processDiscover();

        $this->assertIsInt($countOfDiscoveredExtensions);
    }

    /**
     * Tests the getNote method
     * Ensure that the note is correct.
     *
     * @param   int  $count   Number of extensions to discover
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testGetNote()
    {
        $command = $this->createExtensionDiscoverCommand();

        $note0 = $command->getNote(0);
        $note1 = $command->getNote(1);
        $note2 = $command->getNote(2);

        $this->assertSame($note0, 'No extensions were discovered.');
        $this->assertSame($note1, '1 extension has been discovered.');
        $this->assertSame($note2, '2 extensions have been discovered.');
    }

    /**
     * Helper function to create a ExtensionDiscoverCommand
     *
     * @return  ExtensionDiscoverCommand
     *
     * @since   4.0.0
     */
    protected function createExtensionDiscoverCommand(): ExtensionDiscoverCommand
    {
        return new ExtensionDiscoverCommand();
    }
}
