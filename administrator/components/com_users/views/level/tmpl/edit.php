<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_users/media/css/');
?>

<script type="text/javascript">
<!--
function submitbutton(task)
{
	if (task == 'level.cancel' || document.formvalidator.isValid($('level-form'))) {
		submitform(task);
	}
}

window.addEvent('domready', function(){
	$('user-groups').getElements('input').each(function(i){
		// Event to check all child groups.
		i.addEvent('check', function(e){
			// Check the child groups.
			$('user-groups').getElements('input').each(function(c){
				if (this.getProperty('rel') == c.id) {
					c.setProperty('checked', true);
					c.setProperty('disabled', true);
					c.fireEvent('check');
				}
			}.bind(this));
		}.bind(i));

		// Event to uncheck all the parent groups.
		i.addEvent('uncheck', function(e){
			// Uncheck the parent groups.
			$('user-groups').getElements('input').each(function(c){
				if (c.getProperty('rel') == this.id) {
					c.setProperty('checked', false);
					c.setProperty('disabled', false);
					c.fireEvent('uncheck');
				}
			}.bind(this));
		}.bind(i));

		// Bind to the click event to check/uncheck child/parent groups.
		i.addEvent('click', function(e){
			// Check the child groups.
			$('user-groups').getElements('input').each(function(c){
				if (this.getProperty('rel') == c.id) {
					c.setProperty('checked', true);
					if (this.getProperty('checked')) {
						c.setProperty('disabled', true);
					} else {
						c.setProperty('disabled', false);
					}
					c.fireEvent('check');
				}
			}.bind(this));

			// Uncheck the parent groups.
			$('user-groups').getElements('input').each(function(c){
				if (c.getProperty('rel') == this.id) {
					c.setProperty('checked', false);
					c.setProperty('disabled', false);
					c.fireEvent('uncheck');
				}
			}.bind(this));
		}.bind(i));

		// Initialize the widget.
		if (i.getProperty('checked')) {
			i.fireEvent('click');
		}
	});
});
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_users'); ?>" method="post" name="adminForm" id="level-form" class="form-validate">
	<fieldset style="width:45%;float:left">
		<legend><?php echo JText::_('Users_Level_Details');?></legend>

		<ol>
<?php if (!$this->item->getSectionId()) : ?>
			<li>
				<?php echo $this->form->getLabel('section_id'); ?><br />
				<?php echo $this->form->getInput('section_id'); ?>
			</li>
<?php endif; ?>
			<li>
				<?php echo $this->form->getLabel('title'); ?><br />
				<?php echo $this->form->getInput('title'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset id="user-groups">
		<legend><?php echo JText::_('Users_User_Groups_Having_Access');?></legend>
		<?php echo JHtml::_('access.usergroups', 'jform[groups]', $this->item->getUserGroups()); ?>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
