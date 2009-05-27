<?php
/**
 * @version		$Id: mod_stats.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

$db = &JFactory::getDbo();
$query = 'SELECT menutype, COUNT(id) AS numitems'
. ' FROM #__menu'
. ' WHERE published = 1'
. ' GROUP BY menutype'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
?>
<table class="adminlist">
	<tr>
		<td class="title" width="80%">
			<strong><?php echo JText::_('Menu'); ?></strong>
		</td>
		<td class="title">
			<strong><?php echo JText::_('Num Items'); ?></strong>
		</td>
	</tr>
<?php
foreach ($rows as $row)
{
	$link = 'index.php?option=com_menus&amp;task=view&amp;menutype='. $row->menutype;
	?>
	<tr>
		<td>
			<a href="<?php echo $link; ?>">
				<?php echo $row->menutype;?></a>
		</td>
		<td>
			<?php echo $row->numitems;?>
		</td>
	</tr>
<?php
}
?>
</table>
