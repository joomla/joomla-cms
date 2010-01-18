<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Pagenavigation plugin class.
 *
 * @package		Joomla
 * @subpackage	plg_pagenavigation
 */
class plgContentPagenavigation extends JPlugin
{
	public function onBeforeDisplayContent(&$row, &$params, $page=0)
	{
		$view = JRequest::getCmd('view');

		if ($params->get('show_item_navigation') && ($view == 'article'))
		{

			$html = '';
			$db = &JFactory::getDbo();
			$user = &JFactory::getUser();
			$nullDate = $db->getNullDate();

			$date = &JFactory::getDate();
			$config = &JFactory::getConfig();
			$now = $date->toMySQL();

			$uid = $row->id;
			$option = 'com_content';
			$canPublish = $user->authorize('core.edit.state', $option.'.'.$view.'.'.$row->id);

			// The following is needed as different menu items types utilise a different param to control ordering.
			// For Blogs the `orderby_sec` param is the order controlling param.
			// For Table and List views it is the `orderby` param.
			$params_list = $params->toArray();
			if (array_key_exists('orderby_sec', $params_list)) {
				$order_method = $params->get('orderby_sec', '');
			} else {
				$order_method = $params->get('orderby', '');
			}
			// Additional check for invalid sort ordering.
			if ($order_method == 'front') {
				$order_method = '';
			}

			// Determine sort order.
			switch ($order_method)
			{
				case 'date' :
					$orderby = 'a.created';
					break;
				case 'rdate' :
					$orderby = 'a.created DESC';
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

			$xwhere = ' AND (a.state = 1 OR a.state = -1)' .
			' AND (publish_up = '.$db->Quote($nullDate).' OR publish_up <= '.$db->Quote($now).')' .
			' AND (publish_down = '.$db->Quote($nullDate).' OR publish_down >= '.$db->Quote($now).')';

			// Array of articles in same category correctly ordered.
			$query = 'SELECT a.id,'
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'
			. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'
			. ' FROM #__content AS a'
			. ' LEFT JOIN #__categories AS cc ON cc.id = a.catid'
			. ' WHERE a.catid = ' . (int) $row->catid
			. ' AND a.state = '. (int) $row->state
			. ($canPublish ? '' : ' AND a.access <= ' .(int) $user->get('aid', 0))
			. $xwhere
			. ' ORDER BY '. $orderby;
			$db->setQuery($query);
			$list = $db->loadObjectList('id');

			// This check needed if incorrect Itemid is given resulting in an incorrect result.
			if (!is_array($list)) {
				$list = array();
			}

			reset($list);

			// Location of current content item in array list.
			$location = array_search($uid, array_keys($list));

			$rows = array_values($list);

			$row->prev = null;
			$row->next = null;

			if ($location -1 >= 0) 	{
				// The previous content item cannot be in the array position -1.
				$row->prev = $rows[$location -1];
			}

			if (($location +1) < count($rows)) {
				// The next content item cannot be in an array position greater than the number of array postions.
				$row->next = $rows[$location +1];
			}

			$pnSpace = "";
			if (JText::_('LT') || JText::_('GT')) {
				$pnSpace = " ";
			}

			if ($row->prev) {
				$row->prev = JRoute::_('index.php?option=com_content&view=article&catid='.$row->prev->catslug.'&id='.$row->prev->slug);
			} else {
				$row->prev = '';
			}

			if ($row->next) {
				$row->next = JRoute::_('index.php?option=com_content&view=article&catid='.$row->next->catslug.'&id='.$row->next->slug);
			} else {
				$row->next = '';
			}


			// Output.
			if ($row->prev || $row->next)
			{
				$html = '
				<table align="center" class="pagenav">
				<tr>'
				;
				if ($row->prev)
				{
					$html .= '
					<th class="pagenav_prev">
						<a href="'. $row->prev .'">'
							. JText::_('LT') . $pnSpace . JText::_('Prev') . '</a>
					</th>'
					;
				}

				if ($row->prev && $row->next)
				{
					$html .= '
					<td width="50">
						&nbsp;
					</td>'
					;
				}

				if ($row->next)
				{
					$html .= '
					<th class="pagenav_next">
						<a href="'. $row->next .'">'
							. JText::_('Next') . $pnSpace . JText::_('GT') .'</a>
					</th>'
					;
				}
				$html .= '
				</tr>
				</table>'
				;

				$position 	 = $this->params->get('position', 1);

				if ($position) {
					// Display after content.
					$row->text .= $html;
				} else {
					// Display before content.
					$row->text = $html . $row->text;
				}
			}
		}

		return ;
	}
}