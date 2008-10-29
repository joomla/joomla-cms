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
class JTableBackups extends JTable
{
/**
	 * @var int unsigned
	 */
	protected $backupid = null;
	/**
	 * @var varchar
	 */
	protected $name = null;
	/**
	 * @var int unsigned
	 */
	protected $description = null;
	/**
	 * @var int unsigned
	 */
	protected $start = null;
	/**
	 * @var varchar
	 */
	protected $end = null;
	/**
	 * @var varchar
	 */
	protected $location = null;
	/**
	 * @var int unsigned
	 */
	protected $data = null;

	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__backups', 'backupid', $db);
	}
}
