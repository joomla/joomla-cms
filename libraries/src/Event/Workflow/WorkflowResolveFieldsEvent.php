<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Workflow;

use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Asks plugins for the value of one check across a set of items.
 *
 * Batched, so a plugin can answer for every item with one query. A moment check returns the
 * same value for every id, computed at the evaluation time rather than now.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowResolveFieldsEvent extends Event
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  Must contain 'field', 'itemIds', 'extension' and may contain
     *                              'evaluationTime'.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        $arguments['values']   = [];
        $arguments['answered'] = false;

        parent::__construct($name, $arguments);
    }

    /**
     * The check being resolved, as named by addField().
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getField(): string
    {
        return (string) ($this->arguments['field'] ?? '');
    }

    /**
     * The items to resolve it for.
     *
     * @return  int[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItemIds(): array
    {
        return (array) ($this->arguments['itemIds'] ?? []);
    }

    /**
     * The workflow extension, e.g. com_content.article.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getExtension(): string
    {
        return (string) ($this->arguments['extension'] ?? '');
    }

    /**
     * The moment to describe, for checks that depend on the clock. Null means now.
     *
     * @return  \DateTime|null
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getEvaluationTime(): ?\DateTime
    {
        return $this->arguments['evaluationTime'] ?? null;
    }

    /**
     * Supplies the resolved values, and claims the check.
     *
     * Call this even with an empty array when the check is yours, so it counts as claimed. Leave
     * out any item you cannot answer for rather than guessing, and no rule will fire on it.
     *
     * @param   array  $values  Item id to value. A value may be a scalar or a list, depending
     *                          on the check.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setValues(array $values): void
    {
        $this->arguments['values']   = $values + $this->arguments['values'];
        $this->arguments['answered'] = true;

        $this->stopPropagation();
    }

    /**
     * Whether any plugin claimed this check, regardless of how many items it answered for.
     *
     * An owner that answered for no items is not the same as no owner at all. No owner means the
     * extension that provided the check is gone.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isAnswered(): bool
    {
        return $this->arguments['answered'] === true;
    }

    /**
     * The resolved values, keyed by item id.
     *
     * @return  array<int, mixed>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getValues(): array
    {
        return $this->arguments['values'];
    }
}
