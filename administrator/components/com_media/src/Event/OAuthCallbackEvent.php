<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\Input\Input;

/**
 * Event object to retrieve OAuthCallbacks.
 *
 * @since  4.0.0
 */
class OAuthCallbackEvent extends AbstractEvent
{
    /**
     * The event context.
     *
     * @var string
     *
     * @since  4.0.0
     */
    private $context = null;

    /**
     * The event input.
     *
     * @var    Input
     *
     * @since  4.0.0
     */
    private $input = null;

    /**
     * Get the event context.
     *
     * @return string
     *
     * @since  4.0.0
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the event context.
     *
     * @param   string  $context  Event context
     *
     * @return void
     *
     * @since  4.0.0
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Get the event input.
     *
     * @return  Input
     *
     * @since  4.0.0
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set the event input.
     *
     * @param   Input  $input  Event input
     *
     * @return void
     *
     * @since  4.0.0
     */
    public function setInput($input)
    {
        $this->input = $input;
    }
}
