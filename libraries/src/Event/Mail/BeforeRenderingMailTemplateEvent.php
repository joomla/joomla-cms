<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Mail;

/**
 * Class for MailTemplate events
 * Example:
 *   new BeforeRenderingMailTemplateEvent('onEventName', ['templateId' => 'com_example.template', 'subject' => $mailTemplateInstance]);
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeRenderingMailTemplateEvent extends MailTemplateEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated __DEPLOY_VERSION__ will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['templateId', 'subject'];
}
