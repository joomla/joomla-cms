<?php
/**
* @version 1.5
* @package com_localise
* @author Ifan Evans
* @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

defined('_JEXEC') or die('Restricted access');

// TOOLBAR
$langName = ' <small><small> : ' . $this->options['langName'] . '</small></small>';
JToolbarHelper::title( JText::_( 'Language Files' ) . $langName, 'langmanager.png' );
JToolbarHelper::custom('languages','upload.png','upload_f2.png','Languages',false);
JToolbarHelper::divider();
JToolbarHelper::unpublishList();
JToolbarHelper::publishList();
JToolbarHelper::deleteList(JText::_('Confirm Delete INI'));
JToolbarHelper::editList();
JToolbarHelper::addNew();

?>
<div id="localise">
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<input type="hidden" name="option" value="com_localise" />
	<input type="hidden" name="task" value="files" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

	<table  width="100%">
	<tr>
		<td><b><?php echo JText::_( 'Language' ); ?>:</b></td>
		<td><?php echo $this->lists['client_lang']; ?></td>
		<?php
			if (!$this->options['isReference']) {
				echo '<td align="left"><div style="border:solid silver 1px;background:white;width:100px;height:8px;"><div style="height:100%; width:' . $this->options['fileset-status'] . 'px;background:green;"></div></div></td><td><b>'. $this->options['fileset-status'] .'%&nbsp;</b></td>';
				echo '<td width="100%" align="left"><div style="font-size:smaller">'. sprintf( JText::_('of translated'), $this->options['fileset-changed'], $this->options['fileset-refstrings'] ) .'<br>'. sprintf( JText::_('of published'), $this->options['fileset-exists'], $this->options['fileset-published'], $this->options['fileset-files']  ) .'</td>';
			}
			else {
				echo '<td width="100%" align="left"><div style="font-size:smaller"><div style="color:red">'. JText::_('Warning Default Language') .'</div>'. sprintf( JText::_('of published'), $this->options['fileset-published'], $this->options['fileset-exists'], $this->options['fileset-files'] ) .'</div></td>';
			}
		?>
		<td align="right" nowrap="nowrap">
			<?php
			$html = '<img src="images/search_f2.png" align="absmiddle" width="16" height="16" alt="?" style="cursor:pointer" onclick="if(e=getElementById(\'filter_search\')){e.form.submit();}">';
			echo '<div style="border:1px solid gray;background-color:#e9e9e9"> &nbsp; ' . $this->getTooltip( $html, 'Search Translation Files', 'Search', 'TC' ) . ' ';
			echo $this->lists['search'].' &nbsp; </div>';
			?>
		</td>
		<td align="right"><?php echo $this->lists['state']; ?></td>
		<td align="right"><?php echo $this->lists['status']; ?></td>
	</tr>
	</table>

	<table class="adminlist" id="files">

		<thead>
		<tr>
			<th width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th width="20"><input type="checkbox" name="toggle" value=""  onclick="checkAll(<?php echo count( $this->data ); ?>);" /></th>
			<th width="25%"><?php echo JHtml::_( 'grid.sort',  'File', 'name', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
			<th width="40"><?php echo JText::_( 'State' ); ?></th>
			<th width="100"><?php echo JHtml::_( 'grid.sort',  'Status', 'status', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
			<th width="100"><?php echo JHtml::_( 'grid.sort',  'Strings', 'strings', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
            <th width="40"><?php echo JHtml::_( 'grid.sort',  'Version', 'version', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
			<th width="40"><?php echo JHtml::_( 'grid.sort',  'Date', 'datetime', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
			<th width="20%"><?php echo JHtml::_( 'grid.sort',  'Author', 'author', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
		</tr>
		</thead>

		<tfoot>
			<td  width="100%" colspan="9">
				<?php echo $this->pagenav->getListFooter(); ?>
			</td>
		</tfoot>

		<tbody>
		<?php
		// process the rows (each is an INI translation file)
		for ($i=0, $n=count( $this->data ); $i < $n; $i++) {
			$row =& $this->data[$i];
			$link = 'index.php?option=com_localise&task=edit&client_lang='.$this->options['client_lang'].'&cid[]='. $row->filename;
			?>
			<tr class="row<?php echo $i; ?>">
				<td width="20">
					<?php echo $this->pagenav->getRowOffset( $i ); ?>
				</td>
				<td width="20">
					<?php
					// only select writable files
					if ($row->checkedout) {
						echo '<img src="images/checked_out.png" title="'.JText::_( 'Checked Out' ).'" alt="x" />';
					} else if ($row->writable) {
						echo '<input type="checkbox" id="cb'.$i.'" name="cid[]" value="'.$row->filename.'" onclick="isChecked(this.checked);" />';
					} else {
						echo '&nbsp;';
					}
					?>
				</td>
				<td width="25%">
					<?php
					// edit all files
					if ($row->writable) {
						echo '<a href="' . $link . '" title="' . JText::_( 'Edit' ) . '">' . $row->name . '</a>';
					} else {
						echo $row->name;
					}
					if ( $row->bom != 'UTF-8' ) {
						echo ' &nbsp; <a href="http://en.wikipedia.org/wiki/UTF-8" target="_blank"><b style="font-size:smaller;color:red">' . $this->getTooltip( $row->bom, null, 'Not UTF-8', 'TC' ) . '</b></a>';
					}
					// search matches
					if ($row->searchfound) {
					    $row->searchtext = htmlspecialchars($this->options['filter_search'],ENT_QUOTES);
						if ($row->searchfound_ref) {
						    echo '<div style="font-size:smaller;color:red"> &nbsp; ' . sprintf( JText::_('matches ref file'), $row->searchfound_ref, $row->searchtext ) . '</div>';
						}
                        if ($row->searchfound_tran) {
                            echo '<div style="font-size:smaller;color:green"> &nbsp; ' . sprintf( JText::_('matches tran file'), $row->searchfound_tran, $row->searchtext ) . '</div>';
					    }
                    }
					?>
				</td>
				<td align="center">
					<?php
					// only publish / unpublish writable files
					if (!$row->exists) {
						echo $this->getTooltip( '<img src="images/disabled.png" alt="x" />', null, 'Does Not Exist', 'TC' );
					} else if ($row->writable) {
						echo JHtml::_( 'grid.published',  $row, $i, 'publish_g.png', 'publish_r.png' );
					} else if ($row->published) {
						echo '<img src="images/publish_g.png" alt="'.JText::_( 'Published' ).'" />';
					} else {
						echo '<img src="images/publish_r.png" alt="'.JText::_( 'Not Published' ).'" />';
					}
					?>
				</td>
				<td width="100" align="center">
					<?php
					// no reference file
					// status is inapplicable
					// status is 100 (complete)
					// status is 0 (not started)
					// status is in progress
					if ( $row->bom != 'UTF-8' ) {
						echo $this->getTooltip( '<a href="http://en.wikipedia.org/wiki/UTF-8" target="_blank"><img src="components/com_localise/images/warning.png" alt="!" /></a>', null, 'Not UTF-8', 'TC' );
					} else if (!$row->refexists) {
						echo $this->getTooltip( '<img src="images/disabled.png" alt="x" />', null, 'No Reference File', 'TC' );
					} else if ($this->options['isReference']) {
						echo $this->getTooltip( '<img src="images/disabled.png" alt="x" />', null, 'This is the Reference Language', 'TC' );
					} else if ($row->status == 100) {
						echo $this->getTooltip( '<img src="images/tick.png" alt="1000%" />', null, 'Complete', 'TC' );
					} else if ($row->status == 0) {
						echo $this->getTooltip( '<img src="images/publish_x.png" alt="0%" />', null, 'Not Started', 'TC' );
					} else {
						echo '<span title="'. JText::_('In Progress') .': '. $row->changed . ' '. JText::_('Changed') .'">' . $row->status . '%<div style="text-align:left;border:solid silver 1px;width:100px;height:2px;"><div style="height:100%; width:' . $row->status . '%;background:green;"></div></div></span>';
					}
					?>
				</td>
				<td align="center">
					<?php
					if ($this->options['isReference']) {
						$status = $row->strings;
					} else {
						if ($row->changed==$row->refstrings) {
							$status = $row->refstrings;
						} else {
							$status = $row->changed . '/' . $row->refstrings;
						}
						if($row->extra) {
							$status .= ' +' . $row->extra;
						}
					}
					if ($row->changed==0) {
						$tip = null;
						$caption = 'Not Started';
						$jtext = 'TC';
					} else if ($row->unchanged + $row->missing + $row->extra == 0) {
						$tip = null;
						$caption = 'Complete';
						$jtext = 'TC';
					} else {
						$tip = '';
						$tip .= ($row->unchanged==0) ? '' : sprintf(JText::_('Overlib Unchanged'), $row->unchanged) . '<br>';
						$tip .= ($row->missing==0) ? '' : sprintf(JText::_('Overlib Missing'), $row->missing) . '<br>';
						$tip .= ($row->extra==0) ? '' : sprintf(JText::_('Overlib Extra'), $row->extra) . '<br>';
						$caption = sprintf(JText::_('Overlib Strings'), $row->refstrings);
						$jtext = '';
					}
					echo $this->getTooltip( $status, $tip, $caption, $jtext );
					?>
				</td>
				<td align="center">
					<?php echo $row->version; ?>
				</td>
				<td align="center">
					<?php echo '<span title="' . $row->time .'">' . $row->date . '</span>'; ?>
				</td>
				<td align="center">
					<?php echo $row->author; ?>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>

	</table>

</form>
</div>