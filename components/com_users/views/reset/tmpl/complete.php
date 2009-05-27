<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

JHtml::_('behavior.mootools');
JHtml::_('behavior.formvalidation');
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.complete'); ?>" method="post" class="form-validate">

<?php foreach ($this->form->getGroups() as $group): ?>
	<fieldset>
<?php foreach ($this->form->getFields($group, $group) as $name => $field): ?>
		<dt><?php echo $field->label; ?></dt>
		<dd><?php echo $field->input; ?></dd>
<?php endforeach; ?>
	</fieldset>
<?php endforeach; ?>

	<button type="submit"><?php echo JText::_('BUTTON_SUBMIT'); ?></button>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="reset.complete" />
	<?php echo JHtml::_('form.token'); ?>
</form>