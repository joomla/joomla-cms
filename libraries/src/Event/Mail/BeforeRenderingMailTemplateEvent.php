<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Mail;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for MailTemplate events
 * Example:
 *   new BeforeRenderingMailTemplateEvent('onEventName', ['templateId' => 'com_example.template', 'subject' => $mailTemplateInstance]);
 *
 * @since  5.2.0
 */
class BeforeRenderingMailTemplateEvent extends MailTemplateEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.2.0
     * @deprecated 5.2.0 will be removed in 7.0
     */
    protected $legacyArgumentsOrder = ['templateId', 'subject'];
}
