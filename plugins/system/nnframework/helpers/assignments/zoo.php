<?php
/**
 * NoNumber Framework Helper File: Assignments: ZOO
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
 * Assignments: ZOO
 */
class NNFrameworkAssignmentsZOO
{
	var $_version = '12.6.4';

	/**
	 * passCategories_Zoo
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
	function passCategories_ZOO(&$main, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		if ($main->_params->option != 'com_zoo') {
			return ($assignment == 'exclude');
		}

		if (!$main->_params->view) {
			$main->_params->view = $main->_params->task;
		}
		$pass = (
			($params->inc_apps && $main->_params->view == 'frontpage')
				|| ($params->inc_categories && $main->_params->view == 'category')
				|| ($params->inc_items && $main->_params->view == 'item')
		);

		if (!$pass) {
			return ($assignment == 'exclude');
		}

		$selection = $main->makeArray($selection);

		$cats = array();
		if ($article && isset($article->catid)) {
			$cats = $article->catid;
		} else {
			switch ($main->_params->view) {
				case 'frontpage':
					if ($main->_params->id) {
						$cats[] = $main->_params->id;
					} else {
						$menuparams = $main->getMenuItemParams($main->_params->Itemid);
						if (isset($menuparams->application)) {
							$cats[] = 'app'.$menuparams->application;
						}
					}
					break;
				case 'category':
					if ($main->_params->id) {
						$cats[] = $main->_params->id;
					} else {
						$menuparams = $main->getMenuItemParams($main->_params->Itemid);
						if (isset($menuparams->category)) {
							$cats[] = $menuparams->category;
						}
					}
					if ($cats['0']) {
						$query = $main->_db->getQuery(true);
						$query->select('c.application_id');
						$query->from('#__zoo_category AS c');
						$query->where('c.id = '.(int) $cats['0']);
						$main->_db->setQuery($query);
						if ($main->_db->loadResult()) {
							$cats[] = 'app'.$main->_db->loadResult();
						}
					}
					break;
				case 'item':
					$id = $main->_params->id;
					if (!$id) {
						$menuparams = $main->getMenuItemParams($main->_params->Itemid);
						$id = isset($menuparams->item_id) ? $menuparams->item_id : '';
					}
					if ($id) {
						$query = $main->_db->getQuery(true);
						$query->select('c.category_id');
						$query->from('#__zoo_category_item AS c');
						$query->where('c.item_id = '.(int) $id);
						$query->where('c.category_id != 0');
						$main->_db->setQuery($query);
						$cats = $main->_db->loadResultArray();

						$query = $main->_db->getQuery(true);
						$query->select('i.application_id');
						$query->from('#__zoo_item AS i');
						$query->where('i.id = '.(int) $id);
						$main->_db->setQuery($query);
						$cats[] = 'app'.$main->_db->loadResult();
					}
					break;
				default:
					return ($assignment == 'exclude');
					break;
			}
		}

		$cats = $main->makeArray($cats);

		$pass = $main->passSimple($cats, $selection, 'include');

		if ($pass && $params->inc_children == 2) {
			return ($assignment == 'exclude');
		} else if (!$pass && $params->inc_children) {
			foreach ($cats as $cat) {
				$cats = array_merge($cats, NNFrameworkAssignmentsZOO::getCatParentIds($main, $cat));
			}
		}

		return $main->passSimple($cats, $selection, $assignment);
	}

	function getCatParentIds(&$main, $id = 0)
	{
		$parent_ids = array();

		if (!$id) {
			return $parent_ids;
		}

		while ($id) {
			if (substr($id, 0, 3) == 'app') {
				$parent_ids[] = $id;
				break;
			} else {
				$query = $main->_db->getQuery(true);
				$query->select('c.parent');
				$query->from('#__zoo_category AS c');
				$query->where('c.id = '.(int) $id);
				$main->_db->setQuery($query);
				$pid = $main->_db->loadResult();
				if ($pid) {
					$parent_ids[] = $pid;
				} else {
					$query = $main->_db->getQuery(true);
					$query->select('c.application_id');
					$query->from('#__zoo_category AS c');
					$query->where('c.id = '.(int) $id);
					$main->_db->setQuery($query);
					$app = $main->_db->loadResult();
					if ($app) {
						$parent_ids[] = 'app'.$app;
					}
					break;
				}
				$id = $pid;
			}
		}
		return $parent_ids;
	}
}