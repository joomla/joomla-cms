<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Workflow\Administrator\View\Workflow\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$app = Factory::getApplication();
$user = $app->getIdentity();
$input = $app->getInput();

// In case of modal
$isModal  = $input->get('layout') === 'modal';
$layout   = $isModal ? 'modal' : 'edit';
$tmpl     = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
$clientId = $this->state->get('item.client_id', 0);
$lang     = $this->getLanguage()->getTag();
?>

<form action="<?php echo Route::_('index.php?option=com_workflow&view=workflow&extension=' . $input->getCmd('extension') . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="workflow-form" aria-label="<?php echo Text::_('COM_WORKFLOW_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <?php // Add the translation of the workflow item title when client is administrator ?>
    <?php if ($clientId === 0 && $this->item->id != 0) : ?>
        <div class="row title-alias form-vertical mb-3">
            <div class="col-md-6">
                <div class="control-group">
                    <div class="control-label">
                        <label for="workflow_title_translation"><?php echo Text::sprintf('COM_WORKFLOW_TITLE_TRANSLATION', $lang); ?></label>
                    </div>
                    <div class="controls">
                        <input id="workflow_title_translation" class="form-control" value="<?php echo Text::_($this->item->title); ?>" readonly="readonly" type="text">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('COM_WORKFLOW_DESCRIPTION'));?>
        <div class="row">
            <div class="col-lg-9">
                <div class="form-vertical">
                    <?php echo $this->form->renderField('description'); ?>
                </div>
            </div>
            <div class="col-lg-3">
                <fieldset class="form-vertical">
                    <?php echo $this->form->renderField('published'); ?>
                    <?php echo $this->form->renderField('default'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($user->authorise('core.admin', $this->extension)) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_WORKFLOW_RULES_TAB')); ?>
            <fieldset id="fieldset-rules" class="options-form">
                <legend><?php echo Text::_('COM_WORKFLOW_RULES_TAB'); ?></legend>
                <?php echo $this->form->getInput('rules'); ?>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
    <?php echo $this->form->getInput('extension'); ?>
    <input type="hidden" name="task" value="workflow.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
