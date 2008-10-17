<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Aro table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableARO extends JTable
{
	/** @var int Primary key */
	protected $id		= null;

	protected $section_value	= null;

	protected $value			= null;

	protected $order_value	= null;

	protected $name			= null;

	protected $hidden			= null;

	protected function __construct(&$db)
	{
		parent::__construct('#__core_acl_aro', 'aro_id', $db);
	}
}
