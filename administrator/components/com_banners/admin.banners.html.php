<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
* @subpackage Banners
*/
class HTML_banners {

	function showBanners( &$rows, &$pageNav, $option, &$lists ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_banners" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<td align="left" valign="top" nowrap="nowrap">
			</td>
			<td align="right" valign="top" nowrap="nowrap">
				<?php
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="20">
            	<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value=""  onclick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th nowrap="nowrap" class="title">
				<?php mosCommonHTML :: tableOrdering( 'Banner Name', 'b.name', $lists ); ?>
			</th>
			<th width="7%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'Published', 'b.showBanner', $lists ); ?>
			</th>
			<th width="3%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'ID', 'b.bid', $lists ); ?>
			</th>
			<th width="8%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'Impressions Made', 'b.impmade', $lists ); ?>
			</th>
			<th width="8%" nowrap="nowrap">
				<?php echo JText::_( 'Impressions Left' ); ?>
			</th>
			<th width="7%">
				<?php mosCommonHTML :: tableOrdering( 'Clicks', 'b.clicks', $lists ); ?>
			</th>
			<th width="7%" nowrap="nowrap">
				<?php echo JText::_( '% Clicks' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$row->id 	= $row->bid;
			$link 		= ampReplace( 'index2.php?option=com_banners&task=editA&hidemainmenu=1&id='. $row->id );

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
			$img 	= $row->showBanner ? 'publish_g.png' : 'publish_x.png';
			$alt 	= $row->showBanner ? JText::_( 'Published' ) : JText::_( 'Unpublished' );

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center">
					<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td align="center">
					<?php echo $checked; ?>
				</td>
				<td>
					<?php
					if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
						echo $row->name;
					} else {
						?>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Banner' ); ?>">
							<?php echo $row->name; ?></a>
						<?php
					}
					?>
				</td>
				<td align="center">
					<a href="javascript: void(0);" onmouseover="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
						<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" /></a>
				</td>
				<td align="center">
					<?php echo $row->id; ?>
				</td>
				<td align="center">
					<?php echo $row->impmade;?>
				</td>
				<td align="center">
					<?php echo JText::_( $impleft ); ?>
				</td>
				<td align="center">
					<?php echo $row->clicks;?>
				</td>
				<td align="center">
					<?php echo $percentClicks;?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		
		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	function bannerForm( &$_row, &$lists, $_option ) {
		mosMakeHtmlSafe( $_row, ENT_QUOTES, 'custombannercode' );
		?>
		<script language="javascript" type="text/javascript">
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
				alert( "<?php echo JText::_( 'You must provide a banner name.', true ); ?>" );
			} else if (getSelectedValue('adminForm','cid') < 1) {
				alert( "<?php echo JText::_( 'Please select a client.', true ); ?>" );
			} else if (!getSelectedValue('adminForm','imageurl')) {
				alert( "<?php echo JText::_( 'Please select an image.', true ); ?>" );
			} else if (form.clickurl.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the URL for the banner.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<th colspan="2">
				<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="20%">
				<?php echo JText::_( 'Banner Name' ); ?>:
			</td>
			<td width="80%">
				<input class="inputbox" type="text" name="name" value="<?php echo $_row->name;?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Client Name' ); ?>:
			</td>
			<td >
				<?php echo $lists['cid']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Impressions Purchased' ); ?>:
			</td>
			<?php
			$unlimited = '';
			if ($_row->imptotal == 0) {
				$unlimited = 'checked="checked"';
				$_row->imptotal = '';
			}
			?>
			<td>
				<input class="inputbox" type="text" name="imptotal" size="12" maxlength="11" value="<?php echo $_row->imptotal;?>" />
				&nbsp;&nbsp;&nbsp;&nbsp;
				<?php echo JText::_( 'Unlimited' ); ?> <input type="checkbox" name="unlimited" <?php echo $unlimited;?> />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Show Banner' ); ?>:
			</td>
			<td>
				<?php echo $lists['showBanner']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Click URL' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="clickurl" size="100" maxlength="200" value="<?php echo $_row->clickurl;?>" />
			</td>
		</tr>
		<tr >
			<td valign="top" align="right">
				<?php echo JText::_( 'Clicks' ); ?>
 			</td>
			<td colspan="2">
				<?php echo $_row->clicks;?>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input name="reset_hits" type="button" class="button" value="<?php echo JText::_( 'Reset Clicks' ); ?>" ="submitbutton('resethits');" />
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'Custom banner code' ); ?>:
			</td>
			<td>
				<textarea class="inputbox" cols="70" rows="5" name="custombannercode"><?php echo $_row->custombannercode;?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'Banner Image Selector' ); ?>:
			</td>
			<td >
				<?php echo $lists['imageurl']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'Banner Image' ); ?>:
			</td>
			<td valign="top">
				<?php
				if (eregi("swf", $_row->imageurl)) {
					?>
					<img src="images/blank.png" name="imagelib">
					<?php
				} elseif (eregi("gif|jpg|png", $_row->imageurl)) {
					?>
					<img src="../images/banners/<?php echo $_row->imageurl; ?>" name="imagelib" />
					<?php
				} else {
					?>
					<img src="images/blank.png" name="imagelib" />
					<?php
				}
				?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $_option; ?>" />
		<input type="hidden" name="bid" value="<?php echo $_row->bid; ?>" />
		<input type="hidden" name="clicks" value="<?php echo $_row->clicks; ?>" />
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

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_banners&amp;task=listclients" method="post" name="adminForm">

		<table class="adminlist">
		<tr>
			<th width="20">
           		<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th nowrap="nowrap" class="title">
				<?php mosCommonHTML :: tableOrdering( 'Client Name', 'a.name', $lists, 'listclients' ); ?>
			</th>
			<th width="3%" nowrap="nowrap">
				<?php mosCommonHTML :: tableOrdering( 'ID', 'a.cid', $lists, 'listclients' ); ?>
			</th>
			<th nowrap="nowrap" class="title" width="30%">
				<?php mosCommonHTML :: tableOrdering( 'Contact', 'a.contact', $lists, 'listclients' ); ?>
			</th>
			<th align="center" nowrap="nowrap" width="5%">
				<?php mosCommonHTML :: tableOrdering( 'No. of Active Banners', 'bid', $lists, 'listclients' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$row->id 		= $row->cid;
			$link 			= ampReplace( 'index2.php?option=com_banners&task=editclientA&hidemainmenu=1&id='. $row->id );
			
			$checked 		= mosCommonHTML::CheckedOutProcessing( $row, $i );
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
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Banner Client' ); ?>">
							<?php echo $row->name; ?></a>
						<?php
					}
					?>
				</td>
				<td align="center">
					<?php echo $row->cid; ?>
				</td>
				<td>
					<?php echo $row->contact; ?>
				</td>
				<td align="center">
					<?php echo $row->bid;?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		
		<?php echo $pageNav->getListFooter(); ?>
		
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="listclients" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	function bannerClientForm( &$row, $option ) {
		mosMakeHtmlSafe( $row, ENT_QUOTES, 'extrainfo' );
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancelclient') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the Client Name.', true ); ?>" );
			} else if (form.contact.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the Contact Name.', true ); ?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the Contact Email.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<th colspan="2">
				<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="10%">
				<?php echo JText::_( 'Client Name' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="name" size="30" maxlength="60" valign="top" value="<?php echo $row->name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="10%">
			<?php echo JText::_( 'Contact Name' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="contact" size="30" maxlength="60" value="<?php echo $row->contact; ?>" />
			</td>
		</tr>
		<tr>
			<td width="10%">
				<?php echo JText::_( 'Contact Email' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="email" size="30" maxlength="60" value="<?php echo $row->email; ?>" />
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'Extra Info' ); ?>:
			</td>
			<td>
				<textarea class="inputbox" name="extrainfo" cols="60" rows="10"><?php echo str_replace('&','&amp;',$row->extrainfo);?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="cid" value="<?php echo $row->cid; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>