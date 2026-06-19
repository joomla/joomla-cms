<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a common interface to send emails with HTML.
 *
 * @since  __DEPLOY_VERSION__
 */
interface FormatConfigurableMailerInterface
{
    /**
    * Sets message type to HTML.
    *
    * @param   boolean  $ishtml  Boolean true or false.
    *
    * @return  FormatConfigurableMailerInterface  Returns this object for chaining.
    *
    * @since   __DEPLOY_VERSION__
    */
    public function isHtml($ishtml = true);
}
