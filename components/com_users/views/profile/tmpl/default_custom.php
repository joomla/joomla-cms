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

$fieldsets = $this->form->getFieldsets();
if (isset($fieldsets['core']))   unset($fieldsets['core']);
if (isset($fieldsets['params'])) unset($fieldsets['params']);

foreach ($fieldsets as $group => $fieldset): // Iterate through the form fieldsets
	$fields = $this->form->getFieldset($group);
	if (count($fields)):
?>
<fieldset id="users-profile-custom" class="users-profile-custom-<?php echo $group;?>">
	<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
	<legend><?php echo JText::_($fieldset->label); ?></legend>
	<?php endif;?>
	<dl>
	<?php foreach ($fields as $field):
		if (!$field->hidden) :?>
		<dt><?php echo $field->label; ?></dt>
		<dd>
			<?php
				if (!$value = trim($field->value)) {
					echo JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
				} else {
					if ($field->id == 'jform_profile_website') {
						$v_http = substr ($value, 0, 4);

						if($v_http == "http"){
							echo '<a href="'.$value.'">'.$value.'</a>';
						} else {
							echo '<a href="http://'.$value.'">'.$value.'</a>';
						}
					} else {
						if (method_exists($field, 'getText')) {
							if (($value = $field->getText()) === null) {
								$value = JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
							}
						}
						echo $value;
					}
				}
			?>
		</dd>
		<?php endif;?>
	<?php endforeach;?>
	</dl>
</fieldset>
	<?php endif;?>
<?php endforeach;?>
