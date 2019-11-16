<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$this->fieldsets = $this->form->getFieldsets('params');
$this->useCoreUI = true;

$input = Factory::getApplication()->input;

// In case of modal
$isModal  = $input->get('layout') === 'modal';
$layout   = $isModal ? 'modal' : 'edit';
$tmpl     = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_plugins&view=plugin&layout=' . $layout . $tmpl . '&extension_id=' . (int) $this->item->extension_id); ?>" method="post" name="adminForm" id="style-form" class="form-validate">
	<div>

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_PLUGINS_PLUGIN')); ?>

		<div class="row mt-2">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body">
						<?php if ($this->item->xml) : ?>
							<?php if ($this->item->xml->description) : ?>
								<h2>
								<?php
									if ($this->item->xml)
									{
										echo ($text = (string) $this->item->xml->name) ? Text::_($text) : $this->item->name;
									}
									else
									{
										echo Text::_('COM_PLUGINS_XML_ERR');
									}
								?>
								</h2>
								<div class="info-labels mb-1">
									<span class="badge badge-secondary">
										<?php echo $this->form->getValue('folder'); ?>
									</span> /
									<span class="badge badge-secondary">
										<?php echo $this->form->getValue('element'); ?>
									</span>
								</div>
								<div>
									<?php
									$this->fieldset    = 'description';
									$short_description = Text::_($this->item->xml->description);
									$this->fieldset    = 'description';
									$long_description  = LayoutHelper::render('joomla.edit.fieldset', $this);

									if (!$long_description)
									{
										$truncated = JHtmlString::truncate($short_description, 550, true, false);

										if (strlen($truncated) > 500)
										{
											$long_description  = $short_description;
											$short_description = JHtmlString::truncate($truncated, 250);

											if ($short_description == $long_description)
											{
												$long_description = '';
											}
										}
									}
									?>
									<p><?php echo $short_description; ?></p>
									<?php if ($long_description) : ?>
										<p class="readmore">
											<a href="#" onclick="document.querySelector('#tab-description').click();">
												<?php echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
											</a>
										</p>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<?php else : ?>
								<div class="alert alert-danger">
								<span class="fas fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('ERROR'); ?></span>
									<?php echo Text::_('COM_PLUGINS_XML_ERR'); ?>
								</div>
							<?php endif; ?>
						<?php
						$this->fieldset = 'basic';
						$html = LayoutHelper::render('joomla.edit.fieldset', $this);
						echo $html ? '<hr>' . $html : '';
						?>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="card">
					<div class="card-body">
						<?php
						// Set main fields.
						$this->fields = array(
							'enabled',
							'access',
							'ordering',
							'folder',
							'element',
							'note',
						); ?>
						<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if (isset($long_description) && $long_description != '') : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
			<?php echo $long_description; ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->fieldsets = array();
		$this->ignore_fieldsets = array('basic', 'description');
		echo LayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

