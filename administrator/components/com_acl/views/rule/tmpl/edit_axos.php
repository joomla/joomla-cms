<?php /** $Id$ */ defined('_JEXEC') or die();

	// Get the selected groups from the references
	$selected = $this->item->references->getAxos();
?>

<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->axos as $item) : ?>
	<li>
		<?php $eid = 'aco_'.$item->section_value.'_'.$item->value; ?>
		<input type="checkbox" name="axos[<?php echo $item->section_value;?>][]" value="<?php echo $item->id;?>" id="<?php echo $eid;?>"
			<?php echo in_array($item->id, $selected) ? 'checked="checked"' : ''; ?> />
		<label for="<?php echo $eid;?>">
			<?php echo $item->section_name; ?> -
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
