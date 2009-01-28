<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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
	 * @var text
	 */
	protected $data = null;
	/** @var text */
	protected $params = null;

	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__backup_entries', 'entryid', $db);
	}
	
	/**
	 * Serialise (and unserialise after store) the params and data
	 *
	 */
	public function store($updateNulls=false) {
		 $this->data = serialize($this->data);
		 $this->params = serialize($this->params);
		 $res = parent::store($updateNulls);
		 $this->data = unserialize($this->data);
		 $this->params = unserialize($this->params);
		 return $res;
	}
	
	/**
	 * Unserialise the params and data
	 *
	 */
	public function load($oid = null) {
		 $res = parent::load($oid);
		 if($res) {
		 	$this->data = unserialize($this->data);
		 	$this->params = unserialize($this->params);
		 }
		 return $res;
	}
}
