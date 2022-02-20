<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
?>
<div class="container-popup">
	<form
		class="form-validate"
		id="download-form"
		name="adminForm"
		action="<?php echo JRoute::_('index.php?option=com_banners&task=tracks.display&format=raw'); ?>"
		method="post">

		<fieldset class="adminform">
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset() as $field) : ?>
					<li>
						<?php echo $this->form->getLabel($field->fieldname); ?>
						<?php echo $this->form->getInput($field->fieldname); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>

		<button class="hidden"
			id="closeBtn"
			type="button"
			onclick="window.parent.jQuery('#modal-download').modal('hide');">
		</button>
		<button class="hidden"
			id="exportBtn"
			type="button"
			onclick="this.form.submit();window.top.setTimeout('window.parent.jQuery(\'#downloadModal\').modal(\'hide\')', 700);">
		</button>
	</form>
</div>
