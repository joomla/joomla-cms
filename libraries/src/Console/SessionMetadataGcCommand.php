<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Console\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\Session\SessionInterface;

/**
 * Console command for performing session metadata garbage collection
 *
 * @since  4.0.0
 */
class SessionMetadataGcCommand extends AbstractCommand
{
	/**
	 * The database object.
	 *
	 * @var    DatabaseInterface
	 * @since  4.0.0
	 */
	private $db;

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
	 * @param   SessionInterface   $session  The session object.
	 * @param   DatabaseInterface  $db       The database object.
	 *
	 * @since   4.0.0
	 */
	public function __construct(SessionInterface $session, DatabaseInterface $db)
	{
		$this->session = $session;
		$this->db      = $db;

		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Running Session Metadata Garbage Collection');

		$sessionExpire = $this->session->getExpire();

		(new MetadataManager($this->getApplication(), $this->db))->deletePriorTo(time() - $sessionExpire);

		$symfonyStyle->success('Metadata garbage collection completed.');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function initialise()
	{
		$this->setName('session:metadata:gc');
		$this->setDescription('Performs session metadata garbage collection');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command runs the garbage collection operation for Joomla session metadata

<info>php %command.full_name%</info>
EOF
		);
	}
}
