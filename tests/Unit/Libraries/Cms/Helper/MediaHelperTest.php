<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Helper
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for MediaHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Helper
 *
 * @since       6.2.1
 */
#[TestDox('The MediaHelper')]
class MediaHelperTest extends UnitTestCase
{
    /**
     * Resets the component parameters seeded by a test.
     *
     * @return  void
     *
     * @since   6.2.1
     */
    protected function tearDown(): void
    {
        $this->setMediaParams(null);

        parent::tearDown();
    }

    /**
     * File names that pass with the default com_media parameters.
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function allowedFileNameProvider(): array
    {
        return [
            'simple image'         => ['x.gif'],
            'upper case extension' => ['x.GIF'],
            'dots in the name'     => ['holiday.2026.gif'],
            'document'             => ['report.final.pdf'],
        ];
    }

    /**
     * File names that are refused with the default com_media parameters.
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function refusedFileNameProvider(): array
    {
        return [
            'no extension'             => ['image'],
            'empty extension'          => ['image.'],
            'executable extension'     => ['x.php'],
            'php before gif'           => ['x.php.gif'],
            'upper case php'           => ['x.PHP.gif'],
            'phtml before jpg'         => ['x.phtml.jpg'],
            'executable in the middle' => ['x.tar.php.png'],
            'blocked media executable' => ['x.js.png'],
            'not an allowed extension' => ['x.zip'],
        ];
    }

    /**
     * @param   string  $fileName  The file name
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[DataProvider('allowedFileNameProvider')]
    #[TestDox('accepts the file name $fileName')]
    public function testCheckFileNameAcceptsAllowedNames(string $fileName)
    {
        $this->setMediaParams(new Registry());

        $this->assertTrue(MediaHelper::checkFileName($fileName));
    }

    /**
     * @param   string  $fileName  The file name
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[DataProvider('refusedFileNameProvider')]
    #[TestDox('refuses the file name $fileName')]
    public function testCheckFileNameRefusesNames(string $fileName)
    {
        $this->setMediaParams(new Registry());

        $this->assertFalse(MediaHelper::checkFileName($fileName));
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('accepts a buried executable extension only when it is explicitly allowed')]
    public function testCheckFileNameHonoursAllowedExecutables()
    {
        $this->setMediaParams(new Registry());

        $this->assertFalse(MediaHelper::checkFileName('x.js.gif'));
        $this->assertTrue(MediaHelper::checkFileName('x.js.gif', 'com_media', ['js']));
        $this->assertFalse(MediaHelper::checkFileName('x.php.js.gif', 'com_media', ['js']));
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('uses the configured allowed and ignored extensions of the component')]
    public function testCheckFileNameUsesComponentParameters()
    {
        $this->setMediaParams(new Registry(['restrict_uploads_extensions' => 'gif', 'ignore_extensions' => 'foo']));

        $this->assertTrue(MediaHelper::checkFileName('x.gif'));
        $this->assertTrue(MediaHelper::checkFileName('x.foo'));
        $this->assertFalse(MediaHelper::checkFileName('x.jpg'));
        $this->assertFalse(MediaHelper::checkFileName('x.php.foo'));
    }

    /**
     * File names and their sanitized form with the default sanitize options.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function sanitizeFileNameProvider(): array
    {
        return [
            'spaces and capitals'  => ['My Holiday Photo 2025.JPG', 'my-holiday-photo-2025.jpg'],
            'underscores'          => ['my_holiday__photo.jpg', 'my-holiday-photo.jpg'],
            'special characters'   => ['report (final) & copy!.pdf', 'report-final-copy.pdf'],
            'repeated separators'  => ['  a -- b  .png', 'a-b.png'],
            'leading dot'          => ['.hidden.jpg', 'hidden.jpg'],
            'double dots'          => ['a..b.jpg', 'a.b.jpg'],
            'path separators'      => ['../etc/passwd.jpg', 'etcpasswd.jpg'],
            'cyrillic is kept'     => ['Запрос Фото.png', 'запрос-фото.png'],
            'arabic is kept'       => ['صورة جديدة.png', 'صورة-جديدة.png'],
            'accents are kept'     => ['Café Crème.jpg', 'café-crème.jpg'],
            'decomposed accents'   => ["Cafe\u{0301}.jpg", 'café.jpg'],
            'nothing usable left'  => ['!!!.jpg', null],
            'no extension'         => ['Read Me', 'read-me'],
            'extension is cleaned' => ['photo.J P G', 'photo.jpg'],
        ];
    }

    /**
     * @param   string   $fileName  The file name to sanitize
     * @param   ?string  $expected  The expected result, null for a generated fallback name
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('sanitizeFileNameProvider')]
    #[TestDox('sanitizes the file name "$fileName"')]
    public function testSanitizeFileName(string $fileName, ?string $expected)
    {
        $params = new Registry();
        $result = MediaHelper::sanitizeFileName($fileName, $params);

        if ($expected === null) {
            $this->assertMatchesRegularExpression('/^file-[0-9a-f]{8}\.jpg$/', $result);
        } else {
            $this->assertSame($expected, $result);
        }

        // Sanitizing a sanitized name doesn't change it, the adapter relies on that
        $this->assertSame($result, MediaHelper::sanitizeFileName($result, $params));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('uses the configured replacement character and lower case option')]
    public function testSanitizeFileNameHonoursOptions()
    {
        $this->assertSame('My_Photo.jpg', MediaHelper::sanitizeFileName('My Photo.JPG', new Registry(['sanitize_space_replacement' => '_', 'sanitize_lowercase' => 0])));
        $this->assertSame('my.photo.jpg', MediaHelper::sanitizeFileName('My Photo.jpg', new Registry(['sanitize_space_replacement' => '.'])));
        $this->assertSame('myphoto.jpg', MediaHelper::sanitizeFileName('My Photo.jpg', new Registry(['sanitize_space_replacement' => 'none'])));
        $this->assertSame('my-photo.jpg', MediaHelper::sanitizeFileName('My Photo.jpg', new Registry(['sanitize_space_replacement' => '/'])));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('converts the name to ASCII when transliteration is enabled')]
    public function testSanitizeFileNameTransliterates()
    {
        $params = new Registry(['sanitize_transliterate' => 1]);

        $this->assertSame('cafe-creme.jpg', MediaHelper::sanitizeFileName('Café Crème.jpg', $params));

        if (!\function_exists('transliterator_transliterate')) {
            $this->markTestIncomplete('The intl extension is needed to transliterate Cyrillic');
        }

        $this->assertSame('zapros.png', MediaHelper::sanitizeFileName('запрос.png', $params));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('is disabled unless the Media Manager option is set')]
    public function testFileNameSanitizingIsDisabledByDefault()
    {
        $this->assertFalse(MediaHelper::isFileNameSanitizingEnabled(new Registry()));
        $this->assertTrue(MediaHelper::isFileNameSanitizingEnabled(new Registry(['sanitize_filenames' => '1'])));
    }

    /**
     * An updated site has no stored value for the option, so the Media options form shows the default
     * from config.xml. That default must be off, otherwise saving the Media options would enable it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('is off in the Media options form when no value is stored, as on an updated site')]
    public function testFileNameSanitizingDefaultsToOffInTheOptionsForm()
    {
        $config = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_media/config.xml');
        $fields = $config->xpath('//field[@name="sanitize_filenames"]');

        $this->assertCount(1, $fields);
        $this->assertSame('0', (string) $fields[0]['default']);
    }

    /**
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function installationSqlProvider(): array
    {
        return [
            'MySQL'      => ['installation/sql/mysql/base.sql'],
            'PostgreSQL' => ['installation/sql/postgresql/base.sql'],
        ];
    }

    /**
     * @param   string  $file  The installation SQL file, relative to the root
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('installationSqlProvider')]
    #[TestDox('is enabled for a new installation in $file')]
    public function testFileNameSanitizingIsEnabledForNewInstallations(string $file)
    {
        $sql   = file_get_contents(JPATH_ROOT . '/' . $file);
        $found = preg_match("/'com_media', 'component', 'com_media',[^\\n]*?'(\\{[^\\n]*?\\})'/", $sql, $matches);

        $this->assertSame(1, $found, 'The com_media row was not found in ' . $file);

        $params = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('1', $params['sanitize_filenames'] ?? null);
    }

    /**
     * Seeds the com_media parameters so ComponentHelper never queries the database.
     *
     * @param   ?Registry  $params  The parameters, null resets the component cache
     *
     * @return  void
     *
     * @since   6.2.1
     */
    private function setMediaParams(?Registry $params): void
    {
        $components = new \ReflectionProperty(ComponentHelper::class, 'components');

        if ($params === null) {
            $components->setValue(null, []);

            return;
        }

        $record = new ComponentRecord(['option' => 'com_media', 'enabled' => 1]);
        $record->setParams($params);

        $components->setValue(null, ['com_media' => $record]);
    }
}
