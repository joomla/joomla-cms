<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Factory;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher handling shared by the indexer adapters, which delegate their work to an Indexer.
 *
 * Declaring this on the adapter rather than inheriting CMSPlugin's dispatcher makes
 * PluginHelper::import() hand it the dispatcher its own listeners were registered on.
 *
 * @since  __DEPLOY_VERSION__
 */
trait AdapterDispatcherTrait
{
    use DispatcherAwareTrait {
        setDispatcher as traitSetDispatcher;
    }

    /**
     * Set the event dispatcher, keeping this adapter's indexer on the same one.
     *
     * @param   DispatcherInterface  $dispatcher  The dispatcher to use.
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        if ($this->indexer) {
            $this->indexer->setDispatcher($dispatcher);
        }

        return $this->traitSetDispatcher($dispatcher);
    }

    /**
     * Get the event dispatcher, falling back to the shared one when none was injected.
     *
     * @return  DispatcherInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getDispatcher()
    {
        if (!$this->dispatcher) {
            $this->setDispatcher(Factory::getContainer()->get(DispatcherInterface::class));
        }

        return $this->dispatcher;
    }

    /**
     * Get the event dispatcher for internal event dispatches.
     *
     * @return  DispatcherInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getEventDispatcher(): DispatcherInterface
    {
        if (!$this->dispatcher) {
            $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);

            if ($this->indexer) {
                $this->indexer->setDispatcher($dispatcher);
            }

            $this->traitSetDispatcher($dispatcher);
        }

        return $this->dispatcher;
    }
}
