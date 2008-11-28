<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JTable::addIncludePath(dirname(dirname(__FILE__)).DS.'database'.DS.'tables');

/**
 * @package		Joomla.Framework
 * @subpackage	Acl
 */
class JAclHelper
{
	/**
	 * Get a list of the allows Asset Groups that a user is allowed for a given Action
	 *
	 * This helper method is typicall used in conjuction with Type 3 Rules where an action is applied
	 * to one or more asset groups.  An primary object will typically be given a single asset level
	 * in an `access` field.  The usage could be something like:
	 *
	 * $assetGroups = JAclHelper::getAllowedAssetGroups('com_content', 'view');
	 *
	 * $query = new JQuery;
	 * $query->select('a.*');
	 * $query->from('#__content AS a');
	 * $query->where('a.published = 1');
	 * $query->where('a.access IN ('.$assetGroups.')');
	 * $db = &JFactory:getDBO();
	 * $db->setQuery();
	 * $items = $db->loadObjectList();
	 *
	 * @param	string $actionSection	The section value for the action
	 * @param	mixed $action			A single action value, or an array of action values, to be tested
	 * @param	string $assetSection	An optional section value for the asset (not used)
	 * @param	integer	$userId			An optional user Id.  The current use it used if not supplied
	 *
	 * @return	string					An comma separated list of Asset Groups that the user is authorised to perform the action on
	 */
	static function getAllowedAssetGroups($actionSection, $action, $assetSection = null, $userId = null)
	{
		// @todo This result is ideal for caching in the session as it need only be calculated once for the user for each context

		if (empty($actionSection)) {
			return '0';
		}

		if (empty($assetSection)) {
			$assetSection = $actionSection;
		}

		if ($userId === null) {
			$user = &JFactory::getUser();
			$userId = $user->get('id');
		}

		$db	= &JFactory::getDBO();

		jimport('joomla.database.query');
		$query	= new JQuery;
		$query->select('GROUP_CONCAT(DISTINCT axom.value SEPARATOR \',\')');
		$query->from('jos_core_acl_aco_map AS am');
		$query->join('INNER',	'jos_core_acl_acl AS acl ON acl.id = am.acl_id');
		$query->join('INNER',	'jos_core_acl_aro_groups_map AS agm ON agm.acl_id = am.acl_id');
		$query->join('LEFT',	'jos_core_acl_axo_map AS axom ON axom.acl_id = am.acl_id');
		$query->join('INNER',	'jos_core_acl_groups_aro_map AS garom ON garom.group_id = agm.group_id');
		$query->join('INNER',	'jos_core_acl_aro AS aro ON aro.id = garom.aro_id');
		$query->where('am.section_value = '.$db->Quote($actionSection));

		if (is_array($action))
		{
			$action	= array_map(array($db, 'Quote'), $action);
			$query->where('am.value IN ('.implode(',', $action).')');
		}
		else {
			$query->where('am.value = '.$db->Quote($action));
		}
		$query->where('acl.enabled = 1');
		$query->where('acl.allow = 1');
		$query->where('aro.value = '.(int) $userId);
		$db->setQuery($query->toString());
		if ($ids = $db->loadResult()) {
			return $ids;
		}
		else {
			return '0';
		}
	}
}
