<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post">

	<?php foreach ($this->form->getGroups() as $group): ?>
	<fieldset>
		<?php foreach ($this->form->getFields($group, $group) as $name => $field): ?>
			<?php if (!$field->hidden): ?>
				<dt><?php echo $field->label; ?></dt>
				<dd><?php echo $field->input; ?></dd>
			<?php endif; ?>
		<?php endforeach; ?>
	</fieldset>
	<?php endforeach; ?>

	<button type="submit">Submit</button>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<?php echo JHtml::_('form.token'); ?>
</form>