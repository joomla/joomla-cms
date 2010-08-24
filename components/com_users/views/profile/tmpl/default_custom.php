<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */
defined('_JEXEC') or die;
?>
<?php $fields = $this->form->getFieldset('profile'); ?>
<?php if (count($fields)): ?>
<fieldset id="users-profile-custom">
	<legend><?php echo JText::_('COM_USERS_PROFILE_CUSTOM_LEGEND'); ?></legend>
	<dl>
	<?php foreach ($fields as $field):
		if (!$field->hidden) :?>
		<dt><?php echo $field->label; ?></dt>
		<dd>
			<?php
				if (empty($this->data->profile[$field->fieldname])) {
					echo JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
				} else {
					if ($field->id == 'jform_profile_website') {
						$v_http = substr ($this->data->profile[$field->fieldname], 0, 4);

						if($v_http == "http"){
							echo '<a href="'.$this->data->profile[$field->fieldname].'">'.$this->data->profile[$field->fieldname].'</a>';
						} else {
							echo '<a href="http://'.$this->data->profile[$field->fieldname].'">'.$this->data->profile[$field->fieldname].'</a>';
						}
					} else {
						echo $this->data->profile[$field->fieldname];
					}
				}
			?>
		</dd>
		<?php endif;?>
	<?php endforeach;?>
	</dl>
</fieldset>
<?php endif;?>
