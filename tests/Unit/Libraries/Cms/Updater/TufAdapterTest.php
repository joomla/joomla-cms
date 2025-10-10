<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Updater
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Updater;

use Joomla\CMS\Updater\Adapter\TufAdapter;
use Joomla\Tests\Unit\UnitTestCase;
use Joomla\Utilities\ArrayHelper;
use Tuf\Exception\MetadataException;

/**
 * Test class for Tuf Adapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Updater
 * @since       5.1.0
 */
class TufAdapterTest extends UnitTestCase
{
    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testProcessTufTargetThrowsExceptionIfHashesAreMissing()
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage("No trusted hashes are available for 'nohash.json'");

        $object = $this->getMockBuilder(TufAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->getPublicMethod($object, 'processTufTarget');
        $method->invoke($object, 'nohash.json', []);
    }

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testProcesstuftargetAssignsCustomTargetKeys()
    {
        $object = $this->getMockBuilder(TufAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->getPublicMethod($object, 'processTufTarget');
        $result = $method->invoke($object, 'targets.json', $this->getMockTarget([
            'custom' => [
                'name'    => 'Testupdate',
                'version' => '1.2.3',
            ],
        ]));

        $this->assertSame('Testupdate', $result['name']);
        $this->assertSame('1.2.3', $result['version']);
    }

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testProcesstuftargetAssignsClientId()
    {
        $object = $this->getMockBuilder(TufAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();


        $method = $this->getPublicMethod($object, 'processTufTarget');
        $result = $method->invoke($object, 'targets.json', $this->getMockTarget([
            'client' => 'site',
        ]));

        $this->assertSame(0, $result['client_id']);
    }

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testProcesstuftargetAssignsInfoUrl()
    {
        $object = $this->getMockBuilder(TufAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();


        $method = $this->getPublicMethod($object, 'processTufTarget');
        $result = $method->invoke($object, 'targets.json', $this->getMockTarget([
            'custom' => [
                'infourl' => [
                    'url' => 'https://example.org',
                ],
            ],
        ]));

        $this->assertSame('https://example.org', $result['infourl']);
    }

    /**
     * Internal helper method to get access to protected methods
     *
     * @since   5.1.0
     *
     * @param $object
     * @param $method
     *
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected function getPublicMethod($object, $method)
    {
        $reflectionClass = new \ReflectionClass($object);
        $method          = $reflectionClass->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Target override data
     *
     * @param array $overrides
     *
     * @return array
     *
     * @since  5.1.0
     */
    protected function getMockTarget(array $overrides)
    {
        return ArrayHelper::mergeRecursive(
            [
                'hashes' => [
                    'sha128' => '',
                ],
                'custom' => [
                    'name'           => 'Joomla',
                    'type'           => 'file',
                    'version'        => '1.2.3',
                    'targetplatform' => [
                        'name'    => 'joomla',
                        'version' => '(5\.[0-4])|^(4\.4)',
                    ],
                    'php_minimum'         => '8.1.0',
                    'channel'             => '5.x',
                    'stability'           => 'stable',
                    'supported_databases' => [
                        'mariadb' => '10.4',
                    ],
                ],
            ],
            $overrides
        );
    }
}
