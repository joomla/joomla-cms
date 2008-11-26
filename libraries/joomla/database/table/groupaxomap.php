<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_groupmap.php';

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class MembersTableGroupAxoMap extends MembersTable_GroupMap
{
	var $axo_id = null;

	/**
	 * @var	string The section type
	 * @protected
	 * @final
	 */
	var $_type = 'axo';
}
