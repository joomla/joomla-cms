<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Filter;

use Joomla\CMS\Filter\InputFilter;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Filter\InputFilter.
 *
 * @since  5.4.10
 */
class InputFilterTest extends UnitTestCase
{
    /**
     * Files created during a test that need to be removed afterwards.
     *
     * @var    string[]
     * @since  5.4.10
     */
    private $tempFiles = [];

    /**
     * Remove any temporary files created during the test.
     *
     * @return  void
     *
     * @since   5.4.10
     */
    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->tempFiles = [];

        parent::tearDown();
    }

    /**
     * Write the given content to a temporary file and return an uploaded-file descriptor for it.
     *
     * @param   string  $content  The file content to write.
     *
     * @return  array
     *
     * @since   5.4.10
     */
    private function createUploadedFile(string $content): array
    {
        $path = tempnam(sys_get_temp_dir(), 'jinputfilter');
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return [
            'name'     => 'archive.zip',
            'type'     => 'application/zip',
            'tmp_name' => $path,
            'error'    => 0,
            'size'     => \strlen($content),
        ];
    }

    /**
     * Data provider for testIsSafeFileDetectsPharStub.
     *
     * @return  array
     *
     * @since   5.4.10
     */
    public function dataPharStub(): array
    {
        // The content scanner reads the file in 131072-byte chunks.
        $chunk = 131072;
        $stub  = '__HALT_COMPILER()';

        return [
            'no stub, clean file' => [
                true,
                str_repeat('A', $chunk + 100),
            ],
            'stub within a single chunk' => [
                false,
                str_repeat('A', 1000) . $stub . str_repeat('B', 50),
            ],
            'stub straddling a read boundary' => [
                false,
                str_repeat('A', $chunk - 12) . $stub . str_repeat('B', 100),
            ],
        ];
    }

    /**
     * Tests that the phar stub is detected even when it is split across a read boundary.
     *
     * @param   boolean  $expected  The expected result of isSafeFile().
     * @param   string   $content   The file content to scan.
     *
     * @return  void
     *
     * @since   5.4.10
     * @dataProvider dataPharStub
     */
    public function testIsSafeFileDetectsPharStub(bool $expected, string $content): void
    {
        $this->assertSame($expected, InputFilter::isSafeFile($this->createUploadedFile($content)));
    }
}
