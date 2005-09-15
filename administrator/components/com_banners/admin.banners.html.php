<?php
/**
* @version $Id: admin.banners.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Banners
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
 * @subpackage Banners
 */
class bannersScreens {
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
	* @param array
	*/
	function viewBanners( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& bannersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'viewbanners.html' );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	/**
	* @param array
	*/
	function viewClients( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& bannersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'viewclients.html' );

		//$tmpl->addVar( 'body2', 'search', $lists['search'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editBanners() {
		global $mosConfig_lang;

		$tmpl =& bannersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editBanners.html' );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editClients() {
		global $mosConfig_lang;

		$tmpl =& bannersScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editClients.html' );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Banners
*/
class HTML_banners {

	function showBanners( &$rows, &$pageNav, $option, &$lists ) {
		global $my;
	   global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="bannerform" class="adminform">

		<?php
		bannersScreens::viewBanners( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
					<input type="checkbox" name="toggle" value=""  />
					</th>
					<th align="left" nowrap="nowrap" >
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Banner Name' ), 'b.name' ); ?>
					</th>
					<th width="10%" nowrap="nowrap" >
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'b.showBanner' ); ?>
					</th>
					<th width="8%" nowrap="nowrap" >
						<?php echo $_LANG->_( 'Date' ); ?>
					</th>
					<th width="8%" nowrap="nowrap" class="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Impressions Made' ), 'b.impmade' ); ?>
					</th>
					<th width="8%" nowrap="nowrap" class="center">
						<?php echo $_LANG->_( 'Impressions Left' ); ?>
					</th>
					<th width="5%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Clicks' ), 'b.clicks' ); ?>
					</th>
					<th width="5%" nowrap="nowrap" >
						<?php echo $_LANG->_( '% Clicks' ); ?>
					</th>
					<th width="2%" nowrap="nowrap" >
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'b.bid' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="10" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="10" class="center">
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
					$row = &$rows[$i];

					$row->id 	= $row->bid;
					$link 		= 'index2.php?option=com_banners&amp;task=editA&amp;id='. $row->id;

					$impleft 	= $row->imptotal - $row->impmade;
					if( $impleft < 0 ) {
						$impleft 	= "unlimited";
					}

					if ( $row->impmade != 0 ) {
						$percentClicks = substr(100 * $row->clicks/$row->impmade, 0, 5);
					} else {
						$percentClicks = 0;
					}

					$task 	= $row->showBanner ? 'unpublish' : 'publish';
					$img 	= $row->showBanner ? 'tick.png' : 'publish_x.png';
					$alt 	= $row->showBanner ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );

					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );

					$date = mosFormatDate( $row->date, '%x' );
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td align="center">
							<?php echo $checked; ?>
						</td>
						<td align="left">
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								echo $row->name;
							} else {
								?>
								<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Banner' ); ?>" class="editlink">
									<?php echo $row->name; ?>
								</a>
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
							<?php echo $date; ?>
						</td>
						<td class="center">
							<?php echo $row->impmade;?>
						</td>
						<td class="center">
							<?php echo $impleft;?>
						</td>
						<td align="center">
							<?php echo $row->clicks;?>
						</td>
						<td align="center">
							<?php echo $percentClicks;?>
						</td>
						<td align="center">
							<?php echo $row->bid;?>
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

	function bannerForm( &$_row, &$lists, $_option ) {
		global $_LANG;

	   mosMakeHtmlSafe( $_row, ENT_QUOTES, 'custombannercode' );
		?>
		<script language="javascript">
		<!--
		function changeDisplayImage() {
			if (document.adminForm.imageurl.value !='') {
				document.adminForm.imagelib.src='../images/banners/' + document.adminForm.imageurl.value;
			} else {
				document.adminForm.imagelib.src='images/blank.png';
			}
		}
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo $_LANG->_( 'You must provide a banner name.' ); ?>" );
			} else if (getSelectedValue('adminForm','cid') < 1) {
				alert( "<?php echo $_LANG->_( 'Please select a client.' ); ?>" );
			} else if (!getSelectedValue('adminForm','imageurl')) {
				alert( "<?php echo $_LANG->_( 'Please select an image.' ); ?>" );
			} else if (form.clickurl.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the URL for the banner.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		bannersScreens::editBanners();
		?>

			<table class="adminform" id="editpage">
			<thead>
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Details' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="2">
				</th>
			</tr>
			</tfoot>

			<tr>
				<td width="200">
					<?php echo $_LANG->_( 'Banner Name' ); ?>:
				</td>
				<td>
					<input class="inputbox" type="text" name="name" size="50" value="<?php echo $_row->name;?>">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Client Name' ); ?>:
				</td>
				<td align="left">
					<?php echo $lists['cid']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Impressions Purchased' ); ?>:
				</td>
				<?php
				if ($_row->imptotal == '0') {
					$unlimited = 'checked';
					$_row->imptotal = '';
				} else {
					$unlimited = '';
				}
				?>
				<td>
					<input class="inputbox" type="text" name="imptotal" size="15" maxlength="11" value="<?php echo $_row->imptotal;?>">&nbsp;<?php echo $_LANG->_( 'Unlimited' ); ?> <input type="checkbox" name="unlimited" <?php echo $unlimited;?> />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $_LANG->_( 'Banner URL' ); ?>:
				</td>
				<td align="left">
					<?php echo $lists['imageurl']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Show Banner' ); ?>:
				</td>
				<td>
					<?php echo $lists['showBanner']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Click URL' ); ?>:
				</td>
				<td>
					<input class="inputbox" type="text" name="clickurl" size="50" maxlength="200" value="<?php echo $_row->clickurl;?>" />
				</td>
			</tr>
			<tr >
				<td valign="top" align="right">
					<?php echo $_LANG->_( 'Clicks' ); ?>
					<br/>
					<input name="reset_hits" type="button" class="button" value="<?php echo $_LANG->_( 'Reset Clicks' ); ?>" onClick="submitbutton('resethits');" />
				</td>
				<td colspan="2">
					<?php echo $_row->clicks;?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $_LANG->_( 'Banner Image' ); ?>:
				</td>
				<td valign="top">
					<?php
					if (eregi("swf", $_row->imageurl)) {
						?>
						<img src="images/blank.png" name="imagelib">
						<?php
					} elseif (eregi("gif|jpg|png", $_row->imageurl)) {
						?>
						<img src="../images/banners/<?php echo $_row->imageurl; ?>" name="imagelib">
						<?php
					} else {
						?>
						<img src="images/blank.png" name="imagelib">
						<?php
					}
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Banner alt text' ); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="editor" size="50" value="<?php echo $_row->editor;?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $_LANG->_( 'Custom banner code' ); ?>:
				</td>
				<td>
					<textarea class="inputbox" cols="50" rows="10" name="custombannercode"><?php echo $_row->custombannercode;?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				</td>
			</tr>
			</table>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $_option; ?>" />
		<input type="hidden" name="bid" value="<?php echo $_row->bid; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="impmade" value="<?php echo $_row->impmade; ?>" />
		</form>
		<?php
	}
}

/**
* Banner clients
* @package Joomla
*/
class HTML_bannerClient {

	function showClients( &$rows, &$pageNav, $option, &$lists ) {
		global $my;
	   global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function tableOrdering_alt( order, dir ) {
			var form = document.adminForm;

			form.tOrder.value 		= order;
			form.tOrderDir.value 	= dir;
			submitform( 'listclients' );
		}
		</script>
		<form action="index2.php" method="post" name="adminForm" id="bannerform" class="adminform">

		<?php
		bannersScreens::viewClients( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th align="left" nowrap="nowrap" >
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Client Name' ), 'a.name', 'tableOrdering_alt'  ); ?>
					</th>
					<th align="left">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Contact' ), 'a.contact', 'tableOrdering_alt' ); ?>
					</th>
					<th align="center" nowrap="nowrap" width="5%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Banners' ), 'num', 'tableOrdering_alt' ); ?>
					</th>
					<th align="center" nowrap="nowrap" width="5%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'a.cid', 'tableOrdering_alt' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="7" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="7" class="center">
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
					$row = &$rows[$i];

					$row->id 	= $row->cid;
					$link 		= 'index2.php?option=com_banners&amp;task=editclientA&amp;id='. $row->id;

					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i, 'checkinclients' );
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td>
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								echo $row->name;
							} else {
								?>
								<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Banner Client' ); ?>" class="editlink">
									<?php echo $row->name; ?>
								</a>
								<?php
							}
							?>
						</td>
						<td>
							<?php echo $row->contact;?>
						</td>
						<td align="center">
							<?php echo $row->num;?>
						</td>
						<td align="center">
							<?php echo $row->cid;?>
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

	function bannerClientForm( &$row, $option ) {
		global $_LANG;

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'extrainfo' );
		?>
		<script language="javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancelclient') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the Client Name.' ); ?>" );
			} else if (form.contact.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the Contact Name.' ); ?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the Contact Email.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		bannersScreens::editClients();
		?>
			<table class="adminform" id="editpage">
			<thead>
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Details' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="2">
				</th>
			</tr>
			</tfoot>

			<tr>
				<td width="150">
					<label for="name">
						<?php echo $_LANG->_( 'Client Name' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="name" id="name" size="60" maxlength="60" valign="top" value="<?php echo $row->name; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<label id="contact">
						<?php echo $_LANG->_( 'Contact Name' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="contact" id="contact" size="60" maxlength="60" value="<?php echo $row->contact; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<label for="email">
						<?php echo $_LANG->_( 'Contact Email' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="email" id="email" size="60" maxlength="60" value="<?php echo $row->email; ?>">
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="info">
						<?php echo $_LANG->_( 'Extra Info' ); ?>:
					</label>
				</td>
				<td>
					<textarea class="inputbox" name="extrainfo" id="info" cols="80" rows="30"><?php echo ampReplace( $row->extrainfo );?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="3">
				</td>
			</tr>
			</table>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="cid" value="<?php echo $row->cid; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>