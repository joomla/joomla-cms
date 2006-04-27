<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
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

/**
* @package Joomla
* @subpackage Users
*/
class HTML_users {

	/**
	 * Display list of users
	 */
	function showUsers( &$rows, &$page, $option, &$lists ) {
		global $mainframe;

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
		$user =& $mainframe->getUser();

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_users" method="post" name="adminForm">

		<div id="pane-document">

		<table>
			<tr>
				<td width="100%">
					<?php echo JText::_( 'Filter' ); ?>:
					<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
					<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
				</td>
				<td nowrap="nowrap">
					<?php echo $lists['type'];?>
					<?php echo $lists['logged'];?>
				</td>
			</tr>
		</table>

		<table class="adminlist" cellpadding="1">
			<thead>
				<tr>
					<th width="2%" class="title">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="3%" class="title">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
					</th>
					<th class="title">
						<?php mosCommonHTML::tableOrdering( 'Name', 'a.name', $lists ); ?>
					</th>
					<th width="15%" class="title" >
						<?php mosCommonHTML::tableOrdering( 'Username', 'a.username', $lists ); ?>
					</th>
					<th width="5%" class="title" nowrap="nowrap">
						<?php echo JText::_( 'Logged In' ); ?>
					</th>
					<th width="5%" class="title" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Enabled', 'a.block', $lists ); ?>
					</th>
					<th width="15%" class="title">
						<?php mosCommonHTML::tableOrdering( 'Group', 'groupname', $lists ); ?>
					</th>
					<th width="15%" class="title">
						<?php mosCommonHTML::tableOrdering( 'E-Mail', 'a.email', $lists ); ?>
					</th>
					<th width="10%" class="title">
						<?php mosCommonHTML::tableOrdering( 'Last Visit', 'a.lastvisitDate', $lists ); ?>
					</th>
					<th width="1%" class="title" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'ID', 'a.id', $lists ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="10">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row 	=& $rows[$i];

				$img 	= $row->block ? 'publish_x.png' : 'tick.png';
				$task 	= $row->block ? 'unblock' : 'block';
				$alt 	= $row->block ? JText::_( 'Enabled' ) : JText::_( 'Blocked' );
				$link 	= 'index2.php?option=com_users&amp;task=edit&amp;cid[]='. $row->id. '&amp;hidemainmenu=1';
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $i+1+$page->limitstart;?>
					</td>
					<td>
						<?php echo mosHTML::idBox( $i, $row->id ); ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?></a>
					</td>
					<td>
						<?php echo $row->username; ?>
					</td>
					<td align="center">
						<?php echo $row->loggedin ? '<img src="images/tick.png" width="12" height="12" border="0" alt="" />': ''; ?>
					</td>
					<td align="center">
						<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
							<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" /></a>
					</td>
					<td>
						<?php echo $row->groupname; ?>
					</td>
					<td>
						<a href="mailto:<?php echo $row->email; ?>">
							<?php echo $row->email; ?></a>
					</td>
					<td nowrap="nowrap">
						<?php echo mosFormatDate( $row->lastvisitDate, JText::_( 'DATE_FORMAT_LC4' ) ); ?>
					</td>
					<td>
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	/**
	 * Form for editing a user
	 */
	function edituser( &$user, &$contact, &$lists, $option )
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$acl	= & JFactory::getACL();
		$tabs	= new mosTabs( 0 );

		mosCommonHTML::loadOverlib();
		$canBlockUser 	= $user->authorize( 'com_user', 'block user' );
		$canEmailEvents = $acl->acl_check( 'workflow', 'email_events', 'users', $acl->get_group_name( $user->get('gid'), 'ARO' ) );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

			// do field validation
			if (trim(form.name.value) == "") {
				alert( "<?php echo JText::_( 'You must provide a name.', true ); ?>" );
			} else if (form.username.value == "") {
				alert( "<?php echo JText::_( 'You must provide a user login name.', true ); ?>" );
			} else if (r.exec(form.username.value) || form.username.value.length < 3) {
				alert( "<?php echo JText::_( 'WARNLOGININVALID', true ); ?>" );
			} else if (trim(form.email.value) == "") {
				alert( "<?php echo JText::_( 'You must provide an email address.', true ); ?>" );
			} else if (form.gid.value == "") {
				alert( "<?php echo JText::_( 'You must assign user to a group.', true ); ?>" );
			} else if (trim(form.password.value) != "" && form.password.value != form.password2.value){
				alert( "<?php echo JText::_( 'Password do not match.', true ); ?>" );
			} else if (form.gid.value == "29") {
				alert( "<?php echo JText::_( 'WARNSELECTPF', true ); ?>" );
			} else if (form.gid.value == "30") {
				alert( "<?php echo JText::_( 'WARNSELECTPB', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}

		function gotocontact( id ) {
			var form = document.adminForm;
			form.contact_id.value = id;
			submitform( 'contact' );
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		
		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'User Details' ); ?></legend>

				<table class="admintable" cellspacing="1">
		


					<tr>
						<td width="150" class="key">
							<label for="name">
								<?php echo JText::_( 'Name' ); ?>
							</label>
						</td>
						<td>
							<input type="text" name="name" id="name" class="inputbox" size="40" value="<?php echo $user->get('name'); ?>" maxlength="50" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="username">
								<?php echo JText::_( 'Username' ); ?>
							</label>
						</td>
						<td>
							<input type="text" name="username" id="username" class="inputbox" size="40" value="<?php echo $user->get('username'); ?>" maxlength="25" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="email">
								<?php echo JText::_( 'Email' ); ?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="email" id="email" size="40" value="<?php echo $user->get('email'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="password">
								<?php echo JText::_( 'New Password' ); ?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="password" name="password" id="password" size="40" value="" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="password2">
								<?php echo JText::_( 'Verify Password' ); ?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="password" name="password2" id="password2" size="40" value="" />
						</td>
					</tr>
					<tr>
						<td valign="top" class="key">
							<label for="gid">
								<?php echo JText::_( 'Group' ); ?>
							</label>
						</td>
						<td>
							<?php echo $lists['gid']; ?>
						</td>
					</tr>
					<?php
					if ($canBlockUser) {
						?>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Block User' ); ?>
							</td>
							<td>
								<?php echo $lists['block']; ?>
							</td>
						</tr>
						<?php
					}
					if ($canEmailEvents) {
						?>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Receive System Emails' ); ?>
							</td>
							<td>
								<?php echo $lists['sendEmail']; ?>
							</td>
						</tr>
						<?php
					}
					if( $user->get('id') ) {
						?>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Register Date' ); ?>
							</td>
							<td>
								<?php echo $user->get('registerDate');?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Last Visit Date' ); ?>
							</td>
							<td>
								<?php echo $user->get('lastvisitDate');?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				
			</div>
			<div class="col50">
				<fieldset class="adminform">
				<legend><?php echo JText::_( 'Parameters' ); ?></legend>
					<table class="admintable">
						<tr>
							<td>
								<?php
									$params = $user->getParameters();
									$params->loadSetupFile(JApplicationHelper::getPath( 'com_xml', 'com_users' ));
									echo $params->render( 'params' );
								?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'Contact Information' ); ?></legend>
					<?php
					if ( !$contact ) {
						?>
						
						<table class="admintable">
						<tr>
							<td>
								<br />
								<?php echo JText::_( 'No Contact details linked to this User' ); ?>:
								<br />
								<?php echo JText::_( 'SEECOMPCONTACTFORDETAILS' ); ?>.
								<br /><br />
							</td>
						</tr>
						</table>
						<?php
					} else {
						?>
						<table class="admintable">
						<tr>
							<td width="120" class="key">
								<?php echo JText::_( 'Name' ); ?>
							</td>
							<td>
							<strong>
								<?php echo $contact[0]->name;?>
							</strong>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Position' ); ?>:
							</td>
							<td >
								<strong>
									<?php echo $contact[0]->con_position;?>
								</strong>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Telephone' ); ?>
							</td>
							<td >
								<strong>
									<?php echo $contact[0]->telephone;?>
								</strong>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'Fax' ); ?>
							</td>
							<td >
								<strong>
									<?php echo $contact[0]->fax;?>
								</strong>
							</td>
						</tr>
						<tr>
							<td class="key">&nbsp;</td>
							<td >
								<strong>
									<?php echo $contact[0]->misc;?>
								</strong>
							</td>
						</tr>
						<?php
						if ($contact[0]->image) {
							?>
							<tr>
								<td class="key">&nbsp;</td>
								<td valign="top">
									<img src="<?php echo $mainframe->getSiteURL();?>/images/stories/<?php echo $contact[0]->image; ?>" align="middle" alt="<?php echo JText::_( 'Contact' ); ?>" />
								</td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td class="key">&nbsp;</td>
							<td>
								<div >
									<br />
									<input class="button" type="button" value="<?php echo JText::_( 'change Contact Details' ); ?>" onclick="javascript: gotocontact( '<?php echo $contact[0]->id; ?>' )" />
									<i>
									<br /><br />
									'<?php echo JText::_( 'Components -> Contact -> Manage Contacts' ); ?>'
									</i>
								</div>
							</td>
						</tr>
					</table>
					<?php
				}
				?>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="id" value="<?php echo $user->get('id'); ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $user->get('id'); ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="contact_id" value="" />
		<?php
		if (!$canEmailEvents) {
			?>
			<input type="hidden" name="sendEmail" value="0" />
			<?php
		}
		?>
		</form>
		<?php
	}
}
?>