<?php

/**
 * @package    Joomla.UnitTest
 * @subpackage Cache
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Cache\Storage;

use Joomla\CMS\Cache\Storage\FileStorage;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplicationInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test class for FileStorage.
 */
class FileStorageTest extends TestCase
{
    private $object;
    private $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $app = $this->createMock(CMSApplicationInterface::class);

        $app->method('get')->willReturnCallback(function ($key, $default = null) {
            if ($key === 'cachetime') {
                return 15;
            }
            if ($key === 'secret') {
                return 'test_secret_key_for_unit_tests';
            }
            return $default;
        });

        if (method_exists(Factory::class, 'setApplication')) {
            Factory::setApplication($app);
        } else {
            $reflection = new \ReflectionClass(Factory::class);
            $property = $reflection->getProperty('application');
            $property->setAccessible(true);
            $property->setValue(null, $app);
        }

        $this->cacheDir = sys_get_temp_dir() . '/joomla_test_cache_' . uniqid();
        mkdir($this->cacheDir, 0755, true);

        if (!defined('JPATH_CACHE')) {
            define('JPATH_CACHE', $this->cacheDir);
        }

        $this->object = new FileStorage(['cache_dir' => $this->cacheDir]);

        $reflection = new \ReflectionClass($this->object);
        $property = $reflection->getProperty('_root');
        $property->setAccessible(true);
        $property->setValue($this->object, $this->cacheDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->cacheDir . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }

        parent::tearDown();
    }

    public function testStoreUsesAtomicWriteAndCleansUpTempFiles()
    {
        $id    = 'test_atomic_write';
        $group = 'test_group';
        $data  = 'This is test cache data that should be written atomically.';

        $result = $this->object->store($id, $group, $data);

        $this->assertTrue($result, 'The store operation should return true on success.');

        $retrievedData = $this->object->get($id, $group);
        $this->assertEquals($data, $retrievedData, 'Retrieved cache data should match the stored data.');

        $files = glob($this->cacheDir . '/' . $group . '/*.tmp');

        $this->assertEmpty($files, 'No temporary .tmp files should be left behind after a successful store.');
    }
}
