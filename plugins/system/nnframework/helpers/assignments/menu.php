<?php
/**
 * NoNumber Framework Helper File: Assignments: Menu
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Assignments: Menu
 */
class NNFrameworkAssignmentsMenu
{
	var $_version = '12.6.4';

	/**
	 * passMenuItems
	 *
	 * @param <object> $params
	 * inc_children
	 * inc_noItemid
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passMenuItem(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$pass = 0;

		if ($main->_params->Itemid) {
			$selection = $main->makeArray($selection);
			$pass = in_array($main->_params->Itemid, $selection);
			if ($pass && $params->inc_children == 2) {
				$pass = 0;
			} else if (!$pass && $params->inc_children) {
				$parentids = NNFrameworkAssignmentsMenu::getParentIds($main, $main->_params->Itemid);
				$parentids = array_diff($parentids, array('1'));
				foreach ($parentids as $parent) {
					if (in_array($parent, $selection)) {
						$pass = 1;
						break;
					}
				}
				unset($parentids);
			}
		} else if ($params->inc_noItemid) {
			$pass = 1;
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}

	function getParentIds(&$main, $id = 0)
	{
		return $main->getParentIds($id, 'menu');
	}
}