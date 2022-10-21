<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Console
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Console\Loader;

use Joomla\CMS\Console\Loader\WritableContainerLoader;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

/**
 * Test class for Joomla\CMS\Console\Loader\WritableContainerLoader.
 *
 * @since   4.0.0
 */
class WritableContainerLoaderTest extends UnitTestCase
{
    /**
     * @var  ContainerInterface|MockObject
     *
     * @since   4.0.0
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     *
     * @return  void
     * @since   4.0.0
     */
    public function testTheLoaderCanBeWrittenTo()
    {
        $command = $this->createMock(AbstractCommand::class);

        $commandName = 'test:command';
        $serviceId   = 'test.loader';

        $this->container->expects($this->once())
            ->method('has')
            ->with($serviceId)
            ->willReturn(true);

        $loader = new WritableContainerLoader($this->container, []);
        $loader->add($commandName, $serviceId);

        $this->assertTrue($loader->has($commandName));
    }

    /**
     *
     * @return  void
     * @since   4.0.0
     */
    public function testTheLoaderRetrievesACommand()
    {
        $command = $this->createMock(AbstractCommand::class);

        $commandName = 'test:command';
        $serviceId   = 'test.loader';

        $this->container->expects($this->once())
            ->method('has')
            ->with($serviceId)
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with($serviceId)
            ->willReturn($command);

        $this->assertSame(
            $command,
            (new WritableContainerLoader($this->container, [$commandName => $serviceId]))->get($commandName)
        );
    }

    /**
     *
     * @return  void
     * @since   4.0.0
     */
    public function testTheLoaderDoesNotRetrieveAnUnknownCommand()
    {
        $commandName = 'test:loader';
        $serviceId   = 'test.loader';

        $this->expectException(\Symfony\Component\Console\Exception\CommandNotFoundException::class);

        $this->container->expects($this->once())
            ->method('has')
            ->with($serviceId)
            ->willReturn(false);

        $this->container->expects($this->never())
            ->method('get');

        (new WritableContainerLoader($this->container, [$commandName => $serviceId]))->get($commandName);
    }

    /**
     *
     * @return  void
     * @since   4.0.0
     */
    public function testTheLoaderHasACommand()
    {
        $commandName = 'test:loader';
        $serviceId   = 'test.loader';

        $this->container->expects($this->once())
            ->method('has')
            ->with($serviceId)
            ->willReturn(true);

        $this->assertTrue(
            (new WritableContainerLoader($this->container, [$commandName => $serviceId]))->has($commandName)
        );
    }
}
