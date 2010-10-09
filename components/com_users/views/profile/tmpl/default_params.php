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
<?php $fields = $this->form->getFieldset('params'); ?>
<?php if (count($fields)): ?>
<fieldset id="users-profile-custom">
	<legend><?php echo JText::_('COM_USERS_SETTINGS_FIELDSET_LABEL'); ?></legend>
	<dl>
	<?php foreach ($fields as $field):
		if (!$field->hidden) :?>
		<dt><?php echo $field->label; ?></dt>
		<dd>
			<?php
				if (empty($this->data->params[$field->fieldname])) {
					echo JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
				} else {
					if ($field->id == 'jform_params_helpsite') {
						$v_http = substr ($this->data->params[$field->fieldname], 0, 4);

						if($v_http == "http"){
							echo '<a href="'.$this->data->params[$field->fieldname].'">'.$this->data->params[$field->fieldname].'</a>';
						} else {
							echo '<a href="http://'.$this->data->params[$field->fieldname].'">'.$this->data->params[$field->fieldname].'</a>';
						}
					} else {
						echo $this->data->params[$field->fieldname];
					}
				}
			?>
		</dd>
		<?php endif;?>
	<?php endforeach;?>
	</dl>
</fieldset>
<?php endif;?>
