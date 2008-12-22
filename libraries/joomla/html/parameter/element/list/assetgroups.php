<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
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

require_once dirname(dirname(__FILE__)).DS.'list.php';

/**
 * Renders a select list of Asset Groups
 *
 * @package 	Joomla.Framework
 * @subpackage	Parameter
 * @since		1.6
 */
class JElementList_AssetGroups extends JElementList
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'List_AssetGroups';

	/**
	 * Get the options for the element
	 *
	 * @param	object $node
	 * @return	array
	 */
	protected function _getOptions(&$node)
	{
		$db = &JFactory::getDBO();
		$db->setQuery(
			'SELECT value, name AS text'
			.' FROM #__core_acl_axo_groups'
			.' WHERE parent_id > 0'
			.' ORDER BY name'
		);
		$options = $db->loadObjectList();
		return $options;
	}
}
