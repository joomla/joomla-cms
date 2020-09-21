<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for performing session metadata garbage collection
 *
 * @since  4.0.0
 */
class SessionMetadataGcCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'session:metadata:gc';

	/**
	 * The session metadata manager.
	 *
	 * @var    MetadataManager
	 * @since  4.0.0
	 */
	private $metadataManager;

	/**
	 * The session object.
	 *
	 * @var    SessionInterface
	 * @since  4.0.0
	 */
	private $session;

	/**
	 * Instantiate the command.
	 *
	 * @param   SessionInterface  $session          The session object.
	 * @param   MetadataManager   $metadataManager  The session metadata manager.
	 *
	 * @since   4.0.0
	 */
	public function __construct(SessionInterface $session, MetadataManager $metadataManager)
	{
		$this->session         = $session;
		$this->metadataManager = $metadataManager;

		parent::__construct();
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Running Session Metadata Garbage Collection');

		$sessionExpire = $this->session->getExpire();

		$this->metadataManager->deletePriorTo(time() - $sessionExpire);

		$symfonyStyle->success('Metadata garbage collection completed.');

		return 0;
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
		$this->setDescription('Perform session metadata garbage collection');
		$this->setHelp(
			<<<EOF
The <info>%command.name%</info> command runs the garbage collection operation for Joomla session metadata

<info>php %command.full_name%</info>
EOF
		);
	}
}
