<?php
/**
 * NoNumber Framework Helper File: Assignments: Content
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
 * Assignments: Content
 */
class NNFrameworkAssignmentsContent
{
	var $_version = '12.6.4';

	/**
	 * passCats
	 *
	 * @param <object> $params
	 * inc_children
	 * inc_categories
	 * inc_articles
	 * inc_others
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passCats(&$main, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		// components that use the com_content secs/cats
		$components = array('com_content', 'com_contentsubmit');
		if (!in_array($main->_params->option, $components)) {
			return ($assignment == 'exclude');
		}

		$selection = $main->makeArray($selection);

		if (empty($selection)) {
			return ($assignment == 'exclude');
		}

		$pass = 0;

		$inc = (
			$main->_params->option == 'com_contentsubmit'
				|| ($params->inc_categories && $main->_params->option == 'com_content' && $main->_params->view == 'category')
				|| ($params->inc_articles && $main->_params->option == 'com_content' && ($main->_params->view == '' || $main->_params->view == 'article'))
				|| ($params->inc_others && !($main->_params->option == 'com_content' && ($main->_params->view == 'category' || $main->_params->view == '' || $main->_params->view == 'article')))
		);

		if ($inc) {
			if ($main->_params->option == 'com_contentsubmit') {
				// Content Submit
				$contentsubmit_params = new ContentsubmitModelArticle();
				if (in_array($contentsubmit_params->_id, $selection)) {
					$pass = 1;
				}
			} else {
				$app = JFactory::getApplication();

				if ($params->inc_others && !($main->_params->option == 'com_content' && ($main->_params->view == 'category' || $main->_params->view == 'article'))) {
					if ($article) {
						if (!isset($article->id)) {
							if (isset($article->slug)) {
								$article->id = (int) $article->slug;
							}
						}
						if (!isset($article->catid)) {
							if (isset($article->catslug)) {
								$article->catid = (int) $article->catslug;
							}
						}
						$main->_params->id = $article->id;
						$main->_params->view = 'article';
					}
				}

				switch ($main->_params->view) {
					case 'category':
						$catid = $main->_params->id;
						break;
					default:
						if (!$article && $main->_params->id) {
							$article = JTable::getInstance('content');
							$article->load($main->_params->id);
						}
						if ($article && $article->catid) {
							$catid = $article->catid;
						} else {
							$catid = JRequest::getInt('catid', $app->getUserState('com_content.articles.filter.category_id'));
						}
						break;
				}
				if ($catid) {
					$pass = in_array($catid, $selection);
					if ($pass && $params->inc_children == 2) {
						$pass = 0;
					} else if (!$pass && $params->inc_children) {
						$parentids = NNFrameworkAssignmentsContent::getParentIds($main, $catid);
						$parentids = array_diff($parentids, array('1'));
						foreach ($parentids as $parent) {
							if (in_array($parent, $selection)) {
								$pass = 1;
								break;
							}
						}
						unset($parentids);
					}
				}
			}
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}

	/**
	 * passArticles
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passArticles($main, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		if (!$main->_params->id
			|| !(($main->_params->option == 'com_content' && $main->_params->view == 'article')
				|| ($main->_params->option == 'com_flexicontent' && $main->_params->view == 'item')
			)
		) {
			return ($assignment == 'exclude');
		}

		$pass = 0;

		if ($selection && !is_array($selection)) {
			if (!(strpos($selection, '|') === false)) {
				$selection = explode('|', $selection);
			} else {
				$selection = explode(',', $selection);
			}
		}
		if (!empty($selection)) {
			$pass = in_array($main->_params->id, $selection);
		}

		if ($params->keywords && !is_array($params->keywords)) {
			$params->keywords = explode(',', $params->keywords);
		}
		if (!empty($params->keywords)) {
			$pass = 0;
			if (!$article) {
				require_once JPATH_SITE.'/components/com_content/models/article.php';
				$model = JModel::getInstance('article', 'contentModel');
				$article = $model->getItem($main->_params->id);
			}
			if (isset($article->metakey) && $article->metakey) {
				$keywords = explode(',', $article->metakey);
				foreach ($keywords as $keyword) {
					if ($keyword && in_array(trim($keyword), $params->keywords)) {
						$pass = 1;
						break;
					}
				}
				if (!$pass) {
					$keywords = explode(',', str_replace(' ', ',', $article->metakey));
					foreach ($keywords as $keyword) {
						if ($keyword && in_array(trim($keyword), $params->keywords)) {
							$pass = 1;
							break;
						}
					}
				}
			}
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}

	function getParentIds(&$main, $id = 0)
	{
		return $main->getParentIds($id, 'categories');
	}
}