<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Checks the database schema against one SQL Azure DDL query to see if it has been run.
 *
 * @package     Joomla.Libraries
 * @subpackage  Schema
 * @since       2.5
 */
class JSchemaChangeitemsqlazure extends JSchemaChangeitemsqlsrv
{
	public $name = 'sqlazure';
}
