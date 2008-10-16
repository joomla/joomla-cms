<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableACL extends JTable
{
/**
	 * @var int unsigned
	 */
	protected $id = null;
	/**
	 * @var varchar
	 */
	protected $section_value = null;
	/**
	 * @var int unsigned
	 */
	protected $allow = null;
	/**
	 * @var int unsigned
	 */
	protected $enabled = null;
	/**
	 * @var varchar
	 */
	protected $return_value = null;
	/**
	 * @var varchar
	 */
	protected $note = null;
	/**
	 * @var int unsigned
	 */
	protected $updated_date = null;
	/**
	 * @var int unsigned
	 */
	protected $acl_type = null;

	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__core_acl_acl', 'id', $db);
	}
}
