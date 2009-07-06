<?php defined('_JEXEC') or die; ?>

<?php
	$user = & JFactory :: getUser();

	JHtml::_('behavior.imagetooltip', 206, 145);
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

	<table class="adminlist">
	<thead>
		<tr>
			<th width="5" class="title">
				<?php echo JText::_('Num'); ?>
			</th>
			<th class="title" colspan="2">
				<?php echo JText::_('Template Name'); ?>
			</th>
			<?php
			if ($this->client->id == 1) {
			?>
				<th width="5%">
					<?php echo JText::_('Default'); ?>
				</th>
			<?php
			} else {
			?>
				<th width="5%">
					<?php echo JText::_('Default'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('Assigned'); ?>
				</th>
			<?php
			}
			?>
			<th width="10%" align="center">
				<?php echo JText::_('Version'); ?>
			</th>
			<th width="15%" class="title">
				<?php echo JText::_('Date'); ?>
			</th>
			<th width="25%"  class="title">
				<?php echo JText::_('Author'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="8">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
$k = 0;
for ($i = 0, $n = count($this->rows); $i < $n; $i++) {
	$row = & $this->rows[$i];

	$author_info = @ $row->xmldata->authorEmail . '<br />' . @ $row->xmldata->authorUrl;
	$img_path = ($this->client->id == 1 ? JURI::root().'administrator' : JURI::root()).'/templates/'.$row->template.'/template_thumbnail.png';
?>
		<tr class="<?php echo 'row'. $k; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td width="5">
			<?php
			if (JTable::isCheckedOut($user->get ('id'), $row->xmldata->checked_out)) {
			?>
					&nbsp;
			<?php
			} else {
			?>
					<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
			<?php
			}
			?>
			</td>
			<td>
				<span id="<?php echo $img_path; ?>" class="hasSnapshot" title="<?php echo $row->template;?>::">
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=edit&cid[]=' . $row->id . '&client=' . $this->client->id); ?>"><?php echo $row->template;?></a>
				</span>
			</td>
			<?php
			if ($this->client->id == 1) {
			?>
				<td align="center">
				<?php
				if ($row->home == 1) {
				?>
					<img src="templates/bluestork/images/menu/icon-16-default.png" alt="<?php echo JText::_('Published'); ?>" />
				<?php
				} else {
				?>
						&nbsp;
				<?php
				}
				?>
				</td>
			<?php
			} else {
			?>
				<td align="center">
				<?php
				if ($row->home == 1) {
				?>
						<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_('Default'); ?>" />
				<?php
				} else {
				?>
						&nbsp;
				<?php
				}
				?>
				</td>
				<td align="center">
				<?php
				if ($row->assigned == 1) {
				?>
						<img src="images/tick.png" alt="<?php echo JText::_('Assigned'); ?>" />
				<?php
				} else {
				?>
						&nbsp;
				<?php
				}
				?>
				</td>
			<?php
			}
			?>
			<td align="center">
				<?php echo $row->xmldata->version; ?>
			</td>
			<td>
				<?php echo $row->xmldata->creationdate; ?>
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo JText::_('Author Information');?>::<?php echo $author_info; ?>">
					<?php echo @$row->xmldata->author != '' ? $row->xmldata->author : '&nbsp;'; ?>
				</span>
			</td>
		</tr>
		<?php
		}
		?>
	</tbody>
	</table>

<input type="hidden" name="option" value="com_templates" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::_('form.token'); ?>
</form>
