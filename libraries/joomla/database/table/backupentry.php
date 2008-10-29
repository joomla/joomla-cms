<?php
/**
 * @version		$Id: acl.php 11140 2008-10-16 18:17:16Z ircmaxell $
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableBackupEntry extends JTable
{
	/**
	 * @var int unsigned
	 */
	protected $entryid = null;
	/**
	 * @var int unsigned
	 */
	protected $backupid = null;	
	/**
	 * @var varchar
	 */
	protected $type = null;
	/**
	 * @var varchar
	 */
	protected $name = null;
	/**
	 * @var varchar
	 */
	protected $source = null;
	/**
	 * @var text
	 */
	protected $data = null;

	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__backup_entries', 'entryid', $db);
	}
}
