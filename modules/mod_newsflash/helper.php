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

require_once(JApplicationHelper::getPath('helper', 'com_content'));

class modNewsFlash
{
	function display(&$params)
	{
		global $mainframe;
		
		$params->set('intro_only', 1);
		$params->set('hide_author', 1);
		$params->set('hide_createdate', 0);
		$params->set('hide_modifydate', 1);
		
		// Disable edit ability icon
		$access = new stdClass();
		$access->canEdit = 0;
		$access->canEditOwn = 0;
		$access->canPublish = 0;
		
		$style 			= $params->get('style', 'flash');
		$link_titles 	= $params->get('link_titles', $mainframe->getCfg('link_titles'));
		
		$list = modNewsFlash::getList($params, $access);
	
		// check if any results returned
		$items = count($list);
		if (!$items) {
			return;
		}
		
		switch ($style)
		{
			case 'horiz' :
				echo '<table class="moduletable'.$params->get('moduleclass_sfx').'">';
				echo '<tr>';
				foreach ($list as $item)
				{
					echo '<td>';
					modNewsFlash::renderItem($item, $params, $access);
					echo '</td>';
				}
				echo '</tr></table>';
			break;

			case 'vert' :
				foreach ($list as $item)
				{
					modNewsFlash::renderItem($row, $params, $access);
				}
				break;
			
			case 'flash' :
			default :
				srand((double) microtime() * 1000000);
				$flashnum = rand(0, $items -1);

				$item = $list[$flashnum];

				modNewsFlash::renderItem($item, $params, $access);
			break;
		}
	}
	
	function renderItem(&$item, &$params, &$access)
	{
		global $mainframe;

		$item->text 	= $item->introtext;
		$item->groups 	= '';
		$item->readmore = (trim($item->fulltext) != '');
		$item->metadesc = '';
		$item->metakey 	= '';
		$item->access 	= '';
		$item->created 	= '';
		$item->modified = '';

		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email'))
		{
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php
				// displays Item Title
				JContentHTMLHelper::title($item, $params, 0, $access);
			?>
			</tr>
			</table>
			<?php
		}

		if (!$params->get('intro_only'))
		{
			$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (&$item, &$params, 1));
			echo trim(implode("\n", $results));
		}

		$onBeforeDisplayContent = $mainframe->triggerEvent('onBeforeDisplayContent', array (&$item, &$params, 1));
		echo trim(implode("\n", $onBeforeDisplayContent));
		?>

		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<td valign="top" colspan="2">
		<?php
		// displays Item Text
		echo ampReplace($item->text);
		?>
			</td>
		</tr>
		</table>
		<span class="article_seperator">&nbsp;</span>
		<?php
	}
	
	function getList(&$params, &$access)
	{
		global $mainframe;

		$db 	=& $mainframe->getDBO();
		$user 	=& $mainframe->getUser();
	
		$catid 	= intval($params->get('catid'));
		$items 	= intval($params->get('items', 0));
		
		$noauth  = !$mainframe->getCfg('shownoauth');
	
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