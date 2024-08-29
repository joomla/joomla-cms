<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for \Joomla\CMS\Table\Table onSetNewTags event
 *
 * @since  4.0.0
 * @todo   Only used in JModelAdmin::batchTag since we can't use
 *         \Joomla\CMS\Table\Table::save as we don't want the data to be saved. Maybe trigger onBeforeStore?
 */
class SetNewTagsEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject      \Joomla\CMS\Table\TableInterface The table we are operating on
     * newTags      int[]                            New tags to be added to or replace current tags for an item
     * replaceTags  bool                             Replace tags (true) or add them (false)
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('newTags', $arguments)) {
            throw new \BadMethodCallException("Argument 'newTags' is required for event $name");
        }

        if (!\array_key_exists('replaceTags', $arguments)) {
            throw new \BadMethodCallException("Argument 'replaceTags' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the replaceTags attribute
     *
     * @param   mixed  $value  The value to set
     *
     * @return  boolean  Normalised value
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setReplaceTags($value)
    {
        return (bool) $value;
    }

    /**
     * Setter for the replaceTags attribute
     *
     * @param   mixed  $value  The value to set
     *
     * @return  boolean  Normalised value
     *
     * @since  4.4.0
     */
    protected function onSetReplaceTags($value)
    {
        return $this->setReplaceTags($value);
    }
}
