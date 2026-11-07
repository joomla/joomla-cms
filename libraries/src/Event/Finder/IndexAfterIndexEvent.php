<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Finder;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\Component\Finder\Administrator\Indexer\Result;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event fired after Finder indexes an item.
 * Example:
 *  new IndexAfterIndexEvent('onEventName', ['subject' => $item, 'linkId' => $linkId]);
 *
 * @since  __DEPLOY_VERSION__
 */
class IndexAfterIndexEvent extends AbstractImmutableEvent implements FinderEventInterface
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

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('linkId', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'linkId' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   Result  $value  The value to set
     *
     * @return  Result
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetSubject(Result $value): Result
    {
        return $value;
    }

    /**
     * Setter for the linkId argument.
     *
     * @param   integer  $value  The value to set
     *
     * @return  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetLinkId(int $value): int
    {
        return $value;
    }

    /**
     * Getter for the indexed item.
     *
     * @return  Result
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getItem(): Result
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the id of the created or updated finder link.
     *
     * @return  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getLinkId(): int
    {
        return $this->arguments['linkId'];
    }
}
