<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Modules
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
* @subpackage Modules
*/
class HTML_modules {

	/**
	* Writes a list of the defined modules
	* @param array An array of category objects
	*/
	function showModules( &$rows, $myid, $client, &$pageNav, $option, &$lists, $search ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading">
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<?php
				echo $lists['position'];
				echo $lists['type'];
				?>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="20px">
			<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20px">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows );?>);" />
			</th>
			<th class="title">
			<?php echo JText::_( 'Module Name' ); ?>
			</th>
			<th nowrap="nowrap" width="10%">
			<?php echo JText::_( 'Published' ); ?>
			</th>
			<th colspan="2" align="center" width="5%">
			<?php echo JText::_( 'Reorder' ); ?>
			</th>
			<th width="2%">
			<?php echo JText::_( 'Order' ); ?>
			</th>
			<th width="1%">
			<a href="javascript: saveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo JText::_( 'Save Order' ); ?>" /></a>
			</th>
			<?php
			if ( !$client ) {
				?>
				<th nowrap="nowrap" width="7%">
				<?php echo JText::_( 'Access' ); ?>
				</th>
				<?php
			}
			?>
			<th nowrap="nowrap" width="7%">
			<?php echo JText::_( 'Position' ); ?>
			</th>
			<th nowrap="nowrap" width="5%">
			<?php echo JText::_( 'Pages' ); ?>
			</th>
			<th nowrap="nowrap" width="5%">
			<?php echo JText::_( 'ID' ); ?>
			</th>
			<th nowrap="nowrap" width="10%"  class="title">
			<?php echo JText::_( 'Type' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row 	= &$rows[$i];

			$link = 'index2.php?option=com_modules&client='. $client .'&task=editA&hidemainmenu=1&id='. $row->id;

			$access 	= mosCommonHTML::AccessProcessing( $row, $i );
			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			$published 	= mosCommonHTML::PublishedProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="right">
				<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td>
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->title;
				} else {
					?>
					<a href="<?php echo $link; ?>">
					<?php echo $row->title; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td align="center">
				<?php echo $published;?>
				</td>
				<td>
				<?php echo $pageNav->orderUpIcon( $i, ($row->position == @$rows[$i-1]->position) ); ?>
				</td>
				<td>
				<?php echo $pageNav->orderDownIcon( $i, $n, ($row->position == @$rows[$i+1]->position) ); ?>
				</td>
				<td align="center" colspan="2">
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<?php
				if ( !$client ) {
					?>
					<td align="center">
					<?php echo $access;?>
					</td>
					<?php
				}
				?>
				<td align="center">
				<?php echo $row->position; ?>
				</td>
				<td align="center">
				<?php
				if (is_null( $row->pages )) {
					echo JText::_( 'None' );
				} else if ($row->pages > 0) {
					echo JText::_( 'Varies' );
				} else {
					echo JText::_( 'All' );
				}
				?>
				</td>
				<td align="center">
				<?php echo $row->id;?>
				</td>
				<td>
				<?php echo $row->module ? $row->module : JText::_( 'User' );?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>

		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client;?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosCategory The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function editModule( &$row, &$orders2, &$lists, &$params, $option, $client ) {
		global $mainframe;

		$lang =& $mainframe->getLanguage();

		$lang->load($row->module, $row->client_id);

		$row->titleA = '';
		if ( $row->id ) {
			$row->titleA = '<small><small>[ '. $row->title .' ]</small></small>';
		}

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if ( ( pressbutton == 'save' ) && ( document.adminForm.title.value == "" ) ) {
				alert("<?php echo JText::_( 'Module must have a title', true ); ?>");
			} else {
				<?php
				if ($row->module == '') {
					getEditorContents( 'editor1', 'content' );
				}
				?>
				submitform(pressbutton);
			}
			submitform(pressbutton);
		}
		<!--
		var originalOrder 	= '<?php echo $row->ordering;?>';
		var originalPos 	= '<?php echo $row->position;?>';
		var orders 			= new Array();	// array in the format [key,value,text]
		<?php	$i = 0;
		foreach ($orders2 as $k=>$items) {
			foreach ($items as $v) {
				echo "\n	orders[".$i++."] = new Array( \"$k\",\"$v->value\",\"$v->text\" );";
			}
		}
		?>
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table cellspacing="0" cellpadding="0" width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Details' ); ?>
					</th>
				<tr>
				<tr>
					<td width="100" >
					<?php echo JText::_( 'Title' ); ?>:
					</td>
					<td>
					<input class="text_area" type="text" name="title" size="35" value="<?php echo $row->title; ?>" />
					</td>
				</tr>
				<!-- START selectable pages -->
				<tr>
					<td width="100" >
					<?php echo JText::_( 'Show title' ); ?>:
					</td>
					<td>
					<?php echo $lists['showtitle']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo JText::_( 'Position' ); ?>:
					</td>
					<td>
					<?php echo $lists['position']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo JText::_( 'Module Order' ); ?>:
					</td>
					<td>
					<script language="javascript" type="text/javascript">
					<!--
					writeDynaList( 'class="inputbox" name="ordering" size="1"', orders, originalPos, originalPos, originalOrder );
					//-->
					</script>
					</td>
				</tr>
				<tr>
					<td valign="top" >
					<?php echo JText::_( 'Access Level' ); ?>:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo JText::_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo JText::_( 'ID' ); ?>:
					</td>
					<td>
					<?php echo $row->id; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
					<?php echo JText::_( 'Description' ); ?>:
					</td>
					<td>
					<?php echo $row->description; ?>
					</td>
				</tr>
				</table>

				<?php
				// Hide params for Custom/New modules
				// Show custom.xml params for backward compat with existing custom modules
				// that are used to show rss feeds
				// extra backward compat check [$params->get( 'rssurl', '' )] can be depreciated in 1.2
				if ( $row->module || $params->get( 'rssurl', '' ) ) {
					// Render Parameter list
					?>
					<table class="adminform">
					<tr>
						<th >
						<?php echo JText::_( 'Parameters' ); ?>
						</th>
					<tr>
					<tr>
						<td>
						<?php echo $params->render();?>
						</td>
					</tr>
					</table>
					<?php
				}
				?>
			</td>
			<td width="40%" >
				<table width="100%" class="adminform">
				<tr>
					<th>
					<?php echo JText::_( 'Pages / Items' ); ?>
					</th>
				<tr>
				<tr>
					<td>
					<?php echo JText::_( 'Menu Item Link(s)' ); ?>:
					<br />
					<?php echo $lists['selections']; ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<?php
		if ( !$row->module ) {
			?>
			<tr>
				<td colspan="2">
						<table width="100%" class="adminform">
						<tr>
							<th colspan="2">
							<?php echo JText::_( 'Custom Output' ); ?>
							</th>
						<tr>
						<tr>
							<td valign="top" >
							<?php echo JText::_( 'Content' ); ?>:
							</td>
							<td>
							<?php
							// parameters : areaname, content, hidden field, width, height, rows, cols
							editorArea( 'editor1',  $row->content , 'content', '800', '400', '110', '40' ) ; ?>
							</td>
						</tr>
						</table>
				</td>
			</tr>
			<?php
		}
		?>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="original" value="<?php echo $row->ordering; ?>" />
		<input type="hidden" name="module" value="<?php echo $row->module; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client_id" value="<?php echo $lists['client_id']; ?>" />
		<input type="hidden" name="client" value="<?php echo $client ?>" />
		</form>
		<?php
	}

	function previewModule()
	{
		?>
		<script>
		var content = window.opener.document.adminForm.content.value;
		var title = window.opener.document.adminForm.title.value;

		content = content.replace('#', '');
		title = title.replace('#', '');
		content = content.replace('src=images', 'src=../../images');
		content = content.replace('src=images', 'src=../../images');
		title = title.replace('src=images', 'src=../../images');
		content = content.replace('src=images', 'src=../../images');
		title = title.replace('src=\"images', 'src=\"../../images');
		content = content.replace('src=\"images', 'src=\"../../images');
		title = title.replace('src=\"images', 'src=\"../../images');
		content = content.replace('src=\"images', 'src=\"../../images');
		</script>

		<table align="center" width="160" cellspacing="2" cellpadding="2" border="0" height="100%">
		<tr>
			<td class="moduleheading"><script>document.write(title);</script></td>
		</tr>
		<tr>
			<td valign="top" height="90%"><script>document.write(content);</script></td>
		</tr>
		<tr>
			<td align="center"><a href="#" onClick="window.close()"><?php echo JText::_( 'Close' ); ?></a></td>
		</tr>
		</table>
		<?php
	}
}
?>
