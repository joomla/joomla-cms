<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Adapter
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Filesystem\Local\Adapter;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for the Local filesystem adapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Local
 *
 * @testdox     The Local adapter
 *
 * @since       6.2.0
 */
class LocalAdapterTest extends UnitTestCase
{
    /**
     * Absolute path of the throw-away media root used by a single test.
     *
     * @var    string
     * @since  6.2.0
     */
    private $workDir;

    /**
     * Creates an isolated media root and the minimal global state that
     * MediaHelper::canUpload() reaches for (the application and the com_media
     * component parameters), so the adapter can run as a pure unit test.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->workDir = sys_get_temp_dir() . '/joomla-localadapter-' . uniqid();
        mkdir($this->workDir);

        // canUpload() calls Factory::getApplication() unconditionally.
        Factory::$application = $this->createStub(CMSApplicationInterface::class);

        // Seed com_media with empty params so ComponentHelper never queries the database.
        $record = new ComponentRecord(['option' => 'com_media', 'enabled' => 1]);
        $record->setParams(new Registry());

        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setValue(null, ['com_media' => $record]);
    }

    /**
     * Removes the temporary media root and resets the global state touched in setUp().
     *
     * @return  void
     *
     * @since   6.2.0
     */
    protected function tearDown(): void
    {
        foreach (glob($this->workDir . '/*') ?: [] as $file) {
            unlink($file);
        }

        if (is_dir($this->workDir)) {
            rmdir($this->workDir);
        }

        Factory::$application = null;

        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setValue(null, []);

        parent::tearDown();
    }

    /**
     * Returns the bytes of a tiny, valid single-colour JPEG so that the
     * MIME/type checks in canUpload() see a real image.
     *
     * @param   array  $rgb  The fill colour as [red, green, blue].
     *
     * @return  string
     *
     * @since   6.2.0
     */
    private function jpegBytes(array $rgb): string
    {
        $image = imagecreatetruecolor(4, 4);
        $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($image, 0, 0, 3, 3, $color);

        ob_start();
        imagejpeg($image);
        $bytes = ob_get_clean();


        return $bytes;
    }

    /**
     * @testdox  saves an edited file back to its original non-ASCII name
     *
     * This is the behaviour PR #47935 restores: a file whose on-disk name
     * contains non-ASCII characters (uploaded via FTP, sample data, a
     * migration, ...) can be re-saved by the image editor instead of being
     * rejected by canUpload().
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function testUpdateFileSavesBackToNonAsciiName()
    {
        $name = 'erik_schön.jpg';
        file_put_contents($this->workDir . '/' . $name, $this->jpegBytes([255, 0, 0]));

        $newContent = $this->jpegBytes([0, 0, 255]);

        $adapter = new LocalAdapter($this->workDir, 'test-media');
        $adapter->updateFile($name, '/', $newContent);

        $this->assertSame(
            $newContent,
            file_get_contents($this->workDir . '/' . $name),
            'The non-ASCII named file should be updated in place.'
        );
    }

    /**
     * @testdox  never touches a pre-existing file that already has the safe name
     *
     * Answers @brianteeman's review question: the "safe" name derived in
     * checkContent() is only handed to canUpload() as a validation string. It
     * is never used to name a file on disk (the probe file uses uniqid(), the
     * real write targets the original path), so a file that already exists at
     * File::makeSafe($name) is left completely untouched.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function testUpdateFileDoesNotTouchExistingSafeNamedNeighbour()
    {
        $name     = 'erik_schön.jpg';
        $safeName = File::makeSafe($name);

        // Guard the premise of the test: the two names really are different.
        $this->assertNotSame($name, $safeName);

        file_put_contents($this->workDir . '/' . $name, $this->jpegBytes([255, 0, 0]));

        // A pre-existing, unrelated file that happens to sit at the safe name.
        $neighbourContent = 'PRE-EXISTING-SAFE-NAME-CONTENT';
        file_put_contents($this->workDir . '/' . $safeName, $neighbourContent);

        $adapter = new LocalAdapter($this->workDir, 'test-media');
        $adapter->updateFile($name, '/', $this->jpegBytes([0, 0, 255]));

        // The neighbour is byte-for-byte unchanged.
        $this->assertSame(
            $neighbourContent,
            file_get_contents($this->workDir . '/' . $safeName),
            'The pre-existing safe-named file must not be overwritten.'
        );

        // Only the two original files remain: the uniqid() probe file was
        // cleaned up and no new safe-named file was created.
        $remaining = array_values(array_diff(scandir($this->workDir), ['.', '..']));
        sort($remaining);

        $expected = [$name, $safeName];
        sort($expected);

        $this->assertSame($expected, $remaining, 'No stray files should be left behind.');
    }
}
