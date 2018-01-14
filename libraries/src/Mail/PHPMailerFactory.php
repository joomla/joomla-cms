<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a mailer supporting PHPMailer.
 *
 * @since  __DEPLOY_VERSION__
 */
class PHPMailerFactory implements MailerFactoryInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Creates a new mailer object.
	 *
	 * @return  MailerInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createMailer(): MailerInterface
	{
		$mailer = new Mailer(Factory::getConfig());
		$mailer->setLogger($this->getContainer()->get(LoggerInterface::class));

		return $mailer;
	}
}
