<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Get the user object for the logged in user
$db		= &JFactory::getDbo();
$user	= &JFactory::getUser();
$userId	= (int) $user->get('id');

$where	= 'WHERE a.state <> -2';

// User Filter
switch ($params->get('user_id'))
{
	case 'by_me':
		$where .= ' AND (created_by = ' . (int) $userId . ' OR modified_by = ' . (int) $userId . ')';
		break;
	case 'not_me':
		$where .= ' AND (created_by <> ' . (int) $userId . ' AND modified_by <> ' . (int) $userId . ')';
		break;
}

// Ordering
switch ($params->get('ordering'))
{
	case 'm_dsc':
		$ordering		= 'modified DESC, created DESC';
		$dateProperty	= 'modified';
		break;
	case 'c_dsc':
	default:
		$ordering		= 'created DESC';
		$dateProperty	= 'created';
		break;
}

$query = 'SELECT a.id, a.sectionid, a.title, a.created, a.modified, u.name, a.created_by_alias, a.created_by'
. ' FROM #__content AS a'
. ' LEFT JOIN #__users AS u ON u.id = a.created_by'
. ' '. $where
. ' ORDER BY '. $ordering
;
$db->setQuery($query, 0, 10);
$rows = $db->loadObjectList();
?>

<table class="adminlist">
<thead>
<tr>
	<th>
		<?php echo JText::_('Latest Items'); ?>
	</th>
	<th>
		<strong><?php echo JText::_('Created'); ?></strong>
	</th>
	<th>
		<strong><?php echo JText::_('Creator'); ?></strong>
	</th>
</tr>
</thead>
<?php
if (count($rows))
{
	foreach ($rows as $row)
	{
		$link = 'index.php?option=com_content&amp;task=edit&amp;id='. $row->id;

		if ($user->authorize('core.manage', 'com_users')) {
			if ($row->created_by_alias)
			{
				$author = $row->created_by_alias;
			}
			else
			{
				$linkA 	= 'index.php?option=com_users&amp;task=edit&amp;cid[]='. $row->created_by;
				$author = '<a href="'. $linkA .'" title="'. JText::_('Edit User') .'">'. htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') .'</a>';
			}
		}
		else
		{
			if ($row->created_by_alias)
			{
				$author = $row->created_by_alias;
			}
			else
			{
				$author = htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');
			}
		}
		?>
		<tr>
			<td>
				<a href="<?php echo $link; ?>">
					<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');?></a>
			</td>
			<td class="center">
				<?php echo JHtml::_('date', $row->$dateProperty, '%Y-%m-%d %H:%M:%S'); ?>
			</td>
			<td class="center">
				<?php echo $author;?>
			</td>
		</tr>
		<?php
	}
}
else
{
?>
		<tr>
			<td colspan="3">
				<p class="noresults"><?php echo JText::_('No matching results');?></p>
			</td>
		</tr>
<?php
}
?>
</table>
