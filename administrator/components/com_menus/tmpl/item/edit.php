<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
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

$this->useCoreUI = true;

Text::script('ERROR');
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');

$this->document->addScriptOptions('menu-item', ['itemId' => (int) $this->item->id]);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_menus.admin-item-edit');

$assoc = Associations::isEnabled();
$input = Factory::getApplication()->input;

// In case of modal
$isModal  = $input->get('layout') === 'modal';
$layout   = $isModal ? 'modal' : 'edit';
$tmpl     = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
$clientId = $this->state->get('item.client_id', 0);
$lang     = Factory::getLanguage()->getTag();

// Load mod_menu.ini file when client is administrator
if ($clientId === 1) {
    Factory::getLanguage()->load('mod_menu', JPATH_ADMINISTRATOR);
}
?>
<form action="<?php echo Route::_('index.php?option=com_menus&view=item&client_id=' . $clientId . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" aria-label="<?php echo Text::_('COM_MENUS_ITEM_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <?php // Add the translation of the menu item title when client is administrator ?>
    <?php if ($clientId === 1 && $this->item->id != 0) : ?>
        <div class="row title-alias form-vertical mb-3">
            <div class="col-12">
                <div class="control-group">
                    <div class="control-label">
                        <label for="menus_title_translation"><?php echo Text::sprintf('COM_MENUS_TITLE_TRANSLATION', $lang); ?></label>
                    </div>
                    <div class="controls">
                        <input id="menus_title_translation" class="form-control" value="<?php echo Text::_($this->item->title); ?>" readonly="readonly" type="text">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-card">

        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_MENUS_ITEM_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php
                echo $this->form->renderField('type');

                if ($this->item->type == 'alias') {
                    echo $this->form->renderField('aliasoptions', 'params');
                }

                if ($this->item->type == 'separator') {
                    echo $this->form->renderField('text_separator', 'params');
                }

                echo $this->form->renderFieldset('request');

                if ($this->item->type == 'url') {
                    $this->form->setFieldAttribute('link', 'readonly', 'false');
                    $this->form->setFieldAttribute('link', 'required', 'true');
                }

                echo $this->form->renderField('link');

                if ($this->item->type == 'alias') {
                    echo $this->form->renderField('alias_redirect', 'params');
                }

                echo $this->form->renderField('browserNav');
                echo $this->form->renderField('template_style_id');

                if (!$isModal && $this->item->type == 'container') {
                    echo $this->loadTemplate('container');
                }
                ?>
            </div>
            <div class="col-lg-3">
                <?php
                    // Set main fields.
                    $this->fields = [
                        'id',
                        'client_id',
                        'menutype',
                        'parent_id',
                        'menuordering',
                        'published',
                        'home',
                        'publish_up',
                        'publish_down',
                        'access',
                        'language',
                        'note',
                    ];

                    if ($this->item->type != 'component') {
                        $this->fields = array_diff($this->fields, ['home']);
                        $this->form->setFieldAttribute('publish_up', 'showon', '');
                        $this->form->setFieldAttribute('publish_down', 'showon', '');
                    }

                    echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        $this->fieldsets = [];
        $this->ignore_fieldsets = ['aliasoptions', 'request', 'item_associations'];
        echo LayoutHelper::render('joomla.edit.params', $this);
        ?>

        <?php if (!$isModal && $assoc && $this->state->get('item.client_id') != 1) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
            <fieldset id="fieldset-associations" class="options-form">
            <legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
            <div>
            <?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
            </div>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php elseif ($isModal && $assoc) : ?>
            <div class="hidden"><?php echo LayoutHelper::render('joomla.edit.associations', $this); ?></div>
        <?php endif; ?>

        <?php if (!empty($this->modules)) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'modules', Text::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT')); ?>
            <fieldset id="fieldset-modules" class="options-form">
                <legend><?php echo Text::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT'); ?></legend>
                <div>
                <?php echo $this->loadTemplate('modules'); ?>
                </div>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
    <input type="hidden" name="menutype" value="<?php echo $input->get('menutype', '', 'cmd'); ?>">
    <?php echo $this->form->getInput('component_id'); ?>
    <?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" id="fieldtype" name="fieldtype" value="">
</form>
