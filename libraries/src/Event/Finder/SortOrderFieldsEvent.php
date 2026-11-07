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
 * Event fired before Finder renders the available sort order fields.
 * Example:
 *  new SortOrderFieldsEvent('onEventName', ['sortOrderFields' => $sortOrderFields]);
 *
 * Listeners which want to change the offered sort order fields must call updateSortOrderFields().
 *
 * @since  __DEPLOY_VERSION__
 */
class SortOrderFieldsEvent extends AbstractImmutableEvent implements FinderEventInterface
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

        if (!\array_key_exists('sortOrderFields', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'sortOrderFields' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the sortOrderFields argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetSortOrderFields(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the sort order fields.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSortOrderFields(): array
    {
        return $this->arguments['sortOrderFields'];
    }

    /**
     * Update the sort order fields.
     *
     * @param   array  $value  The value to set
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function updateSortOrderFields(array $value): static
    {
        $this->arguments['sortOrderFields'] = $this->onSetSortOrderFields($value);

        return $this;
    }
}
