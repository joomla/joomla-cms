<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Content;

use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Content event
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentPrepareEvent extends AbstractContentEvent
{
    /**
     * The argument names (mandatory AND optional). In order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'params', 'page'];

    /**
     * Setter for the params argument.
     *
     * @param   Registry  $value  The value to set
     *
     * @return  Registry
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setParams(Registry $value): Registry
    {
        return $value;
    }

    /**
     * Setter for the page argument.
     *
     * @param   ?int  $value  The value to set
     *
     * @return  ?int
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setPage(?int $value): ?int
    {
        return $value;
    }

    /**
     * Getter for the item argument.
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
     * Getter for the item argument.
     *
     * @return  Registry
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getParams(): Registry
    {
        return $this->arguments['params'];
    }

    /**
     * Getter for the page argument.
     *
     * @return  ?int
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getPage(): ?int
    {
        return $this->arguments['page'];
    }
}
