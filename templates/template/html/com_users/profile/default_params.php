<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<?php $fields = $this->form->getFieldset('params'); ?>
<?php if (count($fields)) : ?>
	<div id="users-profile-params">
		<div class="mb-3">
			<strong><?php echo JText::_('COM_USERS_SETTINGS_FIELDSET_LABEL'); ?></strong>
		</div>
		<ul class="list-group">
			<?php foreach ($fields as $field) : ?>
					<?php if (!$field->hidden) : ?>
						<li class="list-group-item">
							<strong><?php echo $field->title; ?></strong>:
							<?php if (JHtml::isRegistered('users.' . $field->id)) : ?>
								<?php echo JHtml::_('users.' . $field->id, $field->value); ?>
							<?php elseif (JHtml::isRegistered('users.' . $field->fieldname)) : ?>
								<?php echo JHtml::_('users.' . $field->fieldname, $field->value); ?>
							<?php elseif (JHtml::isRegistered('users.' . $field->type)) : ?>
								<?php echo JHtml::_('users.' . $field->type, $field->value); ?>
							<?php else : ?>
								<?php echo JHtml::_('users.value', $field->value); ?>
							<?php endif; ?>
						</li>
					<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>