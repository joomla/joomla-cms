<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Messages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Messages
*/
class HTML_messages {
	function showMessages( &$rows, $pageNav, $search, $option ) {
?>
<form action="index2.php" method="post" name="adminForm">
  <table class="adminheading">
	<tr>
	  <th class="inbox"><?php echo JText::_( 'Private Messaging' ); ?></th>
	  <td><?php echo JText::_( 'Search' ); ?>:</td>
	  <td> <input type="text" name="search" value="<?php echo $search;?>" class="inputbox" onChange="document.adminForm.submit();" />
	  </td>
	</tr>
  </table>
  <table class="adminlist">
	<tr>
	  <th width="20"><?php echo JText::_( 'NUM' ); ?></th>
	  <th width="5%" class="title"> <input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count($rows); ?>);" />
	  </th>
	  <th width="60%" class="title"><?php echo JText::_( 'Subject' ); ?></th>
	  <th width="15%" class="title"><?php echo JText::_( 'From' ); ?></th>
	  <!-- <th width="20%" class="title">UserType</th> -->
	  <th width="15%" class="title"><?php echo JText::_( 'Date' ); ?></th>
	  <th width="5%" class="title"><?php echo JText::_( 'Read' ); ?></th>
	</tr>
<?php
$k = 0;
for ($i=0, $n=count( $rows ); $i < $n; $i++) {
	$row =& $rows[$i];
?>
	<tr class="<?php echo "row$k"; ?>">
	  <td width="20"><?php echo $i+1+$pageNav->limitstart;?></td>
	  <td width="5%"><?php echo mosHTML::idBox( $i, $row->message_id ); ?></td>
	  <td width="60%"> <a href="#edit" onClick="hideMainMenu();return listItemTask('cb<?php echo $i;?>','view')">
		<?php echo $row->subject; ?> </a> </td>
	  <td width="15%"><?php echo $row->user_from; ?></td>
	  <td width="15%"><?php echo $row->date_time; ?></td>
	  <td width="15%"><?php
	  if (intval( $row->state ) == "1") {
	  	echo JText::_( 'Read' );
	  } else {
	  	echo JText::_( 'Unread' );
	  } ?></td>
	</tr>
	<?php $k = 1 - $k;
			} ?>
	</table>
	<?php echo $pageNav->getListFooter(); ?>
  <input type="hidden" name="option" value="<?php echo $option;?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="hidemainmenu" value="0" />
</form>
<?php }

function editConfig( &$vars, $option) {
	$tabs = new mosTabs(0);
?>
<table class="adminheading">
  <tr>
	<th class="msgconfig"><?php echo JText::_( 'Private Messaging Configuration' ); ?></th>
  </tr>
</table>
<?php
$title = JText::_( 'General' );
$tabs->startPane("messages");
$tabs->startTab( $title, "general-page" );
?>
<form action="index2.php" method="post" name="adminForm">
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'saveconfig') {
		if (confirm ("<?php echo JText::_( 'Are you sure?' ); ?>")) {
			submitform( pressbutton );
		}
	} else {
		document.location.href = 'index2.php?option=<?php echo $option;?>';
	}
}
</script>


	<table class="adminform">
	  <tr>
		<td width="20%"><?php echo JText::_( 'Lock Inbox' ); ?>:</td>
		<td> <?php echo $vars['lock']; ?> </td>
	  </tr>
	  <tr>
		<td width="20%"><?php echo JText::_( 'Mail me on new Message' ); ?>:</td>
		<td> <?php echo $vars['mail_on_new']; ?> </td>
	  </tr>
	</table>


<?php
$tabs->endTab();
$tabs->endPane();
?>  <input type="hidden" name="option" value="<?php echo $option; ?>">
  <input type="hidden" name="task" value="">
</form>
<?php }

function viewMessage( &$row, $option ) {
?>
	<table class="adminheading">
		<tr>
			<th class="inbox"><?php echo JText::_( 'View Private Message' ); ?></th>
		</tr>
	</table>

	<form action="index2.php" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="100"><?php echo JText::_( 'From' ); ?>:</td>
			<td width="85%" bgcolor="#ffffff"><?php echo $row->user_from;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_( 'Posted' ); ?>:</td>
			<td bgcolor="#ffffff"><?php echo $row->date_time;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_( 'Subject' ); ?>:</td>
			<td bgcolor="#ffffff"><?php echo $row->subject;?></td>
		</tr>
		<tr>
			<td valign="top"><?php echo JText::_( 'Message' ); ?>:</td>
			<td width="100%" bgcolor="#ffffff"><pre><?php echo htmlspecialchars( $row->message );?></pre></td>
		</tr>
	</table>
	<input type="hidden" name="option" value="<?php echo $option;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="cid[]" value="<?php echo $row->message_id; ?>" />
	<input type="hidden" name="userid" value="<?php echo $row->user_id_from; ?>" />
	<input type="hidden" name="subject" value="Re: <?php echo $row->subject; ?>" />
	<input type="hidden" name="hidemainmenu" value="0" />
	</form>
<?php }

function newMessage($option, $recipientslist, $subject ) {
	global $my;
?>
	<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		// do field validation
		if (form.subject.value == "") {
			alert( "<?php echo JText::_( 'You must provide a subject.' ); ?>" );
		} else if (form.message.value == "") {
			alert( "<?php echo JText::_( 'You must provide a message.' ); ?>" );
		} else if (getSelectedValue('adminForm','user_id_to') < 1) {
			alert( "<?php echo JText::_( 'You must select a recipient.' ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
	</script>

	<table class="adminheading">
		<tr>
			<th class="inbox"><?php echo JText::_( 'New Private Message' ); ?></th>
		</tr>
	</table>

	<form action="index2.php" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="100"><?php echo JText::_( 'To' ); ?>:</td>
			<td width="85%"><?php echo $recipientslist; ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_( 'Subject' ); ?>:</td>
			<td>
				<input type="text" name="subject" size="50" maxlength="100" class="inputbox" value="<?php echo $subject; ?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top"><?php echo JText::_( 'Message' ); ?>:</td>
			<td width="100%">
				<textarea name="message" style="width:100%" rows="30" class="inputbox"></textarea>
			</td>
		</tr>
	</table>
	<input type="hidden" name="user_id_from" value="<?php echo $my->id; ?>">
	<input type="hidden" name="option" value="<?php echo $option; ?>">
	<input type="hidden" name="task" value="">
	</form>
<?php }

}?>