<?php
/**
 * NoNumber Framework Helper File: Assignments: Resources
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
 * Assignments: Resources
 */
class NNFrameworkAssignmentsResources
{
	var $_version = '12.6.4';

	/**
	 * passCategories_MR
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
	function passCategories_MR(&$main, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		if ($main->_params->option != 'com_resource') {
			return ($assignment == 'exclude');
		}

		$pass = (
			($params->inc_categories && $main->_params->view == 'list')
				|| ($params->inc_items && $main->_params->view == 'article')
		);

		if (!$pass) {
			return ($assignment == 'exclude');
		}

		$selection = $main->makeArray($selection);

		if ($article && isset($article->catid)) {
			$cats = $article->catid;
		} else {
			$cats = $main->_params->id;
			switch ($main->_params->view) {
				case 'list':
					if (!$cats) {
						$cats = JRequest::getInt('section_id');
					}
					if (!$cats) {
						$cats = JRequest::getInt('category_id');
					}
					break;
				case 'article':
				default:
					$id = JRequest::getInt('article');
					if ($id) {
						$query = $main->_db->getQuery(true);
						$query->select('c.catid');
						$query->from('#__js_res_record_category AS c');
						$query->where('c.record_id = '.(int) $id);
						$main->_db->setQuery($query);
						$cats = $main->_db->loadResultArray();
					} else {
						$cats = array(0);
					}
					break;
			}
		}

		$cats = $main->makeArray($cats, 1);

		$pass = $main->passSimple($cats, $selection, 'include');

		if ($pass && $params->inc_children == 2) {
			return ($assignment == 'exclude');
		} else if (!$pass && $params->inc_children) {
			foreach ($cats as $cat) {
				$cats = array_merge($cats, NNFrameworkAssignmentsResources::getCatParentIds($main, $cat));
			}
		}

		return $main->passSimple($cats, $selection, $assignment);
	}

	function getCatParentIds(&$main, $id = 0)
	{
		return $main->getParentIds($id, 'js_res_category', 'parent');
	}
}