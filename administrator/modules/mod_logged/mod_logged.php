<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright		Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.pagination');

$db				=& JFactory::getDBO();
$user			=& JFactory::getUser();

// TODO -  pagination needs to be completed in module
$limit 			= $mainframe->getUserStateFromRequest( "limit", 'limit', $mainframe->getCfg('list_limit') );
$limitstart 		= $mainframe->getUserStateFromRequest( 'mod_logged.limitstart', 'limitstart', 0 );

// hides Administrator or Super Administrator from list depending on usertype
$and = '';
// administrator check
if ( $user->get('gid') == 24 ) {
	$and = ' AND gid != "25"';
}
// manager check
if ( $user->get('gid') == 23 ) {
	$and = ' AND gid != "25"';
	$and .= ' AND gid != "24"';
}

// get the total number of records
$query = 'SELECT COUNT(*)'
. ' FROM #__session'
. ' WHERE userid != 0'
. $and
. ' ORDER BY usertype, username'
;
$db->setQuery( $query );
$total = $db->loadResult();

// page navigation
$pageNav = new JPagination( $total, $limitstart, $limit );

$query = 'SELECT *'
. ' FROM #__session'
. ' WHERE userid != 0'
. $and
. ' ORDER BY usertype, username'
;
$db->setQuery( $query );
$rows = $db->loadObjectList();
?>
<table class="adminlist">
<tr>
	<td class="title">
		<strong><?php echo '#' ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_( 'Name' ); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_( 'Group' ); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_( 'Client' ); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_( 'Logout' ); ?></strong>
	</td>
</tr>
<?php
$i = 0;
foreach ( $rows as $row )
{
	if ( $user->authorize( 'com_users', 'manage' ) )
	{
		$link 	= 'index.php?option=com_users&amp;task=edit&amp;cid[]='. $row->userid;
		$name 	= '<a href="'. $link .'" title="'. JText::_( 'Edit User' ) .'">'. $row->username .'</a>';
	} else {
		$name 	= $row->username;
	}

	$clientInfo = JApplicationHelper::getClientInfo($row->client_id);
	?>
	<tr>
		<td width="5%">
		<?php echo $pageNav->getRowOffset( $i ); ?>
		</td>
		<td>
		<?php echo $name;?>
		</td>
		<td>
		<?php echo $row->usertype;?>
		</td>
		<td>
		<?php echo $clientInfo->name;?>
		</td>
		<?php
		if ( $user->authorize( 'com_users', 'manage' ) && $user->get('gid') > 24 && $row->userid != $user->get('id')) {
			?>
			<td>
			<a href="index.php?option=com_users&amp;task=logout&amp;cid[]=<?php echo $row->userid ?>&amp;client=<?php echo $row->client_id; ?>">
			<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Logout' ); ?>" title="<?php echo JText::_( 'Force Logout User' ); ?>" />
			</a>
			</td>
			<?php
		}
		else
		{
			?>
			<td>

			</td>
			<?php
		}
		?>
	</tr>
	<?php
	$i++;
}
?>
</table>
<input type="hidden" name="option" value="com_admin" />
