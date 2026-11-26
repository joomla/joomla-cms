<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger\FormattedtextLogger;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for FormattedtextLogger
 *
 * @since  6.2.1
 */
class FormattedtextLoggerTest extends UnitTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/joomla-log-test-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            is_dir($file) ? rmdir($file) : unlink($file);
        }

        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    private function createLogger(array $extra = []): FormattedtextLogger
    {
        $options = $extra + ['text_file' => 'test.php', 'text_file_path' => $this->dir];

        return new FormattedtextLogger($options);
    }

    private function entry(string $message): LogEntry
    {
        return new LogEntry($message, Log::INFO, 'test');
    }

    public function testNewFileStartsWithHeaderAndFolderIsCreated(): void
    {
        $this->createLogger()->addEntry($this->entry('first'));

        $content = file_get_contents($this->dir . '/test.php');

        $this->assertStringStartsWith("#\n#<?php die('Forbidden.'); ?>\n", $content);
        $this->assertSame(1, substr_count($content, "die('Forbidden.')"));
        $this->assertStringContainsString('first', $content);
    }

    public function testEntriesAreAppendedWithoutSecondHeader(): void
    {
        $logger = $this->createLogger();
        $logger->addEntry($this->entry('first'));
        $logger->addEntry($this->entry('second'));

        $content = file_get_contents($this->dir . '/test.php');

        $this->assertSame(1, substr_count($content, "die('Forbidden.')"));
        $this->assertLessThan(strpos($content, 'second'), strpos($content, 'first'));
    }

    public function testDeletedFileIsRecreatedWithHeader(): void
    {
        $logger = $this->createLogger();
        $logger->addEntry($this->entry('first'));
        unlink($this->dir . '/test.php');
        $logger->addEntry($this->entry('<?php echo 1; ?>'));

        $content = file_get_contents($this->dir . '/test.php');

        $this->assertStringStartsWith("#\n#<?php die('Forbidden.'); ?>\n", $content);
    }

    public function testEmptyExistingFileGetsHeader(): void
    {
        mkdir($this->dir);
        touch($this->dir . '/test.php');

        $this->createLogger()->addEntry($this->entry('first'));

        $this->assertStringStartsWith("#\n#<?php die('Forbidden.'); ?>\n", file_get_contents($this->dir . '/test.php'));
    }

    public function testNoPhpOptionOmitsDieStatement(): void
    {
        $this->createLogger(['text_file_no_php' => true])->addEntry($this->entry('first'));

        $this->assertStringNotContainsString('die(', file_get_contents($this->dir . '/test.php'));
    }

    public function testDeferredEntriesAreWrittenOnDestruct(): void
    {
        $logger = $this->createLogger(['defer' => true]);
        $logger->addEntry($this->entry('deferred'));
        $this->assertFileDoesNotExist($this->dir . '/test.php');

        unset($logger);

        $content = file_get_contents($this->dir . '/test.php');
        $this->assertStringStartsWith("#\n#<?php die('Forbidden.'); ?>\n", $content);
        $this->assertStringContainsString('deferred', $content);
    }
}
