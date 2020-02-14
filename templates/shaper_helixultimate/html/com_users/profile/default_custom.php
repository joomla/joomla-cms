<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::register('users.spacer', array('JHtmlUsers', 'spacer'));

$fieldsets = $this->form->getFieldsets();

if (isset($fieldsets['core']))
{
	unset($fieldsets['core']);
}

if (isset($fieldsets['params']))
{
	unset($fieldsets['params']);
}

$tmp          = isset($this->data->jcfields) ? $this->data->jcfields : array();
$customFields = array();

foreach ($tmp as $customField)
{
	$customFields[$customField->name] = $customField;
}
?>
<?php foreach ($fieldsets as $group => $fieldset) : ?>
	<?php $fields = $this->form->getFieldset($group); ?>
	<?php if (count($fields)) : ?>
		<div class="users-profile-custom-<?php echo $group; ?>" id="users-profile-custom-<?php echo $group; ?>">
			<div class="mb-3">
				<?php if (isset($fieldset->label) && ($legend = trim(JText::_($fieldset->label))) !== '') : ?>
					<strong><?php echo $legend; ?></strong>
				<?php endif; ?>
				<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
					<div><?php echo $this->escape(JText::_($fieldset->description)); ?></span>
				<?php endif; ?>
			</div>
			<ul class="list-group">
					<?php foreach ($fields as $field) : ?>
						<?php if (!$field->hidden && $field->type !== 'Spacer') : ?>
							<li class="list-group-item">
								<strong><?php echo $field->title; ?></strong>:
								<?php if (key_exists($field->fieldname, $customFields)) : ?>
									<?php echo $customFields[$field->fieldname]->value ?: JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND'); ?>
								<?php elseif (JHtml::isRegistered('users.' . $field->id)) : ?>
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
<?php endforeach; ?>
