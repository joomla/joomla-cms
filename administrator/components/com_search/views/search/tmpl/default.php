<form action="index.php" method="post" name="adminForm">
<table>
  <tr>
    <td align="left" width="100%">
    <?php echo JText::_( 'Filter' ); ?>:
    <input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onchange="document.adminForm.submit();" />
    <button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
    <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
    </td>
    <td nowrap="nowrap">
      <span class="componentheading"><?php echo JText::_( 'Search Logging' ); ?> :
      <?php echo $this->enabled ? '<b><font color="green">'. JText::_( 'Enabled' ) .'</font></b>' : '<b><font color="red">'. JText::_( 'Disabled' ) .'</font></b>' ?>
	</span>
    </td>
    <td nowrap="nowrap" align="right">
	<?php
	if ( $this->showResults ) {
	?>
	<a href="index.php?option=com_search&amp;search_results=0"><?php echo JText::_( 'Hide Search Results' ); ?></a>
	<?php
	} else {
	?>
	<a href="index.php?option=com_search&amp;search_results=1"><?php echo JText::_( 'Show Search Results' ); ?></a>
	<?php } ?>
	</td>
	</tr>
</table>

		<div id="tablecell">
			<table class="adminlist">
			<thead>
				<tr>
					<th width="10">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort',   'Search Text', 'search_term', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th nowrap="nowrap" width="20%">
						<?php echo JHTML::_('grid.sort',   'Times Requested', 'hits', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<?php
					if ( $this->showResults ) {
						?>
						<th nowrap="nowrap" width="20%">
							<?php echo JText::_( 'Results Returned' ); ?>
						</th>
						<?php
					}
					?>
				</tr>
			</thead>
			<?php
			$k = 0;
			for ($i=0, $n = count($this->items); $i < $n; $i++) {
				$row =& $this->items[$i];
				?>
				<tr class="row<?php echo $k;?>">
					<td align="right">
						<?php echo $i+1+$this->pageNav->limitstart; ?>
					</td>
					<td>
						<?php echo $row->search_term;?>
					</td>
					<td align="center">
						<?php echo $row->hits; ?>
					</td>
					<?php
					if ( $this->showResults ) {
						?>
						<td align="center">
							<?php echo $row->returns; ?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			<tfoot>
				<td colspan="4">
					<?php echo $this->pageNav->getListFooter(); ?>
				</td>
			</tfoot>
			</table>
		</div>

		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>