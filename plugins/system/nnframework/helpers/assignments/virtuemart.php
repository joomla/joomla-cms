<?php
/**
 * NoNumber Framework Helper File: Assignments: VirtueMart
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
 * Assignments: VirtueMart
 */
class NNFrameworkAssignmentsVirtueMart
{
	var $_version = '12.6.4';

	/**
	 * passCategories_VirtueMart
	 *
	 * @param <object> $params
	 * inc_children
	 * inc_categories
	 * inc_items
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passCategories_VirtueMart(&$main, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		if ($main->_params->option != 'com_virtuemart') {
			return ($assignment == 'exclude');
		}

		$pass = (
			($params->inc_categories
				&& ($main->_params->view == 'category')
			)
				|| ($params->inc_items && $main->_params->view == 'productdetails')
		);

		if (!$pass) {
			return ($assignment == 'exclude');
		}

		$selection = $main->makeArray($selection);

		$cats = $main->makeArray($main->_params->category_id);

		$pass = $main->passSimple($cats, $selection, 'include');
		if ($pass && $params->inc_children == 2) {
			return ($assignment == 'exclude');
		} else if (!$pass && $params->inc_children) {
			foreach ($cats as $cat) {
				$cats = array_merge($cats, self::getCatParentIds($main, $cat));
			}
		}

		return $main->passSimple($cats, $selection, $assignment);
	}

	/**
	 * passItems_VirtueMart
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passItems_VirtueMart(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		if (!$main->_params->id || $main->_params->option != 'com_virtuemart' || $main->_params->view != 'productdetails') {
			return ($assignment == 'exclude');
		}

		$selection = $main->makeArray($selection);

		return $main->passSimple(array($main->_params->id), $selection, $assignment);
	}

	function getCatParentIds(&$main, $id = 0)
	{
		return $main->getParentIds($id, 'virtuemart_category_categories', 'category_parent_id', 'category_child_id');
	}
}