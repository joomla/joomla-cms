<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log\Logger;

use Joomla\CMS\Log\Logger\FormattedtextLogger;
use Joomla\CMS\Log\Logger\W3cLogger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for W3cLogger.
 *
 * The logger extends FormattedtextLogger, which writes to disk in addEntry() and falls back to the
 * application configuration for its path, so only the constructor is exercised here and it is
 * always given an explicit path. addEntry() is never called, and the deferred mode is never turned
 * on because the destructor flushes it to disk.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The W3C logger')]
class W3cLoggerTest extends UnitTestCase
{
    /**
     * Builds a logger against the temporary directory.
     *
     * @param   array  $options  Extra options for the logger
     *
     * @return  array  The logger and the options array it was built with
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createLogger(array $options = []): array
    {
        $options = array_merge(['text_file_path' => sys_get_temp_dir()], $options);
        $logger  = new W3cLogger($options);

        return [$logger, $options];
    }

    /**
     * Tests that the log file is named after the logger when nothing else is asked for.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('names its file error.w3c.php by default')]
    public function testDefaultFileName(): void
    {
        [, $options] = $this->createLogger();

        $this->assertSame('error.w3c.php', $options['text_file']);
    }

    /**
     * Tests that a file name which was asked for is kept.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps a file name it was given')]
    public function testGivenFileNameIsKept(): void
    {
        [, $options] = $this->createLogger(['text_file' => 'custom.php']);

        $this->assertSame('custom.php', $options['text_file']);
    }

    /**
     * Tests the W3C entry format, which is the reason the class exists.
     *
     * It is tab separated and splits the timestamp into a date and a time, where the parent writes
     * a single DATETIME field.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('uses the tab separated W3C entry format')]
    public function testEntryFormat(): void
    {
        [$logger] = $this->createLogger();

        $format = (new \ReflectionProperty(W3cLogger::class, 'format'))->getValue($logger);

        $this->assertSame("{DATE}\t{TIME}\t{PRIORITY}\t{CLIENTIP}\t{CATEGORY}\t{MESSAGE}", $format);
    }

    /**
     * Tests that the fields are parsed out of the format.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('parses the fields out of its format')]
    public function testFieldsAreParsed(): void
    {
        [$logger] = $this->createLogger();

        $fields = (new \ReflectionProperty(FormattedtextLogger::class, 'fields'))->getValue($logger);

        $this->assertSame(['DATE', 'TIME', 'PRIORITY', 'CLIENTIP', 'CATEGORY', 'MESSAGE'], $fields);
    }

    /**
     * Tests that the path is built from the directory and the file name.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('builds its path from the directory and the file name')]
    public function testPathIsBuilt(): void
    {
        [$logger, $options] = $this->createLogger(['text_file' => 'custom.php']);

        $path = (new \ReflectionProperty(FormattedtextLogger::class, 'path'))->getValue($logger);

        $this->assertSame($options['text_file_path'] . '/custom.php', $path);
    }

    /**
     * Tests that building the logger does not touch the filesystem.
     *
     * The file is only created when the first entry is written, which is what makes it safe to
     * build the logger in a test.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('does not touch the filesystem until something is logged')]
    public function testConstructorDoesNotTouchTheFilesystem(): void
    {
        $file = sys_get_temp_dir() . '/w3c-unittest-' . uniqid() . '.php';

        $this->createLogger(['text_file' => basename($file)]);

        $this->assertFileDoesNotExist($file);
    }

    /**
     * Tests that a custom entry format given in the options wins.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('lets the options replace its entry format')]
    public function testEntryFormatCanBeReplaced(): void
    {
        [$logger] = $this->createLogger(['text_entry_format' => '{DATETIME}	{MESSAGE}']);

        $fields = (new \ReflectionProperty(FormattedtextLogger::class, 'fields'))->getValue($logger);

        $this->assertSame(['DATETIME', 'MESSAGE'], $fields);
    }
}
