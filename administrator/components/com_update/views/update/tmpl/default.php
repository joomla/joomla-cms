<form action="index.php" method="post" name="adminForm">

	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="10px"><?php echo JText::_( 'Num' ); ?></th>
				<th class="title" nowrap="nowrap"><?php echo JText::_( 'Extension' ); ?></th>
				<th class="title"><?php echo JText::_('Type') ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Version' ); ?></th>
				<th class="title" ><?php echo JText::_( 'Folder' ) ?></th>
				<th class="title" ><?php echo JText::_( 'Client' ) ?></th>
				<th class="title" width="25%"><?php echo JText::_( 'Author' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
			<td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php for ($i=0, $n=count($this->items), $rc=0; $i < $n; $i++, $rc = 1 - $rc) : ?>
			<?php
				$this->loadItem($i);
				echo $this->loadTemplate('item');
			?>
		<?php endfor; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_( 'ERRNOUPDATES' ); ?>
	<?php endif; ?>

	<input type="hidden" name="task" value="manage" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_installer" />
	<input type="hidden" name="type" value="update" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
