<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event.
 * Example:
 *  new PrepareDataEvent('onEventName', ['context' => 'com_example.example', 'subject' => $data]);
 *
 * @since  __DEPLOY_VERSION__
 */
class PrepareDataEvent extends ModelEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject'];

    /**
     * Getter for the data.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData()
    {
        return $this->arguments['subject'];
    }
}
