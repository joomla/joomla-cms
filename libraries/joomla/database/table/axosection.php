<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_aclsection.php';

/**
 * Table object for ACO sections.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableAxoSection extends JTable_AclSection
{
	/**
	 * @var	string The section type
	 */
	final protected $_type = 'axo';
}
