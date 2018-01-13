<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('_JEXEC') or die;

/**
 * Interface for creating mailer objects
 *
 * @since  __DEPLOY_VERSION__
 */
interface MailerFactoryInterface
{
	/**
	 * Creates a new mailer object.
	 *
	 * @return  MailerInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createMailer(): MailerInterface;
}
