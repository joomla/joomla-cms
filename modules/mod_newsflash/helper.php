<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

class modNewsFlashHelper
{
	function renderItem(&$item, &$params, &$access)
	{
		$mainframe = JFactory::getApplication();

		$user 	=& JFactory::getUser();

		$item->text 	= $item->introtext;
		$item->groups 	= '';
		$item->readmore = (trim($item->fulltext) != '');
		$item->metadesc = '';
		$item->metakey 	= '';
		$item->access 	= '';
		$item->created 	= '';
		$item->modified = '';

		if ($params->get('readmore') || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// Check to see if the user has access to view the full article
				if ($item->access <= $user->get('aid', 0)) {
					$linkOn = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
				} else {
					$linkOn = JRoute::_('index.php?option=com_user&task=register');
				}
			}

			$item->linkOn = $linkOn;
		}

		if (!$params->get('image')) {
			$item->text = preg_replace( '/<img[^>]*>/', '', $item->text );
		}

		$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (&$item, &$params, 1));
		$item->afterDisplayTitle = trim(implode("\n", $results));

		$results = $mainframe->triggerEvent('onBeforeDisplayContent', array (&$item, &$params, 1));
		$item->beforeDisplayContent = trim(implode("\n", $results));

		require(JModuleHelper::getLayoutPath('mod_newsflash', '_item'));
	}

	function getList(&$params, &$access)
	{
		$mainframe = JFactory::getApplication();

		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$catid 	= (int) $params->get('catid', 0);
		$items 	= (int) $params->get('items', 0);

		$contentConfig	= &JComponentHelper::getParams( 'com_content' );
		$noauth			= !$contentConfig->get('shownoauth');

		$date =& JFactory::getDate();
		$now = $date->toMySQL();

		$nullDate = $db->getNullDate();

		// query to determine article count
		$query = 'SELECT a.*,' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' WHERE a.state = 1 ' .
			($noauth ? ' AND a.access IN (' .implode(',', $user->authorisedLevels('com_content.article.view')). ') AND cc.access IN (' .implode(',', $user->authorisedLevels('com_content.category.view')).')' : '').
			' AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' ) ' .
			' AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )' .
			' AND cc.id = '. (int) $catid .
			' AND cc.published = 1' .
			' ORDER BY a.ordering';
		$db->setQuery($query, 0, $items);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
