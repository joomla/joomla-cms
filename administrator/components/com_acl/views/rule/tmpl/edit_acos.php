<?php /** $Id$ */ ?>

<ul class="checklist scroll" style="height:280px">
	<?php foreach ($this->acos as $item) : ?>
	<li title="<?php echo $item->note;?>">
		<?php $eid = 'aco_'.$item->section_value.'_'.$item->value; ?>
		<input type="checkbox" name="aco_array[<?php echo $item->section_value;?>][]" value="<?php echo $item->value;?>" id="<?php echo $eid;?>"
			<?php echo aclObjectChecked($this->acl['aco'], $item->section_value, $item->value); ?> />
		<label for="<?php echo $eid;?>">
			<strong><?php echo $item->section_name; ?></strong>:
			<?php echo $item->name; ?>
		</label>
	</li>
	<?php endforeach; ?>
</ul>
