<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$app = Factory::getApplication();
$input = $app->input;

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = ['jmetadata', 'item_associations'];
$this->useCoreUI = true;

?>

<form action="<?php echo Route::_('index.php?option=com_csp&view=report&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<div class="row form-vertical mb-3">
		<div class="col-12 col-md-6">
			<?php echo $this->form->renderField('document_uri'); ?>
		</div>
	</div>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_CSP')); ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body form-vertical">
						<?php echo $this->form->renderField('blocked_uri'); ?>
						<?php echo $this->form->renderField('value'); ?>
						<?php if (!empty($this->item->script_sample)) : ?>
							<?php echo Text::sprintf('COM_CSP_SCRIPT_SAMPLE', $this->escape($this->item->script_sample)); ?>
							<br>
						<?php endif; ?>
						<?php if (!empty($this->item->line_number)) : ?>
							<?php echo Text::sprintf('COM_CSP_LINE_NUMBER', (int) $this->item->line_number); ?>
							<br>
						<?php endif; ?>
						<?php if (!empty($this->item->column_number)) : ?>
							<?php echo Text::sprintf('COM_CSP_COLUMN_NUMBER', (int) $this->item->column_number); ?>
							<br>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="card card-block">
					<div class="card-body">
					<?php echo $this->form->renderField('client'); ?>
					<?php echo $this->form->renderField('directive'); ?>
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
