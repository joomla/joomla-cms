<?php
/**
* @version $Id: admin.users.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Users
 */
class usersScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$lists, $search ) {

		$tmpl =& usersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'search', $search );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_type', $lists['type'] );
		$tmpl->addVar( 'body2', 'lists_logged', $lists['logged'] );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editUsers() {
		$tmpl =& usersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editUsers.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function massCreate( &$lists ) {
		$tmpl =& usersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'massCreate.html' );

		$tmpl->addVars( 'masscreate', $lists );

		$tmpl->displayParsedTemplate( 'masscreate' );
	}
}

/**
* @package Joomla
* @subpackage Users
*/
class HTML_users {

	function showUsers( &$rows, $pageNav, $search, $option, $lists ) {
		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="usersform" class="adminform">

		<?php
		usersScreens::view( $lists, $search );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="1%" class="title">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="1%" class="title">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Name' ), 'a.name' ); ?>
					</th>
					<th class="title" width="15%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Username' ), 'a.username' ); ?>
					</th>
					<th width="2%" class="title" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Logged In' ), 'loggedin' ); ?>
					</th>
					<th width="2%" class="title" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Enabled' ), 'a.block' ); ?>
					</th>
					<th width="2%" class="title" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'a.id' ); ?>
					</th>
					<th width="20%" class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Group' ), 'groupname' ); ?>
					</th>
					<th width="15%" class="title" nowrap="nowrap">
						<?php echo $_LANG->_( 'Last Visit' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="11" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="11" class="center">
						<?php echo $_LANG->_( 'Display Num' ) ?>
						<?php echo  $pageNav->getLimitBox() ?>
						<?php echo $pageNav->getPagesCounter() ?>
					</td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row 	=& $rows[$i];

					$img 	= $row->block ? 'publish_x.png' : 'tick.png';
					$task 	= $row->block ? 'unblock' : 'block';
					$alt 	= $row->block ? $_LANG->_( 'Enabled' ) : $_LANG->_( 'Blocked' );
					$link 	= 'index2.php?option=com_users&amp;task=editA&amp;id='. $row->id;

					$info 	= '<tr><td>'. $_LANG->_( 'Register Date' ) .':</td><td>'. mosFormatDate( $row->registerDate, $_LANG->_( 'DATE_FORMAT_LC3' ) ) .'</td></tr>';
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $i+1+$pageNav->limitstart;?>
						</td>
						<td>
							<?php echo mosHTML::idBox( $i, $row->id ); ?>
						</td>
						<td onmouseover="return overlib('<table><?php echo $info; ?></table>', CAPTION, '<?php echo $_LANG->_( 'User Information' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
							<a href="<?php echo $link; ?>" class="editlink">
								<?php echo $row->name; ?>
							</a>
						</td>
						<td>
							<?php echo $row->username; ?>
						</td>
						<td align="center">
							<?php echo $row->loggedin ? '<img src="images/tick.png" width="12" height="12" border="0" alt="'. $_LANG->_( 'Logged In' ) .'" title="'. $_LANG->_( 'Logged In' ) .'" />': ''; ?>
						</td>
						<td>
							<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
								<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>" />
							</a>
						</td>
						<td>
							<?php echo $row->id; ?>
						</td>
						<td nowrap="nowrap">
							<?php echo $row->groupname; ?>
						</td>
						<td>
							<?php echo mosFormatDate( $row->lastvisitDate, $_LANG->_( 'DATE_FORMAT_LC3' ) ); ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
				</table>
			</fieldset>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function edituser( &$row, &$contact, &$lists, $option, $uid, $params ) {
		global $my, $acl;
		global $mosConfig_live_site, $mosConfig_password_length;
	  	global $_LANG;

		$canBlockUser 	= $acl->acl_check( 'com_users', 'block_user', 'users', $my->usertype );
		$canEmailEvents = $acl->acl_check( 'com_users', 'email_events', 'users', $acl->get_group_name( $row->gid, 'ARO' ) );
		mosCommonHTML::loadOverlib();
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
			if ( trim( form.name.value) == "" ) {
				alert( "<?php echo $_LANG->_( 'validName' ); ?>" );
			} else if ( form.username.value == "" ) {
				alert( "<?php echo $_LANG->_( 'You must provide a user login name.' ); ?>" );
			} else if ( ( form.password.value != "" ) && ( r.exec( form.password.value ) || form.password.value.length < <?php echo $mosConfig_password_length; ?> ) ) {
				alert( "<?php echo $_LANG->_( 'WARNPASSWORDINVALIDORTOOSHORT' ); ?>" );
			} else if ( trim( form.email.value ) == "") {
				alert( "<?php echo $_LANG->_( 'You must provide an email address.' ); ?>" );
			} else if ( form.gid.value == "" ) {
				alert( "<?php echo $_LANG->_( 'You must assign user to a group.' ); ?>" );
			} else if ( trim( form.password.value ) != "" && form.password.value != form.password2.value ){
				alert( "<?php echo $_LANG->_( 'Password do not match.' ); ?>" );
			} else if ( form.gid.value == "29" ) {
				alert( "<?php echo $_LANG->_( "Please Select another group as 'Public Frontend' is not a selectable option" ); ?>" );
			} else if ( form.gid.value == "30" ) {
				alert( "<?php echo $_LANG->_( "Please Select another group as 'Public Backend' is not a selectable option" ); ?>" );
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
		<form action="index2.php" method="post" name="adminForm" id="userform" class="adminform">
		<?php
		usersScreens::editUsers();
		?>
		<table width="100%">
		<tr>
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'User Details' ); ?>
					</th>
				</tr>
				<tr>
					<td width="100">
					<label for="name"><?php echo $_LANG->_( 'Name' ); ?>:</label>
					</td>
					<td width="85%">
					<input type="text" name="name" id="name" class="inputbox" size="40" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<label for="username"><?php echo $_LANG->_( 'Username' ); ?>:</label>
					</td>
					<td>
					<input type="text" name="username" id="username" class="inputbox" size="40" value="<?php echo $row->username; ?>" />
					</td>
				<tr>
					<td>
					<label for="email"><?php echo $_LANG->_( 'Email' ); ?>:</label>
					</td>
					<td>
					<input class="inputbox" type="text" name="email" id="email" size="40" value="<?php echo $row->email; ?>" />
					</td>
				</tr>
				<tr>
					<td>
					<label for="password"><?php echo $_LANG->_( 'New Password' ); ?>:</label>
					</td>
					<td>
					<input class="inputbox" type="password" name="password" id="password" size="40" value="" />
					</td>
				</tr>
				<tr>
					<td>
					<label for="password2"><?php echo $_LANG->_( 'Verify Password' ); ?>:</label>
					</td>
					<td>
					<input class="inputbox" type="password" name="password2" id="password2" size="40" value="" />
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo $_LANG->_( 'Group' ); ?>:
					</td>
					<td>
					<?php echo $lists['gid']; ?>
					</td>
				</tr>
				<?php
				if ($canBlockUser) {
					?>
					<tr>
						<td>
						<?php echo $_LANG->_( 'Block User' ); ?></label>
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
						<td>
						<label for="sendEmail"><?php echo $_LANG->_( 'Receive Submission Emails' ); ?></label>
						</td>
						<td>
						<?php echo $lists['sendEmail']; ?>
						</td>
					</tr>
					<?php
				}
				if( $uid ) {
					?>
					<tr>
						<td>
						<?php echo $_LANG->_( 'Register Date' ); ?>
						</td>
						<td>
						<?php echo mosFormatDate( $row->registerDate, $_LANG->_( 'DATE_FORMAT_LC2' ) ); ?>
						</td>
					</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'Last Visit Date' ); ?>
					</td>
					<td>
					<?php echo mosFormatDate( $row->lastvisitDate, $_LANG->_( 'DATE_FORMAT_LC2' ) ); ?>
					</td>
				</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="2">&nbsp;

					</td>
				</tr>
				</table>
			</td>
			<td width="40%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="1">
					<?php echo $_LANG->_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php echo $params->render( 'params', 0 );?>
					</td>
				</tr>
				</table>
				<?php
				if ( !$contact ) {
					?>
					<table class="adminform">
					<tr>
						<th>
						<?php echo $_LANG->_( 'Contact Information' ); ?>
						</th>
					</tr>
					<tr>
						<td>
						<br />
						<?php echo $_LANG->_( 'No Contact details linked to this User' ); ?>:
						<br />
						<?php echo $_LANG->_( "SEECOMPCONTACTFORDETAILS" ); ?>
						<br /><br />
						</td>
					</tr>
					</table>
					<?php
				} else {
					?>
					<table class="adminform">
					<tr>
						<th colspan="2">
						<?php echo $_LANG->_( 'Contact Information' ); ?>
						</th>
					</tr>
					<tr>
						<td width="15%">
						<?php echo $_LANG->_( 'Name' ); ?>:
						</td>
						<td>
						<strong>
						<?php echo $contact[0]->name;?>
						</strong>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo $_LANG->_( 'Position' ); ?>:
						</td>
						<td >
						<strong>
						<?php echo $contact[0]->con_position;?>
						</strong>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo $_LANG->_( 'Telephone' ); ?>:
						</td>
						<td >
						<strong>
						<?php echo $contact[0]->telephone;?>
						</strong>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo $_LANG->_( 'Fax' ); ?>:
						</td>
						<td >
						<strong>
						<?php echo $contact[0]->fax;?>
						</strong>
						</td>
					</tr>
					<tr>
						<td></td>
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
							<td></td>
							<td valign="top">
							<img src="<?php echo $mosConfig_live_site;?>/images/stories/<?php echo $contact[0]->image; ?>" align="middle" alt="<?php echo $_LANG->_( 'Contact' ); ?>" />
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td colspan="2">
						<br /><br />
						<input class="button" type="button" value="<?php echo $_LANG->_( 'change Contact Details' ); ?>" onclick="javascript: gotocontact( '<?php echo $contact[0]->id; ?>' )">
						<i>
						<br />
						<?php echo $_LANG->_( "'Components -> Contact -> Manage Contacts'." ); ?>
						</i>
						</td>
					</tr>
					</table>
					<?php
				}
				?>
			</td>
		</tr>
		</table>
		</fieldset>
		</div>
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
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
