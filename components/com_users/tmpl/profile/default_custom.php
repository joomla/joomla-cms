<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::register('users.spacer', array('JHtmlUsers', 'spacer'));

$fieldsets = $this->form->getFieldsets();

if (isset($fieldsets['core']))
{
	unset($fieldsets['core']);
}

if (isset($fieldsets['params']))
{
	unset($fieldsets['params']);
}

$tmp          = $this->data->jcfields ?? array();
$customFields = array();

foreach ($tmp as $customField)
{
	$customFields[$customField->name] = $customField;
}

?>
<?php foreach ($fieldsets as $group => $fieldset) : ?>
	<?php $fields = $this->form->getFieldset($group); ?>
	<?php if (count($fields)) : ?>
		<fieldset id="users-profile-custom-<?php echo $group; ?>" class="com-users-profile__custom users-profile-custom-<?php echo $group; ?>">
			<?php if (isset($fieldset->label) && ($legend = trim(Text::_($fieldset->label))) !== '') : ?>
				<legend><?php echo $legend; ?></legend>
			<?php endif; ?>
			<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
				<p><?php echo $this->escape(Text::_($fieldset->description)); ?></p>
			<?php endif; ?>
			<dl class="dl-horizontal">
				<?php foreach ($fields as $field) : ?>
					<?php if (!$field->hidden && $field->type !== 'Spacer') : ?>
						<dt>
							<?php echo $field->title; ?>
						</dt>
						<dd>
							<?php if (array_key_exists($field->fieldname, $customFields)) : ?>
								<?php echo $customFields[$field->fieldname]->value ?: Text::_('COM_USERS_PROFILE_VALUE_NOT_FOUND'); ?>
							<?php elseif (HTMLHelper::isRegistered('users.' . $field->id)) : ?>
								<?php echo HTMLHelper::_('users.' . $field->id, $field->value); ?>
							<?php elseif (HTMLHelper::isRegistered('users.' . $field->fieldname)) : ?>
								<?php echo HTMLHelper::_('users.' . $field->fieldname, $field->value); ?>
							<?php elseif (HTMLHelper::isRegistered('users.' . $field->type)) : ?>
								<?php echo HTMLHelper::_('users.' . $field->type, $field->value); ?>
							<?php else : ?>
								<?php echo HTMLHelper::_('users.value', $field->value); ?>
							<?php endif; ?>
						</dd>
					<?php endif; ?>
				<?php endforeach; ?>
			</dl>
		</fieldset>
	<?php endif; ?>
<?php endforeach; ?>
