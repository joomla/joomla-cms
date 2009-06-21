<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$db = &JFactory::getDbo();
$query = 'SELECT menutype, COUNT(id) AS numitems'
. ' FROM #__menu'
. ' WHERE published = 1 and parent_id <> 0'
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
