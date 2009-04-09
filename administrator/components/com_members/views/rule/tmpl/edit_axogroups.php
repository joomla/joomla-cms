<?php /** $Id: edit_axogroups.php 11323 2008-11-28 07:05:20Z eddieajau $ */ defined('_JEXEC') or die();

	// Get the selected groups from the references
	$selected = $this->item->references->getAxoGroups();
?>

<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->axoGroups as $item) : ?>
	<li>
		<?php $eid = 'axogroup_'.$item->id; ?>
		<input type="checkbox" name="axo_groups[]" value="<?php echo $item->id;?>" id="<?php echo $eid;?>"
			<?php echo in_array($item->id, $selected) ? 'checked="checked"' : ''; ?> />
		<label for="<?php echo $eid;?>" style="padding-left:<?php echo intval($item->level*15)+4; ?>px">
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
