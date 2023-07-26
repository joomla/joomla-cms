<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Content;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Content event
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ContentSaveEvent extends ContentEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'isNew', 'data'];

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject(object $value): object
    {
        return $value;
    }

    /**
     * Setter for the isNew argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setIsNew($value): bool
    {
        return (bool) $value;
    }

    /**
     * Getter for the item.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getItem(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the isNew state.
     *
     * @return  boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getIsNew(): bool
    {
        return $this->arguments['isNew'];
    }

    /**
     * Getter for the data.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData()
    {
        return $this->arguments['data'];
    }
}
