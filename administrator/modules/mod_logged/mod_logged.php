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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.presentation.pagination');

$user			= & $mainframe->getUser();
$limit 			= $mainframe->getUserStateFromRequest( "limit", 'limit', $mainframe->getCfg('list_limit') );
$limitstart 	= $mainframe->getUserStateFromRequest( "$option.limitstart", 'limitstart', 0 );

// hides Administrator or Super Administrator from list depending on usertype
$and = '';
// administrator check
if ( $my->gid == 24 ) {
	$and = "\n AND gid != '25'";
}
// manager check
if ( $my->gid == 23 ) {
	$and = "\n AND gid != '25'";
	$and .= "\n AND gid != '24'";
}

// get the total number of records
$query = "SELECT COUNT(*)"
. "\n FROM #__session"
. "\n WHERE userid != 0"
. $and
. "\n ORDER BY usertype, username"
;
$database->setQuery( $query );
$total = $database->loadResult();

// page navigation
$pageNav = new JPagination( $total, $limitstart, $limit );

$query = "SELECT *"
. "\n FROM #__session"
. "\n WHERE userid != 0"
. $and
. "\n ORDER BY usertype, username"
;
$database->setQuery( $query );
$rows = $database->loadObjectList();
?>
<table class="adminlist">
<?php
$i = 0;
foreach ( $rows as $row ) 
{
	if ( $user->authorize( 'com_users', 'manage' ) ) 
	{
		$link 	= 'index2.php?option=com_users&amp;task=editA&amp;hidemainmenu=1&amp;id='. $row->userid;
		$name 	= '<a href="'. $link .'" title="'. JText::_( 'Edit User' ) .'">'. $row->username .'</a>';
	} else {
		$name 	= $row->username;
	}
	
	$clientInfo = JApplicationHelper::getClientInfo($row->client_id);
	?>
	<tr>
		<td width="5%">
		<?php echo $pageNav->rowNumber( $i ); ?>
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
		if ( $user->authorize( 'com_users', 'manage' ) ) {
			?>
			<td>
			<a href="index2.php?option=com_users&amp;task=flogout&amp;id=<?php echo $row->userid ?>&amp;client=<?php echo $row->client_id; ?>">
			<img src="images/publish_x.png" width="12" height="12" border="0" alt="<?php echo JText::_( 'Logout' ); ?>" title="<?php echo JText::_( 'Force Logout User' ); ?>" />
			</a>
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