<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Content\Site\View\CategoryForm\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_content.form-edit');

$this->tab_name = 'com-content-category-form';
$this->ignore_fieldsets = ['jmetadata', 'item_associations', 'workflow'];
$this->useCoreUI = true;

$params = $this->state->get('params');
$assoc = Associations::isEnabled();
$extensionassoc = array_key_exists('item_associations', $this->form->getFieldsets());
?>

<div class="edit category-edit">
    <?php if ($params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_content&task=category.save'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
        <?php echo HTMLHelper::_('uitab.startTabSet', $this->tab_name, ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'details', Text::_('JCATEGORY')); ?>
            <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </div>
                <div class="col-12 col-lg-4">
                    <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <fieldset id="fieldset-publishingdata" class="options-form">
                        <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
                        <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                    </fieldset>
                </div>
                <div class="col-12 col-lg-6">
                    <fieldset id="fieldset-metadata" class="options-form">
                        <legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
                        <?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
                    </fieldset>
                </div>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($assoc && $extensionassoc) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
                <fieldset id="fieldset-associations" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
                    <?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
                </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php if ($this->user && $this->user->authorise('core.admin', 'com_content')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'rules', Text::_('COM_CATEGORIES_FIELDSET_RULES')); ?>
                <fieldset id="fieldset-rules" class="options-form">
                    <legend><?php echo Text::_('COM_CATEGORIES_FIELDSET_RULES'); ?></legend>
                    <?php echo $this->form->getInput('rules'); ?>
                </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <?php echo $this->form->getInput('extension'); ?>
        <?php echo $this->form->renderControlFields(); ?>

        <div class="d-grid gap-2 d-sm-block mb-2">
            <button type="button" class="btn btn-primary" data-submit-task="category.apply">
                <span class="icon-check" aria-hidden="true"></span>
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button" class="btn btn-primary" data-submit-task="category.save">
                <span class="icon-check" aria-hidden="true"></span>
                <?php echo Text::_('JSAVEANDCLOSE'); ?>
            </button>
            <?php if ($this->showSaveAsCopy) : ?>
                <button type="button" class="btn btn-primary" data-submit-task="category.save2copy">
                    <span class="icon-copy" aria-hidden="true"></span>
                    <?php echo Text::_('JSAVEASCOPY'); ?>
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-danger" data-submit-task="category.cancel">
                <span class="icon-times" aria-hidden="true"></span>
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </form>
</div>
