<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_aclsection.php';

/**
 * Table object for ACL sections.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.6
 */
class JTableAclSection extends JTable_AclSection
{
	/**
	 * @var	string The section type
	 */
	protected $_type = 'acl';
}
