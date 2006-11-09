<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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

		$item->text 	= ampReplace($item->introtext);
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
				if ($item->access <= $user->get('gid')) 
				{
					$Itemid = JContentHelper::getItemid($item->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;view=article&amp;id=".$item->id."&amp;Itemid=".$Itemid);
				} 
				else 
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
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

		$catid 	= intval($params->get('catid'));
		$items 	= intval($params->get('items', 0));

		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$noauth  = !$contentConfig->get('shownoauth');

		$now 	 = date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);
		$nullDate = $db->getNullDate();

		// query to determine article count
		$query = "SELECT a.id, a.introtext, a.`fulltext`, a.images, a.attribs, a.title, a.state" .
			"\n FROM #__content AS a" .
			"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
			"\n INNER JOIN #__sections AS s ON s.id = a.sectionid" .
			"\n WHERE a.state = 1".
			($noauth ? "\n AND a.access <= " .$user->get('gid'). " AND cc.access <= " .$user->get('gid'). " AND s.access <= " .$user->get('gid') : '').
			"\n AND (a.publish_up = '$nullDate' OR a.publish_up <= '$now' ) " .
			"\n AND (a.publish_down = '$nullDate' OR a.publish_down >= '$now' )" .
			"\n AND a.catid = $catid"."\n AND cc.published = 1" .
			"\n AND s.published = 1" .
			"\n ORDER BY a.ordering";
		$db->setQuery($query, 0, $items);
		$rows = $db->loadObjectList();

		return $rows;
	}
}