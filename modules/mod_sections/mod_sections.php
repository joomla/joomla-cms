<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/// no direct access
defined('_JEXEC') or die('Restricted access');

$db			= & $mainframe->getDBO();
$user		= & $mainframe->getUser();
$gid		= $user->get('gid');
$nullDate	= $db->getNullDate();
$menu		= JMenu::getInstance();

$count	= intval($params->get('count', 20));
$access	= !$mainframe->getCfg('shownoauth');
$now	= date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);

$query = "SELECT a.id AS id, a.title AS title, COUNT(b.id) as cnt" .
		"\n FROM #__sections as a" .
		"\n LEFT JOIN #__content as b ON a.id = b.sectionid" .
		($access ? "\n AND b.access <= $gid" : '') .
		"\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )" .
		"\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )" .
		"\n WHERE a.scope = 'content'" .
		"\n AND a.published = 1" .
		($access ? "\n AND a.access <= $gid" : '') .
		"\n GROUP BY a.id" .
		"\n HAVING COUNT( b.id ) > 0" .
		"\n ORDER BY a.ordering" .
		"\n LIMIT $count";
$db->setQuery($query);
$rows = $db->loadObjectList();

if ($rows)
{
?>
	<ul class="sections<?php echo $moduleclass_sfx; ?>">
	<?php
	foreach ($rows as $row)
	{
		$_Itemid = $menu->getItemid($row->id);
		if ($Itemid == $_Itemid) {
			$link = sefRelToAbs("index.php?option=com_content&task=blogsection&id=".$row->id);
		} else {
			$link = sefRelToAbs("index.php?option=com_content&task=blogsection&id=".$row->id."&Itemid=".$_Itemid);
		}

		// Render the list item
		?>
		<li>
			<a href="<?php echo $link;?>">
				<?php echo $row->title;?></a>
		</li>
		<?php
	}
	?>
	</ul>
	<?php
}
?>