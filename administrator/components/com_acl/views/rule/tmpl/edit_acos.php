<?php /** $Id$ */ defined('_JEXEC') or die();

	// Get the selected groups from the references
	$selected = $this->item->references->getAcos();
?>
<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->acos as $item) : ?>
	<li title="<?php echo $item->note;?>">
		<?php $eid = 'aco_'.$item->section_value.'_'.$item->value; ?>
		<input type="checkbox" name="acos[<?php echo $item->section_value;?>][]" value="<?php echo $item->id;?>" id="<?php echo $eid;?>"
			<?php echo in_array($item->id, $selected) ? 'checked="checked"' : ''; ?> />
		<label for="<?php echo $eid;?>">
			<strong><?php echo $item->section_name; ?></strong>:
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
