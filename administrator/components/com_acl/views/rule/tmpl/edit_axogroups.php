<?php /** $Id$ */ ?>

<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->axoGroups as $item) : ?>
	<li>
		<?php $eid = 'axogroup_'.$item->value; ?>
		<input type="checkbox" name="axo_group_ids[]" value="<?php echo $item->id;?>" id="<?php echo $eid;?>"
			<?php echo aclGroupChecked($this->acl['axo_groups'], $item->id); ?> />
		<label for="<?php echo $eid;?>" style="padding-left:<?php echo intval($item->level*15)+4; ?>px">
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
