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
$query = 'SELECT a.hits, a.id, a.sectionid, a.title, a.created, u.name'
. ' FROM #__content AS a'
. ' LEFT JOIN #__users AS u ON u.id=a.created_by'
. ' WHERE a.state <> -2'
. ' ORDER BY hits DESC'
;
$db->setQuery($query, 0, 10);
$rows = $db->loadObjectList();
?>

<table class="adminlist">
<tr>
	<td class="title">
		<strong><?php echo JText::_('Most Popular Items'); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_('Created'); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_('Hits'); ?></strong>
	</td>
</tr>
<?php
foreach ($rows as $row)
{
	$link = 'index.php?option=com_content&amp;task=edit&amp;id='. $row->id;
	?>
	<tr>
		<td>
			<a href="<?php echo $link; ?>">
				<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');?></a>
		</td>
		<td>
			<?php echo JHtml::_('date', $row->created, '%Y-%m-%d %H:%M:%S'); ?>
		</td>
		<td>
			<?php echo $row->hits;?>
		</td>
	</tr>
	<?php
}
?>
</table>
