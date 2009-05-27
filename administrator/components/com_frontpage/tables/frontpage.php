<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Content
 */
class TableFrontPage extends JTable
{
	/** @var int Primary key */
	var $content_id	= null;
	/** @var int */
	var $ordering	= null;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__content_frontpage', 'content_id', $db);
	}
}