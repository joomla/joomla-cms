<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('_JEXEC') or die;

/**
 * Interface defining a mailer service
 *
 * @since  __DEPLOY_VERSION__
 */
interface MailerInterface
{
	/**
	 * Creates a new mail message.
	 *
	 * @return  MailMessageInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createMessage(): MailMessageInterface;
}
