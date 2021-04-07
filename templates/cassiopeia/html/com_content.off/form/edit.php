<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_content.form-edit');

$this->tab_name = 'com-content-form';
$this->ignore_fieldsets = array('image-intro', 'image-full', 'jmetadata', 'item_associations');
$this->useCoreUI = true;

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);

if (!$editoroptions)
{
	$params->show_urls_images_frontend = '0';
}
?>
<div class="edit item-page">
	<?php if ($params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo Route::_('index.php?option=com_content&a_id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<fieldset>

			<?php echo $this->form->renderField('title'); ?>

			<?php echo $this->form->renderField('untertitel', 'com_fields'); ?>

			<?php if (is_null($this->item->id)) : ?>
				<?php $this->form->setFieldAttribute('alias', 'type', 'hidden'); ?>
				<?php echo $this->form->renderField('alias'); ?>
			<?php endif; ?>

			<?php echo $this->form->renderField('articletext'); ?>

			<?php if ($this->captchaEnabled) : ?>
				<?php echo $this->form->renderField('captcha'); ?>
			<?php endif; ?>

			<?php if ($params->get('show_urls_images_frontend')) : ?>
				<?php echo $this->form->renderField('image_intro', 'images'); ?>
			<?php endif; ?>

			<?php $this->form->setFieldAttribute('catid', 'type', 'hidden'); ?>
			<?php echo $this->form->renderField('catid'); ?>

			<?php if ($this->item->params->get('access-change')) : ?>
				<?php echo $this->form->renderField('featured'); ?>
				<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
					<?php echo $this->form->renderField('featured_up'); ?>
					<?php echo $this->form->renderField('featured_down'); ?>
					<?php echo $this->form->renderField('publish_up'); ?>
					<?php echo $this->form->renderField('publish_down'); ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php if (Multilanguage::isEnabled()) : ?>
				<?php $this->form->setFieldAttribute('language', 'type', 'hidden'); ?>
				<?php echo $this->form->renderField('language'); ?>
			<?php endif; ?>

			<input type="hidden" name="task" value="">
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</fieldset>
		<div class="mb-2">
			<button type="button" class="btn btn-primary" data-submit-task="article.save">
				<span class="icon-check" aria-hidden="true"></span>
				<?php echo Text::_('JSAVE'); ?>
			</button>
			<button type="button" class="btn btn-danger" data-submit-task="article.cancel">
				<span class="icon-times" aria-hidden="true"></span>
				<?php echo Text::_('JCANCEL'); ?>
			</button>
			<?php if ($params->get('save_history', 0) && $this->item->id) : ?>
				<?php echo $this->form->getInput('contenthistory'); ?>
			<?php endif; ?>
		</div>
	</form>
</div>
