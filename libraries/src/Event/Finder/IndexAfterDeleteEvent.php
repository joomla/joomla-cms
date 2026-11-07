<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Finder;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event fired after Finder removes, or is asked to remove, an item from the index.
 * Example:
 *  new IndexAfterDeleteEvent('onEventName', ['linkId' => $linkId]);
 *
 * The indexer passes the removed finder link id, the adapters the source item id when it had no link.
 *
 * @since  __DEPLOY_VERSION__
 */
class IndexAfterDeleteEvent extends AbstractImmutableEvent implements FinderEventInterface
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        // Check the values, not the keys: a null id is as useless to a listener as a missing one.
        if ($this->getLinkId() === null && $this->getItemId() === null) {
            throw new \BadMethodCallException(
                "Event {$name} requires a non-null 'linkId' or 'itemId' argument but neither has been provided"
            );
        }
    }

    /**
     * Setter for the linkId argument.
     *
     * @param   ?integer  $value  The value to set
     *
     * @return  ?integer
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetLinkId(?int $value): ?int
    {
        return $value;
    }

    /**
     * Setter for the itemId argument.
     *
     * @param   ?integer  $value  The value to set
     *
     * @return  ?integer
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetItemId(?int $value): ?int
    {
        return $value;
    }

    /**
     * Getter for the id of the removed finder link, if one existed.
     *
     * @return  ?integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getLinkId(): ?int
    {
        return $this->arguments['linkId'] ?? null;
    }

    /**
     * Getter for the id of the source item that had no finder link to remove.
     *
     * @return  ?integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getItemId(): ?int
    {
        return $this->arguments['itemId'] ?? null;
    }
}
