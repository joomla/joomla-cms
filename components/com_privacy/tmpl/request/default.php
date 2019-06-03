<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var PrivacyViewRequest $this */

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');

?>
<div class="request-form<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>
	<?php if ($this->sendMailEnabled) : ?>
		<form action="<?php echo Route::_('index.php?option=com_privacy&task=request.submit'); ?>" method="post" class="form-validate form-horizontal well">
			<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
				<fieldset>
					<?php if (!empty($fieldset->label)) : ?>
						<legend><?php echo Text::_($fieldset->label); ?></legend>
					<?php endif; ?>
					<?php echo $this->form->renderFieldset($fieldset->name); ?>
				</fieldset>
			<?php endforeach; ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary validate">
						<?php echo Text::_('JSUBMIT'); ?>
					</button>
				</div>
			</div>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php else : ?>
		<div class="alert alert-warning">
			<p><?php echo Text::_('COM_PRIVACY_WARNING_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'); ?></p>
		</div>
	<?php endif; ?>
</div>
