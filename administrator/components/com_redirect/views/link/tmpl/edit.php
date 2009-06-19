<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the behaviors.
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_redirect/media/css/');

// Build the toolbar.
$this->buildDefaultToolBar();

// Get the form fields.
$fields	= $this->form->getFields();
?>
<form action="<?php echo JRoute::_('index.php?option=com_redirect');?>" method="post" name="adminForm">
		<div class="col width-60">
			<div>
				<?php echo $fields['old_url']->label; ?><br />
				<?php echo $fields['old_url']->input; ?>
			</div>
			<br />
			<div>
				<?php echo $fields['new_url']->label; ?><br />
				<?php echo $fields['new_url']->input; ?>
			</div>
			<br />
			<div>
				<?php echo $fields['comment']->label; ?><br />
				<?php echo $fields['comment']->input; ?>
			</div>
		</div>

		<div class="col width-40">
			<fieldset>
				<legend><?php echo JText::_('Details'); ?></legend>

				<table class="adminlist">
					<tbody>
						<tr>
							<td>
								<?php echo $fields['published']->label; ?><br />
								<?php echo $fields['published']->input; ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $fields['created_date']->label; ?><br />
								<?php echo $fields['created_date']->input; ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $fields['updated_date']->label; ?><br />
								<?php echo $fields['updated_date']->input; ?>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
