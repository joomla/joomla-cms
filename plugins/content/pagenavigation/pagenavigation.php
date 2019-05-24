<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

/**
 * Pagenavigation plugin class.
 *
 * @since  1.5
 */
class PlgContentPagenavigation extends JPlugin
{
	/**
	 * If in the article view and the parameter is enabled shows the page navigation
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   mixed    &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  void or true
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$app   = JFactory::getApplication();
		$view  = $app->input->get('view');
		$print = $app->input->getBool('print');

		if ($print)
		{
			return false;
		}

		if ($context === 'com_content.article' && $view === 'article' && $params->get('show_item_navigation'))
		{
			$db       = JFactory::getDbo();
			$user     = JFactory::getUser();
			$lang     = JFactory::getLanguage();
			$nullDate = $db->getNullDate();

			$date = JFactory::getDate();
			$now  = $date->toSql();

			$uid        = $row->id;
			$option     = 'com_content';
			$canPublish = $user->authorise('core.edit.state', $option . '.article.' . $row->id);

			/**
			 * The following is needed as different menu items types utilise a different param to control ordering.
			 * For Blogs the `orderby_sec` param is the order controlling param.
			 * For Table and List views it is the `orderby` param.
			**/
			$params_list = $params->toArray();

			if (array_key_exists('orderby_sec', $params_list))
			{
				$order_method = $params->get('orderby_sec', '');
			}
			else
			{
				$order_method = $params->get('orderby', '');
			}

			// Additional check for invalid sort ordering.
			if ($order_method === 'front')
			{
				$order_method = '';
			}

			// Get the order code
			$orderDate = $params->get('order_date');
			$queryDate = $this->getQueryDate($orderDate);

			// Determine sort order.
			switch ($order_method)
			{
				case 'date' :
					$orderby = $queryDate;
					break;
				case 'rdate' :
					$orderby = $queryDate . ' DESC ';
					break;
				case 'alpha' :
					$orderby = 'a.title';
					break;
				case 'ralpha' :
					$orderby = 'a.title DESC';
					break;
				case 'hits' :
					$orderby = 'a.hits';
					break;
				case 'rhits' :
					$orderby = 'a.hits DESC';
					break;
				case 'order' :
					$orderby = 'a.ordering';
					break;
				case 'author' :
					$orderby = 'a.created_by_alias, u.name';
					break;
				case 'rauthor' :
					$orderby = 'a.created_by_alias DESC, u.name DESC';
					break;
				case 'front' :
					$orderby = 'f.ordering';
					break;
				default :
					$orderby = 'a.ordering';
					break;
			}

			$xwhere = ' AND (a.state = 1 OR a.state = -1)'
				. ' AND (publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($now) . ')'
				. ' AND (publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($now) . ')';

			// Array of articles in same category correctly ordered.
			$query = $db->getQuery(true);

			// Sqlsrv changes
			$case_when = ' CASE WHEN ' . $query->charLength('a.alias', '!=', '0');
			$a_id = $query->castAsChar('a.id');
			$case_when .= ' THEN ' . $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ' . $a_id . ' END as slug';

			$case_when1 = ' CASE WHEN ' . $query->charLength('cc.alias', '!=', '0');
			$c_id = $query->castAsChar('cc.id');
			$case_when1 .= ' THEN ' . $query->concatenate(array($c_id, 'cc.alias'), ':');
			$case_when1 .= ' ELSE ' . $c_id . ' END as catslug';
			$query->select('a.id, a.title, a.catid, a.language,' . $case_when . ',' . $case_when1)
				->from('#__content AS a')
				->join('LEFT', '#__categories AS cc ON cc.id = a.catid');

			if ($order_method === 'author' || $order_method === 'rauthor')
			{
				$query->select('a.created_by, u.name');
				$query->join('LEFT', '#__users AS u ON u.id = a.created_by');
			}

			$query->where(
					'a.catid = ' . (int) $row->catid . ' AND a.state = ' . (int) $row->state
						. ($canPublish ? '' : ' AND a.access IN (' . implode(',', JAccess::getAuthorisedViewLevels($user->id)) . ') ') . $xwhere
				);
			$query->order($orderby);

			if ($app->isClient('site') && $app->getLanguageFilter())
			{
				$query->where('a.language in (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');
			}

			$db->setQuery($query);
			$list = $db->loadObjectList('id');

			// This check needed if incorrect Itemid is given resulting in an incorrect result.
			if (!is_array($list))
			{
				$list = array();
			}

			reset($list);

			// Location of current content item in array list.
			$location = array_search($uid, array_keys($list));
			$rows     = array_values($list);

			$row->prev = null;
			$row->next = null;

			if ($location - 1 >= 0)
			{
				// The previous content item cannot be in the array position -1.
				$row->prev = $rows[$location - 1];
			}

			if (($location + 1) < count($rows))
			{
				// The next content item cannot be in an array position greater than the number of array postions.
				$row->next = $rows[$location + 1];
			}

			if ($row->prev)
			{
				$row->prev_label = ($this->params->get('display', 0) == 0) ? JText::_('JPREV') : $row->prev->title;
				$row->prev = JRoute::_(ContentHelperRoute::getArticleRoute($row->prev->slug, $row->prev->catid, $row->prev->language));
			}
			else
			{
				$row->prev_label = '';
				$row->prev = '';
			}

			if ($row->next)
			{
				$row->next_label = ($this->params->get('display', 0) == 0) ? JText::_('JNEXT') : $row->next->title;
				$row->next = JRoute::_(ContentHelperRoute::getArticleRoute($row->next->slug, $row->next->catid, $row->next->language));
			}
			else
			{
				$row->next_label = '';
				$row->next = '';
			}

			// Output.
			if ($row->prev || $row->next)
			{
				// Get the path for the layout file
				$path = JPluginHelper::getLayoutPath('content', 'pagenavigation');

				// Render the pagenav
				ob_start();
				include $path;
				$row->pagination = ob_get_clean();

				$row->paginationposition = $this->params->get('position', 1);

				// This will default to the 1.5 and 1.6-1.7 behavior.
				$row->paginationrelative = $this->params->get('relative', 0);
			}
		}
	}

	/**
	 * Translate an order code to a field for primary ordering.
	 *
	 * @param   string  $orderDate  The ordering code.
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   3.3
	 */
	private static function getQueryDate($orderDate)
	{
		$db = JFactory::getDbo();

		switch ($orderDate)
		{
			// Use created if modified is not set
			case 'modified' :
				$queryDate = ' CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END';
				break;

			// Use created if publish_up is not set
			case 'published' :
				$queryDate = ' CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END ';
				break;

			// Use created as default
			case 'created' :
			default :
				$queryDate = ' a.created ';
				break;
		}

		return $queryDate;
	}
}
