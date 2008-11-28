<?php /** $Id$ */ defined('_JEXEC') or die();

	// Get the selected groups from the references
	$selected = $this->item->references->getAroGroups();
?>

<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->aroGroups as $item) : ?>
	<li>
		<?php $eid = 'arogroup_'.$item->value; ?>
		<input type="checkbox" name="aro_groups[]" value="<?php echo $item->id;?>" id="<?php echo $eid;?>"
			<?php echo in_array($item->id, $selected) ? 'checked="checked"' : ''; ?> />
		<label for="<?php echo $eid;?>" style="padding-left:<?php echo intval(($item->level-2)*15)+4; ?>px">
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
