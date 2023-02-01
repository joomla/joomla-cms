<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Application;

use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Language\Language;
use Joomla\DI\Container;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleApplicationTest extends UnitTestCase
{
    /**
     * @testdox  Test that the ConsoleApplication can init and has the right name
     *
     * @return void
     * @since   4.0.0
     */
    public function testAppInitialisesByName()
    {
        $app = $this->createApplication();

        $this->assertSame('cli', $app->getName());
        $this->assertSame(true, $app->isClient('cli'));
    }

    /**
     * Helper function to create a ConsoleApplication with mocked dependencies
     *
     * @return ConsoleApplication
     *
     * @since   4.0.0
     */
    protected function createApplication(): ConsoleApplication
    {
        $config     = $this->createMock(Registry::class);
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $container  = $this->createMock(Container::class);
        $language   = $this->createMock(Language::class);
        $input      = $this->createMock(InputInterface::class);
        $output     = $this->createMock(OutputInterface::class);

        $object = new ConsoleApplication($config, $dispatcher, $container, $language, $input, $output);

        return $object;
    }
}
