<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Config\Administrator\View\Component\HtmlView $this */

$app = Factory::getApplication();
$template = $app->getTemplate();

Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('form.validate')
    ->useScript('keepalive');

if ($this->fieldsets) {
    HTMLHelper::_('bootstrap.framework');
}

$xml = $this->form->getXml();
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="component-form" method="post" class="form-validate main-card" name="adminForm" autocomplete="off">
    <div class="row main-card-columns">
        <?php // Begin Sidebar ?>
        <div class="col-md-3" id="sidebar">
            <button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false">
                 <span class="icon-align-justify" aria-hidden="true"></span>
                 <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
            </button>
            <div id="sidebar-nav" class="sidebar-nav">
                <?php echo $this->loadTemplate('navigation'); ?>
            </div>
        </div>
        <?php // End Sidebar ?>

        <div class="col-md-9" id="config">
            <?php if ($this->fieldsets) : ?>
                <?php $opentab = 0; ?>

                <?php echo HTMLHelper::_('uitab.startTabSet', 'configTabs', ['recall' => true, 'breakpoint' => 768]); ?>

                <?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
                    <?php
                    $hasChildren = $xml->xpath('//fieldset[@name="' . $name . '"]/fieldset');
                    $hasParent = $xml->xpath('//fieldset/fieldset[@name="' . $name . '"]');
                    $isGrandchild = $xml->xpath('//fieldset/fieldset/fieldset[@name="' . $name . '"]');
                    ?>

                    <?php $dataShowOn = ''; ?>
                    <?php if (!empty($fieldSet->showon)) : ?>
                        <?php $wa->useScript('showon'); ?>
                        <?php $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
                    <?php endif; ?>

                    <?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>

                    <?php if (!$isGrandchild && $hasParent) : ?>
                        <fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-menu options-form">
                            <legend><?php echo Text::_($fieldSet->label); ?></legend>
                            <div class="form-grid">
                    <?php elseif (!$hasParent) : ?>
                        <?php if ($opentab) : ?>
                            <?php if ($opentab > 1) : ?>
                                </div>
                                </fieldset>
                            <?php endif; ?>

                            <?php echo HTMLHelper::_('uitab.endTab'); ?>

                        <?php endif; ?>

                        <?php echo HTMLHelper::_('uitab.addTab', 'configTabs', $name, Text::_($label)); ?>

                        <?php $opentab = 1; ?>

                        <?php if (!$hasChildren) : ?>
                        <fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-menu options-form">
                            <legend><?php echo Text::_($fieldSet->label); ?></legend>
                            <div class="form-grid">
                            <?php $opentab = 2; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($fieldSet->description)) : ?>
                        <div class="tab-description alert alert-info">
                            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                            <?php echo Text::_($fieldSet->description); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasChildren) : ?>
                        <?php echo $this->form->renderFieldset($name, $name === 'permissions' ? ['hiddenLabel' => true, 'class' => 'revert-controls'] : []); ?>
                    <?php endif; ?>

                    <?php if (!$isGrandchild && $hasParent) : ?>
                        </div>
                    </fieldset>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($opentab) : ?>
                    <?php if ($opentab > 1) : ?>
                        </div>
                        </fieldset>
                    <?php endif; ?>
                    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php endif; ?>

                <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

            <?php else : ?>
                <div class="alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_CONFIG_COMPONENT_NO_CONFIG_FIELDS_MESSAGE'); ?>
                </div>
            <?php endif; ?>
        </div>

        <input type="hidden" name="id" value="<?php echo $this->component->id; ?>">
        <input type="hidden" name="component" value="<?php echo $this->component->option; ?>">
        <input type="hidden" name="return" value="<?php echo $this->return; ?>">
        <input type="hidden" name="task" value="">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
