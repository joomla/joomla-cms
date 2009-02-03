<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_members&task=member.login'); ?>" method="post">

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

	<input type="hidden" name="option" value="com_members" />
	<input type="hidden" name="task" value="member.login" />
	<?php echo JHtml::_('form.token'); ?>
</form>