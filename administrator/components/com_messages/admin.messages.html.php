<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Messages
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Messages
*/
class HTML_messages
{
	function showMessages( &$rows, &$pageNav, $option, &$lists )
	{
		// Initialize variables
		$user	=& JFactory::getUser();
		?>
		<form action="index.php?option=com_messages" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Search' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20" class="title">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
					</th>
					<th width="50%" class="title">
						<?php echo JHTML::_('grid.sort',   'Subject', 'a.subject', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="5%" class="title" align="center">
						<?php echo JHTML::_('grid.sort',   'Read', 'a.state', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="25%" class="title">
						<?php echo JHTML::_('grid.sort',   'From', 'user_from', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="15%" class="title" nowrap="nowrap" align="center">
						<?php echo JHTML::_('grid.sort',   'Date', 'a.date_time', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $pageNav->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row =& $rows[$i];
				$img = $row->state ? 'tick.png' : 'publish_x.png';
				$alt = $row->state ? JText::_( 'Read' ) : JText::_( 'Read' );

				if ( $user->authorize( 'com_users', 'manage' ) ) {
					$linkA 	= 'index.php?option=com_users&task=editA&id='. $row->user_id_from;
					$author = '<a href="'. JRoute::_( $linkA ) .'" title="'. JText::_( 'Edit User' ) .'">'. $row->user_from .'</a>';
				} else {
					$author = $row->user_from;
				}

				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $i+1+$pageNav->limitstart;?>
					</td>
					<td>
						<?php echo JHTML::_('grid.id', $i, $row->message_id ); ?>
					</td>
					<td>
						<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','view')">
							<?php echo $row->subject; ?></a>
					</td>
					<td align="center">
						<a href="javascript: void(0);">
							<img src="images/<?php echo $img;?>" width="16" height="16" border="0" alt="<?php echo $alt; ?>" /></a>
					</td>
					<td>
						<?php echo $author; ?>
					</td>
					<td>
						<?php echo $row->date_time; ?>
					</td>
				</tr>
				<?php $k = 1 - $k;
				}
			?>
			</tbody>
			</table>
		</div>

		<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
		</form>
		<?php
	}

	function editConfig( &$vars, $option) {
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'saveconfig') {
				if (confirm ("<?php echo JText::_( 'Are you sure?' ); ?>")) {
					submitform( pressbutton );
				}
			} else {
				document.location.href = 'index.php?option=<?php echo $option;?>';
			}
		}
		</script>
		<form action="index.php" method="post" name="adminForm">

		<div id="editcell">
			<table class="adminform">
			<tr>
				<td width="20%">
					<?php echo JText::_( 'Lock Inbox' ); ?>:
				</td>
				<td>
					<?php echo $vars['lock']; ?>
				</td>
			</tr>
			<tr>
				<td width="20%">
					<?php echo JText::_( 'Mail me on new Message' ); ?>:
				</td>
				<td>
					<?php echo $vars['mail_on_new']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_( 'Auto Purge Messages' ); ?>:
				</td>
				<td>
					<input type="text" name="vars[auto_purge]" size="5" value="<?php echo $vars['auto_purge']; ?>" class="inputbox" />
					<?php echo JText::_( 'days old' ); ?>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function viewMessage( &$row, $option ) {
		?>
		<form action="index.php" method="post" name="adminForm">

		<table class="adminform">
			<tr>
				<td width="100">
					<?php echo JText::_( 'From' ); ?>:
				</td>
				<td width="85%" bgcolor="#ffffff">
					<?php echo $row->user_from;?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_( 'Posted' ); ?>:
				</td>
				<td bgcolor="#ffffff">
					<?php echo $row->date_time;?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_( 'Subject' ); ?>:
				</td>
				<td bgcolor="#ffffff">
					<?php echo $row->subject;?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo JText::_( 'Message' ); ?>:
				</td>
				<td width="100%" bgcolor="#ffffff">
					<pre><?php echo htmlspecialchars( $row->message, ENT_COMPAT, 'UTF-8' );?></pre>
				</td>
			</tr>
		</table>

		<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="1" />
		<input type="hidden" name="cid[]" value="<?php echo $row->message_id; ?>" />
		<input type="hidden" name="userid" value="<?php echo $row->user_id_from; ?>" />
		<input type="hidden" name="subject" value="Re: <?php echo $row->subject; ?>" />
		</form>
		<?php
	}

	function newMessage($option, $recipientslist, $subject )
	{
		$user =& JFactory::getUser();
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
		<form action="index.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td width="100">
				<?php echo JText::_( 'To' ); ?>:
			</td>
			<td width="85%">
				<?php echo $recipientslist; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Subject' ); ?>:
			</td>
			<td>
				<input type="text" name="subject" size="50" maxlength="100" class="inputbox" value="<?php echo $subject; ?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'Message' ); ?>:
			</td>
			<td width="100%">
				<textarea name="message" style="width:95%" rows="30" class="inputbox"></textarea>
			</td>
		</tr>
		</table>

		<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
		<input type="hidden" name="user_id_from" value="<?php echo $user->get('id'); ?>">
		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="task" value="">
		</form>
		<?php
	}
}