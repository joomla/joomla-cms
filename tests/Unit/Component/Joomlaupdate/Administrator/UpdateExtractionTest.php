<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Joomlaupdate
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Joomlaupdate\Administrator;

use Joomla\Tests\Unit\UnitTestCase;
use ZipArchive;

/**
 * Test class for the Joomla Update ZIP extraction script.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Joomlaupdate
 * @since       __DEPLOY_VERSION__
 */
class UpdateExtractionTest extends UnitTestCase
{
    /**
     * @testdox  Test that the update extractor rejects archive entries outside the destination
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExtractorRejectsArchiveEntriesOutsideDestination(): void
    {
        $this->requireZipArchive();
        $this->loadExtractor();

        $base = $this->createTemporaryDirectory();
        $this->createZip($base . '/update.zip', [
            '../outside.txt' => 'outside',
        ]);

        $error = $this->extractZip($base . '/update.zip', $base . '/root');

        $this->assertSame('Archive entry ../outside.txt is not safe to extract.', $error);
        $this->assertFileDoesNotExist($base . '/outside.txt');
        $this->assertFileDoesNotExist($base . '/root/outside.txt');

        $this->removeTemporaryDirectory($base);
    }

    /**
     * @testdox  Test that the update extractor still extracts regular archive entries
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExtractorStillExtractsRegularEntries(): void
    {
        $this->requireZipArchive();
        $this->loadExtractor();

        $base = $this->createTemporaryDirectory();
        $this->createZip($base . '/update.zip', [
            'administrator/components/com_example/example.php' => '<?php echo "ok";',
        ]);

        $error = $this->extractZip($base . '/update.zip', $base . '/root');

        $this->assertNull($error);
        $this->assertFileExists($base . '/root/administrator/components/com_example/example.php');
        $this->assertSame('<?php echo "ok";', file_get_contents($base . '/root/administrator/components/com_example/example.php'));

        $this->removeTemporaryDirectory($base);
    }

    /**
     * @testdox  Test that the update extractor rejects symlink targets outside the destination
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExtractorRejectsSymlinkTargetsOutsideDestination(): void
    {
        $this->loadExtractor();

        $base = $this->createTemporaryDirectory();
        $this->createSymlinkZip($base . '/update.zip', 'link', '..');

        $error = $this->extractZip($base . '/update.zip', $base . '/root');

        $this->assertStringContainsString('points outside the extraction root', $error);
        $this->assertFileDoesNotExist($base . '/root/link');

        $this->removeTemporaryDirectory($base);
    }

    /**
     * Loads the standalone update extractor class without executing request handling code.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadExtractor(): void
    {
        if (class_exists('\\ZIPExtraction', false)) {
            return;
        }

        $source = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/extract.php');
        $source = substr($source, 0, strpos($source, '// Import configuration'));
        $source = preg_replace('#^<\?php\s*#', '', $source);

        eval("namespace {\n" . $source . "\n}");
    }

    /**
     * Creates a ZIP archive with the given entries.
     *
     * @param   string  $filename  The ZIP filename
     * @param   array   $entries   The archive entries
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createZip(string $filename, array $entries): void
    {
        $zip = new \ZipArchive();

        $this->assertTrue($zip->open($filename, \ZipArchive::CREATE));

        foreach ($entries as $name => $contents) {
            $this->assertTrue($zip->addFromString($name, $contents));
        }

        $this->assertTrue($zip->close());
    }

    /**
     * Creates a minimal ZIP archive containing a symlink entry.
     *
     * @param   string  $filename  The ZIP filename
     * @param   string  $linkName  The symlink name
     * @param   string  $target    The symlink target
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createSymlinkZip(string $filename, string $linkName, string $target): void
    {
        $header = pack(
            'VCCvvvvVVVvv',
            0x04034b50,
            10,
            3,
            0,
            0,
            0,
            0,
            0,
            \strlen($target),
            \strlen($target),
            \strlen($linkName),
            0
        );

        $centralDirectoryHeader = pack('V', 0x02014b50) . str_repeat("\0", 26);

        file_put_contents($filename, $header . $linkName . $target . $centralDirectoryHeader);
    }

    /**
     * Extracts a ZIP file using the update extractor.
     *
     * @param   string  $filename     The ZIP filename
     * @param   string  $destination  The extraction destination
     *
     * @return  string|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function extractZip(string $filename, string $destination): ?string
    {
        mkdir($destination, 0777, true);

        $engine = new \ZIPExtraction();
        $engine->setFilename($filename);
        $engine->setAddPath($destination);

        do {
            $done  = $engine->step();
            $error = $engine->getError();
        } while (!$done && $error === null);

        return $error;
    }

    /**
     * Creates a temporary directory.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createTemporaryDirectory(): string
    {
        $base = sys_get_temp_dir() . '/joomla-update-extraction-' . bin2hex(random_bytes(8));

        mkdir($base, 0777, true);

        return $base;
    }

    /**
     * Removes a temporary directory.
     *
     * @param   string  $directory  The directory to remove
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function removeTemporaryDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($directory);
    }

    /**
     * Skips the test when ZipArchive is unavailable.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function requireZipArchive(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('This test requires the ZipArchive class.');
        }
    }
}
