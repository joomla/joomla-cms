<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<?php $fields = $this->form->getFieldset('params'); ?>
<?php if (count($fields)) : ?>
	<fieldset id="users-profile-custom" class="com-users-profile__params">
		<legend><?php echo Text::_('COM_USERS_SETTINGS_FIELDSET_LABEL'); ?></legend>
		<dl class="dl-horizontal">
			<?php foreach ($fields as $field) : ?>
				<?php if (!$field->hidden) : ?>
					<dt>
						<?php echo $field->title; ?>
					</dt>
					<dd>
						<?php if (HTMLHelper::isRegistered('users.' . $field->id)) : ?>
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
