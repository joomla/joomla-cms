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
 * @since    __DEPLOY_VERSION__
 */
class InputFilterTest extends UnitTestCase
{
    /**
     * The size of a single read performed by InputFilter::isSafeFile().
     *
     * @var integer
     */
    private const READ_CHUNK = 131072;

    /**
     * Temporary files created during the tests, removed on tear down.
     *
     * @var string[]
     */
    private $tmpFiles = [];

    /**
     * Remove any temporary files created during a test.
     *
     * @return  void
     */
    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $this->tmpFiles = [];

        parent::tearDown();
    }

    /**
     * Write the given content to a temporary file and return a file descriptor
     * array in the shape expected by InputFilter::isSafeFile().
     *
     * @param   string  $content  The raw file content.
     * @param   string  $name     The reported (intended) file name.
     *
     * @return  array
     */
    private function descriptorFor(string $content, string $name): array
    {
        $path = tempnam(sys_get_temp_dir(), 'jfilter');
        file_put_contents($path, $content);
        $this->tmpFiles[] = $path;

        return [
            'name'     => $name,
            'type'     => 'application/octet-stream',
            'tmp_name' => $path,
            'error'    => 0,
            'size'     => \strlen($content),
        ];
    }

    /**
     * A dangerous signature that straddles a read boundary must still be
     * detected. Here the 17-byte "__HALT_COMPILER()" phar stub is positioned so
     * that 11 of its bytes fall before the boundary, which previously defeated
     * the (too small) overlap that is carried over between reads.
     *
     * @return  void
     */
    public function testIsSafeFileDetectsPharStubAcrossReadBoundary()
    {
        $stub    = '__HALT_COMPILER()';
        $content = str_repeat('A', self::READ_CHUNK - 11) . $stub . str_repeat('B', 50000);

        $this->assertFalse(
            InputFilter::isSafeFile($this->descriptorFor($content, 'evil.jpg')),
            'A phar stub spanning the read boundary must be detected as unsafe'
        );
    }

    /**
     * The same detection must hold at a later read boundary, not just the first.
     *
     * @return  void
     */
    public function testIsSafeFileDetectsPharStubAcrossSecondReadBoundary()
    {
        $stub    = '__HALT_COMPILER()';
        $content = str_repeat('A', (2 * self::READ_CHUNK) - 11) . $stub . str_repeat('B', 10000);

        $this->assertFalse(
            InputFilter::isSafeFile($this->descriptorFor($content, 'evil.jpg')),
            'A phar stub spanning a later read boundary must also be detected as unsafe'
        );
    }

    /**
     * A PHP open tag spanning the read boundary must still be detected.
     *
     * @return  void
     */
    public function testIsSafeFileDetectsPhpTagAcrossReadBoundary()
    {
        $content = str_repeat('A', self::READ_CHUNK - 3) . '<?php echo 1;' . str_repeat('B', 1000);

        $this->assertFalse(
            InputFilter::isSafeFile($this->descriptorFor($content, 'evil.png')),
            'A <?php tag spanning the read boundary must be detected as unsafe'
        );
    }

    /**
     * A large, benign file that contains none of the scanned signatures must
     * not be falsely reported as unsafe.
     *
     * @return  void
     */
    public function testIsSafeFileAllowsLargeBenignFile()
    {
        $content = str_repeat("\x89PNG\r\n\x1a\n", 60000);

        $this->assertTrue(
            InputFilter::isSafeFile($this->descriptorFor($content, 'image.png')),
            'A large file without any dangerous signature must be considered safe'
        );
    }
}
