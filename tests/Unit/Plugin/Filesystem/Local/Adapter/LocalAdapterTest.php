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
use Joomla\CMS\Language\Language;
use Joomla\CMS\User\User;
use Joomla\Filesystem\File;
use Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for the Local filesystem adapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Local
 *
 * @since       6.2.0
 */
#[TestDox('The Local adapter')]
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

        // Text::_() resolves the language through the Factory; return the key untranslated.
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnArgument(0);
        (new \ReflectionProperty(Factory::class, 'language'))->setValue(null, $language);

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
        if (is_dir($this->workDir)) {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->workDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($items as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }

            rmdir($this->workDir);
        }

        Factory::$application = null;
        (new \ReflectionProperty(Factory::class, 'language'))->setValue(null, null);

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
     * This is the behaviour PR #47935 restores: a file whose on-disk name
     * contains non-ASCII characters (uploaded via FTP, sample data, a
     * migration, ...) can be re-saved by the image editor instead of being
     * rejected by canUpload().
     *
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('saves an edited file back to its original non-ASCII name')]
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
    #[TestDox('never touches a pre-existing file that already has the safe name')]
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

    /**
     * Destination names that must be refused, because a blocked executable
     * extension is buried in a non-terminal segment.
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function buriedExecutableProvider(): array
    {
        return [
            'php before gif'   => ['x.php.gif'],
            'upper case php'   => ['x.PHP.gif'],
            'phtml before jpg' => ['x.phtml.jpg'],
            'several segments' => ['x.tar.php.png'],
        ];
    }

    /**
     * A rename (move) must not produce a file name that the upload path refuses.
     *
     * @param   string  $destination  The destination file name
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[DataProvider('buriedExecutableProvider')]
    #[TestDox('refuses to move a file to a name with a buried executable extension ($destination)')]
    public function testMoveRejectsBuriedExecutableExtension(string $destination)
    {
        file_put_contents($this->workDir . '/x.gif', 'inert');

        $adapter = new LocalAdapter($this->workDir, 'test-media');

        try {
            $adapter->move('/x.gif', '/' . $destination);
            $this->fail('The move should have been refused.');
        } catch (\Exception $e) {
            $this->assertSame('COM_MEDIA_MOVE_FILE_EXTENSION_INVALID', $e->getMessage());
        }

        $this->assertFileExists($this->workDir . '/x.gif', 'The source file must be left in place.');
        $this->assertSame(['x.gif'], array_values(array_diff(scandir($this->workDir), ['.', '..'])));
    }

    /**
     * A copy must not produce a file name that the upload path refuses.
     *
     * @param   string  $destination  The destination file name
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[DataProvider('buriedExecutableProvider')]
    #[TestDox('refuses to copy a file to a name with a buried executable extension ($destination)')]
    public function testCopyRejectsBuriedExecutableExtension(string $destination)
    {
        file_put_contents($this->workDir . '/x.gif', 'inert');

        $adapter = new LocalAdapter($this->workDir, 'test-media');

        try {
            $adapter->copy('/x.gif', '/' . $destination);
            $this->fail('The copy should have been refused.');
        } catch (\Exception $e) {
            $this->assertSame('COM_MEDIA_MOVE_FILE_EXTENSION_INVALID', $e->getMessage());
        }

        $this->assertSame(['x.gif'], array_values(array_diff(scandir($this->workDir), ['.', '..'])));
    }

    /**
     * Ordinary names, including dots that are not executable extensions, keep working.
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('still moves and copies files with ordinary multi-dot names')]
    public function testMoveAndCopyAllowOrdinaryNames()
    {
        file_put_contents($this->workDir . '/x.gif', 'inert');

        $adapter = new LocalAdapter($this->workDir, 'test-media');

        $adapter->move('/x.gif', '/holiday.2026.gif');
        $this->assertFileExists($this->workDir . '/holiday.2026.gif');
        $this->assertFileDoesNotExist($this->workDir . '/x.gif');

        $adapter->copy('/holiday.2026.gif', '/holiday.copy.gif');
        $this->assertFileExists($this->workDir . '/holiday.2026.gif');
        $this->assertFileExists($this->workDir . '/holiday.copy.gif');
    }

    /**
     * Creates a media root below the work directory, with one file inside and one file next to it.
     *
     * @return  LocalAdapter
     *
     * @since   6.2.1
     */
    private function adapterWithOutsideFile(): LocalAdapter
    {
        mkdir($this->workDir . '/media/sub', 0777, true);
        file_put_contents($this->workDir . '/media/sub/inside-marker.txt', 'inert');
        file_put_contents($this->workDir . '/outside-marker.txt', 'inert');

        $adapter = new LocalAdapter($this->workDir . '/media', 'test-media');

        // The result entries carry dates, which read the language tag and the time zone of the current user
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnArgument(0);
        $language->method('getTag')->willReturn('en-GB');
        (new \ReflectionProperty(Factory::class, 'language'))->setValue(null, $language);

        $adapter->setCurrentUser(new User());

        return $adapter;
    }

    /**
     * A path segment with glob characters must not expand to "." and "..", so the search cannot leave the root.
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('matches the search path literally instead of as a glob pattern')]
    public function testSearchDoesNotExpandThePath()
    {
        $adapter = $this->adapterWithOutsideFile();

        $this->assertSame([], $adapter->search('/.*', 'marker'));
        $this->assertSame([], $adapter->search('/.*', 'marker', true));
        $this->assertSame([], $adapter->search('/sub/.*/.*', 'marker'));
    }

    /**
     * The normal search, flat and recursive, keeps working.
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('still finds files in the root and in subfolders')]
    public function testSearchFindsFilesInsideTheRoot()
    {
        $adapter = $this->adapterWithOutsideFile();

        mkdir($this->workDir . '/media/[x]');
        file_put_contents($this->workDir . '/media/[x]/bracket-marker.txt', 'inert');

        $this->assertSame([], $adapter->search('/', 'marker'));
        $this->assertSame(['/sub/inside-marker.txt'], array_column($adapter->search('/sub', 'marker'), 'path'));
        $this->assertSame(['/[x]/bracket-marker.txt'], array_column($adapter->search('/[x]', 'marker'), 'path'));

        $found = array_column($adapter->search('/', 'marker', true), 'path');
        sort($found);

        $this->assertSame(['/[x]/bracket-marker.txt', '/sub/inside-marker.txt'], $found);
    }
}
