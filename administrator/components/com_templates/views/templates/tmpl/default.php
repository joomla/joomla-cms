<?php defined('_JEXEC') or die; ?>

<?php
	$user = & JFactory :: getUser();

	JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_('Num'); ?>
			</th>
			<th colspan="2">
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
			<th width="10%" class="center">
				<?php echo JText::_('Version'); ?>
			</th>
			<th width="15%">
				<?php echo JText::_('Date'); ?>
			</th>
			<th width="25%" >
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
$i = 0;
foreach($this->rows as $row) {
	$author_info = @ $row->xmldata->authorEmail . '<br />' . @ $row->xmldata->authorUrl;
	$img_path = ($this->client->id == 1 ? JURI::root().'administrator' : JURI::root()).'/templates/'.$row->name.'/template_thumbnail.png';
?>
		<tr class="<?php echo 'row'. $k; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td width="5">
				<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->name; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo $row->name;?>::
<img border=&quot;1&quot; src=&quot;<?php echo $img_path; ?>&quot; name=&quot;imagelib&quot; alt=&quot;<?php echo JText::_( 'No preview available' ); ?>&quot; width=&quot;206&quot; height=&quot;145&quot; />">
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=edit&template=' . $row->name . '&client=' . $this->client->id); ?>"><?php echo $row->name;?></a>
				</span>
			</td>
			<?php
			if ($this->client->id == 1) {
			?>
				<td class="center">
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
				<td class="center">
				<?php
				if ($row->home == 1) {
				?>
						<img src="templates/bluestork/images/menu/icon-16-default.png" alt="<?php echo JText::_('Default'); ?>" />
				<?php
				} else {
				?>
						&nbsp;
				<?php
				}
				?>
				</td>
				<td class="center">
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
			<td class="center">
				<?php echo $row->version; ?>
			</td>
			<td>
				<?php echo $row->creationdate; ?>
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo JText::_('Author Information');?>::<?php echo $author_info; ?>">
					<?php echo @$row->author != '' ? $row->author : '&nbsp;'; ?>
				</span>
			</td>
		</tr>
		<?php
		$i++;
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
