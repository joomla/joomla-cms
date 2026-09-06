<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Application;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event fired while building a link into the administrator backend of the site.
 * Example:
 *  new BuildAdministratorLoginUrlEvent('onEventName', ['subject' => $uri]);
 *
 * Security solutions which guard the administrator login with a secret query parameter add that
 * parameter here, either by modifying the Uri in place or by calling updateUri() with a new one.
 *
 * @since  __DEPLOY_VERSION__
 */
class BuildAdministratorLoginUrlEvent extends AbstractImmutableEvent
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
    }

    /**
     * Setter for the subject argument.
     *
     * @param   Uri  $value  The value to set
     *
     * @return  Uri
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetSubject(Uri $value): Uri
    {
        return $value;
    }

    /**
     * Getter for the administrator login uri.
     *
     * @return  Uri
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getUri(): Uri
    {
        return $this->arguments['subject'];
    }

    /**
     * Replace the administrator login uri.
     *
     * @param   Uri  $value  The value to set
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function updateUri(Uri $value): static
    {
        $this->arguments['subject'] = $this->onSetSubject($value);

        return $this;
    }
}
