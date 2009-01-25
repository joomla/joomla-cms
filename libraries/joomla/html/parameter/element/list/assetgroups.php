<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
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
