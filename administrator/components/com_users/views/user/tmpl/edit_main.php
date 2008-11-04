<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>

<fieldset class="adminform">
	<?php if ($id = $this->item->get('id')) : ?>
	<legend><?php echo JText::sprintf('Record #%d', $id); ?></legend>
	<?php endif; ?>
	<table class="admintable">
		<tr>
			<td width="150" class="key">
				<label for="name">
					<?php echo JText::_('Name'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="name" id="name" class="inputbox validate required" size="40" value="<?php echo $this->item->get('name'); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="username">
					<?php echo JText::_('Username'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="username" id="username" class="inputbox validate-username2 required" size="40" value="<?php echo $this->item->get('username'); ?>" autocomplete="off" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="email">
					<?php echo JText::_('Email'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox validate-email required" type="text" name="email" id="email" size="40" value="<?php echo $this->item->get('email'); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="password">
					<?php echo JText::_('New Password'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password" id="password" size="40" value=""/>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="password2">
					<?php echo JText::_('Verify Password'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password2" id="password2" size="40" value=""/>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('Block User'); ?>
				@todo ACL this field
			</td>
			<td>
				<?php echo JHtml::_('select.booleanlist', 'block', '', $this->item->get('block')); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('Receive System Emails'); ?>
				@todo ACL this field
			</td>
			<td>
				<?php echo JHtml::_('select.booleanlist', 'sendEmail', '', $this->item->get('sendEmail')); ?>
			</td>
		</tr>
		<?php if ($id == 0) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('Register Date'); ?>
			</td>
			<td>
				<?php echo $this->item->get('registerDate');?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('Last Visit Date'); ?>
			</td>
			<td>
			<?php
			 	$lvisit = $this->item->get('lastvisitDate');
				if ($lvisit == '0000-00-00 00:00:00') {
					$lvisit = JText::_('Never');
				}
	 			echo $lvisit;
	 		?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</fieldset>
