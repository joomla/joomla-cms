<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
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
* @subpackage Polls
*/
class HTML_poll {
	function showPolls( &$rows, &$pageNav, $option, &$lists ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_poll" method="post" name="adminForm">
		
		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
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
			<tr>
				<th width="5">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th  class="title">
					<?php mosCommonHTML::tableOrdering( 'Poll Title', 'm.title', $lists ); ?>
				</th>
				<th width="8%" align="center">
					<?php mosCommonHTML::tableOrdering( 'Published', 'm.published', $lists ); ?>
				</th>
				<th width="3%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'ID', 'm.id', $lists ); ?>
				</th>
				<th width="8%" align="center">
					<?php mosCommonHTML::tableOrdering( 'Votes', 'm.voters', $lists ); ?>
				</th>
				<th width="8%" align="center">
					<?php mosCommonHTML::tableOrdering( 'Options', 'numoptions', $lists ); ?>
				</th>
				<th width="10%" align="center">
					<?php mosCommonHTML::tableOrdering( 'Lag', 'm.lag', $lists ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
	
				$link 		= ampReplace( 'index2.php?option=com_poll&task=editA&hidemainmenu=1&id='. $row->id );
	
				$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
				$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Poll' ); ?>">
							<?php echo $row->title; ?></a>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
					<td align="center">
						<?php echo $row->voters; ?>
					</td>
					<td align="center">
						<?php echo $row->numoptions; ?>
					</td>
					<td align="center">
						<?php echo $row->lag; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
			
			<?php echo $pageNav->getListFooter(); ?>
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


	function editPoll( &$row, &$options, &$lists ) {
		mosMakeHtmlSafe( $row, ENT_QUOTES );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.title.value == "") {
				alert( "<?php echo JText::_( 'Poll must have a title', true ); ?>" );
			} else if( isNaN( parseInt( form.lag.value ) ) ) {
				alert( "<?php echo JText::_( 'Poll must have a non-zero lag time', true ); ?>" );
			//} else if (form.menu.options.value == ""){
			//	alert( "Poll must have pages." );
			//} else if (form.adminForm.textfieldcheck.value == 0){
			//	alert( "Poll must have options." );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		
		<div id="editcell">				
			<table width="100%">
			<tr>
				<td width="50%" valign="top">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo JText::_( 'Details' ); ?>
						</th>
					</tr>
					<tr>
						<td width="110">
							<label for="title">
								<?php echo JText::_( 'Title' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="title" id="title" size="60" value="<?php echo $row->title; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="lag">
								<?php echo JText::_( 'Lag' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="lag" id="lag" size="10" value="<?php echo $row->lag; ?>" /> 
							<?php echo JText::_( '(seconds between votes)' ); ?>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<label for="selections">
								<?php echo JText::_( 'Menu Item Link(s)' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['select']; ?>
						</td>
					</tr>
					</table>
				</td>
				<td width="50%" valign="top">
					<table class="adminform">
					<tr>
						<th colspan="3">
							<?php echo JText::_( 'Options' ); ?>
						</th>
					</tr>
					<?php
					for ($i=0, $n=count( $options ); $i < $n; $i++ ) {
						?>
						<tr>
							<td>
								<label for="polloption<?php echo $options[$i]->id; ?>">
									<?php echo JText::_( 'Option' ); ?> <?php echo ($i+1); ?>
								</label>
							</td>
							<td>
								<input class="inputbox" type="text" name="polloption[<?php echo $options[$i]->id; ?>]" id="polloption<?php echo $options[$i]->id; ?>" value="<?php echo stripslashes($options[$i]->text); ?>" size="60" />
							</td>
						</tr>
						<?php
					}
					?>
					</table>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_poll" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="textfieldcheck" value="<?php echo $n; ?>" />
		</form>
		<?php
	}

	function previewPoll($title, $options){
		?>
		<form>
		<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0" >
		<tr>
			<td class="moduleheading" colspan="2"><?php echo $title; ?></td>
		</tr>
		<?php foreach ($options as $text)
		{
			if ($text <> "")
			{?>
			<tr>
				<td valign="top" height="30"><input type="radio" name="poll" value="<?php echo $text; ?>"></td>
				<td class="poll" width="100%" valign="top"><?php echo $text; ?></td>
			</tr>
			<?php }
		} ?>
		<tr>
			<td valign="middle" height="50" colspan="2" align="center"><input type="button" name="submit" value="<?php echo JText::_( 'Vote' ); ?>">&nbsp;&nbsp;<input type="button" name="result" value="<?php echo JText::_( 'Results' ); ?>"></td>
		</tr>
		<tr>
			<td align="center" colspan="2"><a href="#" onClick="window.close()"><?php echo JText::_( 'Close' ); ?></a></td>
		</tr>
		</table>
		</form>
		<?php
	}
}
?>
