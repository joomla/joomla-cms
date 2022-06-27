<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$this->fieldsets = $this->form->getFieldsets('params');
$this->useCoreUI = true;

$input = Factory::getApplication()->input;

// In case of modal
$isModal  = $input->get('layout') === 'modal';
$layout   = $isModal ? 'modal' : 'edit';
$tmpl     = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_plugins&view=plugin&layout=' . $layout . $tmpl . '&extension_id=' . (int) $this->item->extension_id); ?>" method="post" name="adminForm" id="style-form" aria-label="<?php echo Text::_('COM_PLUGINS_FORM_EDIT'); ?>" class="form-validate">
	<div class="main-card">

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_PLUGINS_PLUGIN')); ?>

		<div class="row mt-2">
			<div class="col-lg-9">
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
							<span class="badge bg-secondary">
								<?php echo $this->form->getValue('folder'); ?>
							</span> /
							<span class="badge bg-secondary">
								<?php echo $this->form->getValue('element'); ?>
							</span>
						</div>
						<div>
							<?php
							$this->fieldset    = 'description';
							$short_description = Text::_($this->item->xml->description);
							$long_description  = LayoutHelper::render('joomla.edit.fieldset', $this);

							if (!$long_description)
							{
								$truncated = HTMLHelper::_('string.truncate', $short_description, 550, true, false);

								if (strlen($truncated) > 500)
								{
									$long_description  = $short_description;
									$short_description = HTMLHelper::_('string.truncate', $truncated, 250);

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
									<a href="#" onclick="document.querySelector('[aria-controls=description]').click();">
										<?php echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
									</a>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php else : ?>
						<div class="alert alert-danger">
						<span class="icon-exclamation-triangle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('ERROR'); ?></span>
							<?php echo Text::_('COM_PLUGINS_XML_ERR'); ?>
						</div>
					<?php endif; ?>
				<?php
				$this->fieldset = 'basic';
				$html = LayoutHelper::render('joomla.edit.fieldset', $this);
				echo $html ? '<hr>' . $html : '';
				?>
			</div>
			<div class="col-lg-3">
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
