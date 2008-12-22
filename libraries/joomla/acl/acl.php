<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 *
 * This and supporting files based heavily on phpGacl
 *
 * @copyright	Copyright (C) 2002,2003 Mike Benoit
 * @link		http://phpgacl.sourceforge.net/
 * @license		GNU Lesser General Public
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.query');

JTable::addIncludePath(dirname(dirname(__FILE__)).DS.'database'.DS.'tables');

/**
 * @package		Joomla.Framework
 * @subpackage	Acl
 */
class JAcl
{
	/**
	 * Authorises either the current or supplied user to perform an action
	 *
	 * @param	string $actionSection	The section of the action of test
	 * @param	string $action			The action value to test
	 * @param	string $assetSection	An optional asset section to test
	 * @param	int $asset				An optional asset id to test (both asset section and id must be used together)
	 * @param	int $userId				An optional user id to test against, otherwise the current user is used.
	 *
	 * @return	boolean
	 */
	function authorise($actionSection, $action, $assetSection = null, $asset = null, $userId = null)
	{
		// Get the current user if not supplied
		if ($userId === null) {
			$userId = JFactory::getUser()->get('id');
		}

		// Check for the root user
		$config = new JConfig;
		if ($userId == $config->root_user) {
			return true;
		}

		$result = self::check($actionSection, $action, 'users', $userId, $assetSection, $asset);

		return $result['allow'];
	}

	/**
	 * The Main function that does the actual ACL lookup.
	 *
	 * @param string The ACO section value
	 * @param string The ACO value
	 * @param string The ARO section value
	 * @param string The ARO section
	 * @param string The AXO section value (optional)
	 * @param string The AXO section value (optional)
	 * @param string The value of the root ARO group (optional)
	 * @param string The value of the root AXO group (optional)
	 *
	 * @return array Returns as much information as possible about the ACL so other functions can trim it down and omit unwanted data.
	 */
	function check($acoSectionValue, $acoValue, $aroSectionValue, $aroValue, $axoSectionValue=NULL, $axoValue=NULL, $rootAroGroup=NULL, $rootAxoGroup=NULL)
	{
		// @todo More advanced caching to span session
		static $cache;

		// Simple cache
		if ($cache == null) {
			$cache = array();
		}

		$cacheId = 'acl_query_'.$acoSectionValue.'-'.$acoValue.'-'.$aroSectionValue.'-'.$aroValue.'-'.$axoSectionValue.'-'.$axoValue.'-'.$rootAroGroup.'-'.$rootAxoGroup;

		if (!isset($cache[$cacheId]))
		{
			/*
			 * This query is where all the magic happens.
			 * The ordering is very important here, as well very tricky to get correct.
			 * Currently there can be  duplicate ACLs, or ones that step on each other toes. In this case, the ACL that was last updated/created
			 * is used.
			 *
			 * This is probably where the most optimizations can be made.
			 */
			$sql_aro_group_ids	= null;
			$sql_axo_group_ids	= null;
			$db = &JFactory::getDbo();

			$order_by = array();

			$query = new JQuery;

			$query->select('a.id,a.allow,a.return_value');
			$query->from('#__core_acl_acl AS a');
			$query->join('LEFT', '#__core_acl_aco_map ac ON ac.acl_id=a.id');
			$query->join('LEFT', '#__core_acl_aro_map ar ON ar.acl_id=a.id');
			$query->join('LEFT', '#__core_acl_axo_map ax ON ax.acl_id=a.id');

			// Get all groups mapped to this ARO/AXO
			$aroGroupIds = JAcl::acl_get_groups($aroSectionValue, $aroValue, $rootAroGroup, 'ARO');

			if (is_array($aroGroupIds) AND !empty($aroGroupIds))
			{
				$sql_aro_group_ids = implode(',', $aroGroupIds);
				$query->join('LEFT', '#__core_acl_aro_groups_map arg ON arg.acl_id=a.id');
				$query->join('LEFT', '#__core_acl_aro_groups rg ON rg.id=arg.group_id');
			}

			if ($axoSectionValue !== '' AND $axoValue !== '')
			{
				$axo_group_ids = JAcl::acl_get_groups($axoSectionValue, $axoValue, $rootAxoGroup, 'AXO');

				if (is_array($axo_group_ids) AND !empty($axo_group_ids))
				{
					$sql_axo_group_ids = implode(',', $axo_group_ids);
				}
			}

			// this join is necessary to weed out rules associated with axo groups
			$query->join('LEFT', '#__core_acl_axo_groups_map axg ON axg.acl_id=a.id');

			if ($sql_axo_group_ids) {
				$query->join('LEFT', '#__core_acl_axo_groups xg ON xg.id=axg.group_id');
			}

			$query->where('a.enabled = 1');
			$query->where('ac.section_value='. $db->quote($acoSectionValue));
			$query->where('ac.value='. $db->quote($acoValue));

			$temp = '(ar.section_value='. $db->quote($aroSectionValue) .' AND ar.value='. $db->quote($aroValue) .')';
			if ($sql_aro_group_ids) {
				$temp .= ' OR rg.id IN ('. $sql_aro_group_ids .')';
			}
			$query->where('('.$temp.')');

			if ($axoSectionValue == '' AND $axoValue === null)
			{
				$temp = '(ax.section_value IS NULL AND ax.value IS NULL)';
				$query->order('(CASE WHEN ar.value IS NULL THEN 0 ELSE 1 END) DESC');
				$query->order('(rg.rgt-rg.lft) ASC');
			}
			else {
				$temp = '(ax.section_value='. $db->quote($axoSectionValue) .' AND ax.value='. $db->quote($axoValue) .')';
			}

			if ($sql_axo_group_ids) {
				$temp .= ' OR xg.id IN ('. $sql_axo_group_ids .')';

				$query->order('(CASE WHEN ax.value IS NULL THEN 0 ELSE 1 END) DESC');
				$query->order('(xg.rgt-xg.lft) ASC');
			}
			else {
				$temp .= ' AND axg.group_id IS NULL';
			}
			$query->where('('.$temp.')');

			/*
			 * The ordering is always very tricky and makes all the difference in the world.
			 * Order (ar.value IS NOT NULL) DESC should put ACLs given to specific AROs
			 * ahead of any ACLs given to groups. This works well for exceptions to groups.
			 */

			$query->order('a.updated_date DESC');


			// we are only interested in the first row
			$db->setQuery($query->toString(), 0, 1);
			//echo $db->getQuery().'<hr />';
			$row = $db->loadRow();

			/*
			 * Return ACL ID. This is the key to "hooking" extras like pricing assigned to ACLs etc... Very useful.
			 */
			if (is_array($row)) {
				// Permission granted?
				// This below oneliner is very confusing.
				//$allow = (isset($row[1]) AND $row[1] == 1);

				//Prefer this.
				if ( isset($row[1]) AND $row[1] == 1 ) {
					$allow = TRUE;
				} else {
					$allow = FALSE;
				}

				$cache[$cacheId] = array('acl_id' => $row[0], 'return_value' => $row[2], 'allow' => $allow);
			}
			else {
				// Permission denied.
				$cache[$cacheId] = array('acl_id' => NULL, 'return_value' => NULL, 'allow' => FALSE);
			}
		}

		return $cache[$cacheId];
	}

	/**
	 * Grabs all groups mapped to an ARO.
	 *
	 * A root group value can be specified for looking at sub-tree
	 * (results include the root group)
	 *
	 * @param	string	The section value or the ARO or AXO
	 * @param	string	The value of the ARO or AXO
	 * @param	integer	The value of the group to start at (optional)
	 * @param	string	The type of group, either ARO or AXO (optional)
	 */
	function acl_get_groups($sectionValue, $value, $rootGroupValue=NULL, $type='ARO')
	{
		// @todo More advanced caching to span session
		static $cache;

		$db		= &JFactory::getDbo();
		$type	= strtolower($type);

		if ($type != 'aro' && $type != 'axo') {
			// @todo Throw an expection
			return array();
		}

		// Simple cache
		if ($cache == null) {
			$cache = array();
		}

		// Generate unique cache id.
		$cacheId = 'acl_get_groups_'.$sectionValue.'-'.$value.'-'.$rootGroupValue.'-'.$type;

		if (!isset($cache[$cacheId]))
		{
			$query = new JQuery;

			// Make sure we get the groups
			$query->select('DISTINCT g2.id');
			$query->from('#__core_acl_'.$type.' AS o');
			$query->join('INNER', '#__core_acl_groups_'.$type.'_map AS gm ON gm.'. $type .'_id=o.id');
			$query->join('INNER', '#__core_acl_'.$type.'_groups AS g1 ON g1.id = gm.group_id');

			$query->where('(o.section_value='. $db->quote($sectionValue) .' AND o.value='. $db->quote($value) .')');

			/*
			 * If root group value is specified, we have to narrow this query down
			 * to just groups deeper in the tree then what is specified.
			 * This essentially creates a virtual "subtree" and ignores all outside groups.
			 * Useful for sites like sourceforge where you may seperate groups by "project".
			 */
			if ( $rootGroupValue != '') {
				$query->join('INNER', '#__core_acl_'.$type.'_groups AS g3 ON g3.value='. $db->quote($rootGroupValue));
				$query->join('INNER', '#__core_acl_'.$type.'_groups AS g2 ON ((g2.lft BETWEEN g3.lft AND g1.lft) AND (g2.rgt BETWEEN g1.rgt AND g3.rgt))');
			}
			else {
				$query->join('INNER', '#__core_acl_'.$type.'_groups AS g2 ON (g2.lft <= g1.lft AND g2.rgt >= g1.rgt)');
			}

			$db->setQuery($query->toString());
			//echo $db->getQuery();
			$cache[$cacheId] = $db->loadResultArray();
		}

		return $cache[$cacheId];
	}

	/**
	 * Get a list of the allows Asset Groups that a user is allowed for a given Action
	 *
	 * This helper method is typicall used in conjuction with Type 3 Rules where an action is applied
	 * to one or more asset groups.  An primary object will typically be given a single asset level
	 * in an `access` field.  The usage could be something like:
	 *
	 * $assetGroups = JAcl::getAllowedAssetGroups('core', 'global.view');
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
	 * @param	integer	$userId			An optional user Id.  The current use it used if not supplied
	 *
	 * @return	string					An comma separated list of Asset Groups that the user is authorised to perform the action on
	 */
	static function getAllowedAssetGroups($actionSection, $action, $userId = null)
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
		$query->select('GROUP_CONCAT(DISTINCT axog.value SEPARATOR \',\')');
		$query->from('jos_core_acl_aco_map AS am');
		$query->join('INNER',	'#__core_acl_acl AS acl ON acl.id = am.acl_id');
		$query->join('INNER',	'#__core_acl_aro_groups_map AS agm ON agm.acl_id = am.acl_id');
		$query->join('LEFT',	'#__core_acl_axo_groups_map AS axogm ON axogm.acl_id = am.acl_id');
		$query->join('INNER',	'#__core_acl_axo_groups AS axog ON axog.id = axogm.group_id');
		$query->join('INNER',	'#__core_acl_groups_aro_map AS garom ON garom.group_id = agm.group_id');
		$query->join('INNER',	'#__core_acl_aro AS aro ON aro.id = garom.aro_id');
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
