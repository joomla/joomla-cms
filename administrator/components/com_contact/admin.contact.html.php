<?php
/**
* @version $Id: admin.contact.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Contact
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
 * @subpackage Contact
 */
class contactsScreens {
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
	function view( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& contactsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_catid', $lists['catid'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editContacts() {
		global $mosConfig_lang;

		$tmpl =& contactsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editContacts.html' );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Contact
*/
class HTML_contact {

	function showContacts( &$rows, &$pageNav, $option, &$lists ) {
		global $my;
		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="contactsform" class="adminform">

		<?php
		contactsScreens::view( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10" class="title">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Name' ), 'cd.name' ); ?>
					</th>
					<th width="18%" align="left">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category' ), 'category' ); ?>
					</th>
					<th class="title" nowrap="nowrap" width="18%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Linked to User' ), 'user' ); ?>
					</th>
					<th width="5%" class="title" nowrap="true">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'cd.published' ); ?>
					</th>
					<th colspan="2" nowrap="nowrap" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'cd.ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th nowrap="nowrap" width="20" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'cd.id' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="11" align="center">
							<?php echo $pageNav->getPagesLinks(); ?>
						</th>
					</tr>
					<tr>
						<td colspan="11" align="center">
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
					$row = $rows[$i];

					$link 	= 'index2.php?option=com_contact&amp;task=editA&amp;id='. $row->id;

					$img 	= $row->published ? 'tick.png' : 'publish_x.png';
					$task 	= $row->published ? 'unpublish' : 'publish';
					$alt 	= $row->published ? 'Published' : 'Unpublished';

					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );

					$row->cat_link 	= 'index2.php?option=com_categories&amp;section=com_contact_details&amp;task=editA&amp;id='. $row->catid;
					$row->user_link	= 'index2.php?option=com_users&amp;task=editA&amp;id='. $row->user_id;
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="10">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td width="10">
							<?php echo $checked; ?>
						</td>
						<td>
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								echo $row->name;
							} else {
								?>
								<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Contact' ); ?>" class="editlink">
									<?php echo $row->name; ?>
								</a>
								<?php
							}
							?>
						</td>
						<td>
							<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>" class="editlink">
								<?php echo $row->category; ?>
							</a>
						</td>
						<td>
							<?php
							if ( $row->user ) {
								?>
								<a href="<?php echo $row->user_link; ?>" title="<?php echo $_LANG->_( 'Edit User' ); ?>" class="editlink">
									<?php echo $row->user; ?>
								</a>
								<?php
							} else {
								?>
								-
								<?php
							}
							?>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
								<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
							</a>
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'category' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i, ( $row->catid == @$rows[$i-1]->catid ) );
							}
							?>
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'category' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderDownIcon( $i, $n, ( $row->catid == @$rows[$i+1]->catid ) );
							}
							?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<td align="center">
							<?php echo $row->id; ?>
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
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}


	function editContact( &$row, &$lists, $option, &$params ) {
		global $mosConfig_live_site;
		global $_LANG;

		if ($row->image == '') {
			$row->image = 'blank.png';
		}

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'misc' );
		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if ( form.name.value == "" ) {
				alert( "<?php echo $_LANG->_( 'validName' ); ?>" );
			} else if ( form.catid.value == 0 ) {
				alert( "<?php echo $_LANG->_( 'Please select a Category.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		contactsScreens::editContacts( $lists );
		?>
		<table width="100%">
		<tr>
			<td width="60%" valign="top">
				<table width="100%" class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Contact Details' ); ?>
					</th>
				<tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Category' ); ?>:
					</td>
					<td width="40%">
					<?php echo $lists['catid'];?>
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Linked to User' ); ?>:
					</td>
					<td >
					<?php echo $lists['user_id'];?>
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Name' ); ?>:
					</td>
					<td >
					<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( "Contact's Position" ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="con_position" size="50" maxlength="50" value="<?php echo $row->con_position; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Email' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="email_to" size="50" maxlength="100" value="<?php echo $row->email_to; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Street Address' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="address" size="50" value="<?php echo $row->address; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Town/Suburb' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="suburb" size="50" maxlength="50" value="<?php echo $row->suburb;?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'State/County' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="state" size="50" maxlength="20" value="<?php echo $row->state;?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Country' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="country" size="50" maxlength="50" value="<?php echo $row->country;?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Postal Code/ZIP' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="postcode" size="25" maxlength="10" value="<?php echo $row->postcode; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Telephone' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="telephone" size="25" maxlength="25" value="<?php echo $row->telephone; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Fax' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="fax" size="25" maxlength="25" value="<?php echo $row->fax; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					<?php echo $_LANG->_( 'Miscellaneous Info' ); ?>:
					</td>
					<td>
					<textarea name="misc" rows="5" cols="50" class="inputbox"><?php echo $row->misc; ?></textarea>
					</td>
				</tr>
				<tr>
				</table>

				<table width="100%" class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Publishing Info' ); ?>
					</th>
				<tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Ordering' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Access' ); ?>:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					</td>
				</tr>
				</table>

				<table width="100%" class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Image Info' ); ?>
					</th>
				<tr>
				<tr>
					<td align="left" width="20%">
					<?php echo $_LANG->_( 'Image' ); ?>:
					</td>
					<td align="left">
					<?php echo $lists['image']; ?>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
					<script language="javascript" type="text/javascript">
					if (document.forms[0].image.options.value!=''){
						jsimg='../images/stories/' + getSelectedValue( 'adminForm', 'image' );
					} else {
						jsimg='../images/M_images/blank.png';
					}
					document.write('<img src=' + jsimg + ' name="imagelib" width="100" height="100" border="2" alt="<?php echo $_LANG->_( 'Preview' ); ?>" />');
					</script>
					</td>
				</tr>
				</table>

			</td>
			<td width="40%" valign="top">
				<table class="adminform">
				<tr>
					<th>
					<?php echo $_LANG->_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php echo $_LANG->_( 'DESCPARAMCONTROLWHATSEEWHENCLICKCONTACTITEM' ); ?>
					<br /><br />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $params->render( 'params', 0 );?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		<fieldset>
	</div>

		<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>