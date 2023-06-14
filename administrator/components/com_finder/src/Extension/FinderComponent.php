<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Finder\Administrator\Service\HTML\Filter;
use Joomla\Component\Finder\Administrator\Service\HTML\Finder;
use Joomla\Component\Finder\Administrator\Service\HTML\Query;
use Joomla\Database\DatabaseInterface;
use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_finder
 *
 * @since  4.0.0
 */
class FinderComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   ContainerInterface  $container  The container
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function boot(ContainerInterface $container)
    {
        $finder = new Finder();
        $finder->setDatabase($container->get(DatabaseInterface::class));

        $this->getRegistry()->register('finder', $finder);

        $filter = new Filter();
        $filter->setDatabase($container->get(DatabaseInterface::class));

        $this->getRegistry()->register('filter', $filter);

        $this->getRegistry()->register('query', new Query());
    }
}
