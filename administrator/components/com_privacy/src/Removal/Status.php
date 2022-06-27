<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Removal;

/**
 * Data object communicating the status of whether the data for an information request can be removed.
 *
 * Typically, this object will only be used to communicate data will be removed.
 *
 * @since  3.9.0
 */
class Status
{
    /**
     * Flag indicating the status reported by the plugin on whether the information can be removed
     *
     * @var    boolean
     * @since  3.9.0
     */
    public $canRemove = true;

    /**
     * A status message indicating the reason data can or cannot be removed
     *
     * @var    string
     * @since  3.9.0
     */
    public $reason;
}
