<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
?>
<div class="reset<?php echo $this->pageclass_sfx; ?>">
	<div class="row justify-content-center">
		<div class="col-md-4">
			<?php if ($this->params->get('show_page_heading')) : ?>
				<div class="page-header">
					<h1>
						<?php echo $this->escape($this->params->get('page_heading')); ?>
					</h1>
				</div>
			<?php endif; ?>

			<form id="user-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="form-validate">
				<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
					<div>
						<p><?php echo JText::_($fieldset->label); ?></p>
						<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field) : ?>
							<div class="form-group">
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>

				<div>
					<button type="submit" class="btn btn-primary validate"><?php echo JText::_('JSUBMIT'); ?></button>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
