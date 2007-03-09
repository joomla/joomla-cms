<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE . '/components/com_content/helpers/content.php');

class modNewsFlashHelper
{
	function renderItem(&$item, &$params, &$access)
	{
		global $mainframe;

		$user 	=& JFactory::getUser();

		$item->text 	= htmlspecialchars($item->introtext);
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
				if ($item->access <= $user->get('aid', 0))
				{
					$Itemid = JContentHelper::getItemid($item->id);
					$linkOn = JRoute::_('index.php?option=com_content&view=article&catid='.$item->catslug.'&id='.$item->slug.'&Itemid='.$Itemid);
				}
				else
				{
					$linkOn = JRoute::_('index.php?option=com_user&task=register');
				}
			}

			$item->linkOn = $linkOn;
		}

		$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (&$item, &$params, 1));
		$item->afterDisplayTitle = trim(implode("\n", $results));

		$results = $mainframe->triggerEvent('onBeforeDisplayContent', array (&$item, &$params, 1));
		$item->beforeDisplayContent = trim(implode("\n", $results));

		require(JModuleHelper::getLayoutPath('mod_newsflash', '_item'));
	}

	function getList(&$params, &$access)
	{
		global $mainframe;

		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();
		$aid	= $user->get('aid', 0);

		$catid 	= (int) $params->get('catid', 0);
		$items 	= (int) $params->get('items', 0);

		$contentConfig	= &JComponentHelper::getParams( 'com_content' );
		$noauth			= !$contentConfig->get('shownoauth');

		$now 	 = date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);
		$nullDate = $db->getNullDate();

		// query to determine article count
		$query = 'SELECT a.*,' .
			' CASE WHEN CHAR_LENGTH(a.title_alias) THEN CONCAT_WS(":", a.id, a.title_alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.name) THEN CONCAT_WS(":", cc.id, cc.name) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
			' WHERE a.state = 1 ' .
			($noauth ? ' AND a.access <= ' .(int) $aid. ' AND cc.access <= ' .(int) $aid. ' AND s.access <= ' .(int) $aid : '').
			' AND (a.publish_up = "'.$nullDate.'" OR a.publish_up <= "'.$now.'" ) ' .
			' AND (a.publish_down = "'.$nullDate.'" OR a.publish_down >= "'.$now.'" )' .
			' AND cc.id = '. $catid .
			' AND cc.section = s.id' .
			' AND cc.published = 1' .
			' AND s.published = 1' .
			' ORDER BY a.ordering';
		$db->setQuery($query, 0, $items);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
