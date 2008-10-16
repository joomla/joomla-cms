<?php /** $Id$ */ ?>

<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->aroGroups as $item) : ?>
	<li>
		<?php $eid = 'arogroup_'.$item->value; ?>
		<input type="checkbox" name="aro_group_ids[]" value="<?php echo $item->id;?>" id="<?php echo $eid;?>"
			<?php echo aclGroupChecked($this->acl['aro_groups'], $item->id); ?> />
		<label for="<?php echo $eid;?>" style="padding-left:<?php echo intval(($item->level-2)*15)+4; ?>px">
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
