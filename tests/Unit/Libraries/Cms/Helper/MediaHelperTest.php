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
