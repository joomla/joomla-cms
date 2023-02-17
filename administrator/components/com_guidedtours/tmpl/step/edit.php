<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\GenericDataException;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$app     = Factory::getApplication();
$tour_id = $app->getUserState('com_guidedtours.tour_id');
$lang    = Factory::getLanguage()->getTag();

if (empty($tour_id)) {
    throw new GenericDataException("\nThe Tour id was not set!\n", 500);
}
?>

<form action="<?php echo Route::_('index.php?option=com_guidedtours&view=step&layout=edit&id=' .
    (int) $this->item->id); ?>" method="post" name="adminForm" id="guidedtour-dates-form" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <?php if ($this->item->id != 0 && strpos($this->item->title, 'GUIDEDTOUR') !== false) : ?>
        <div class="row title-alias form-vertical mb-3">
            <div class="col-12">
                <?php $this->form->setFieldAttribute('title_translation', 'label', Text::sprintf('COM_GUIDEDTOURS_STEP_TITLE_TRANSLATION', $lang)); ?>
                <?php echo $this->form->renderField('title_translation'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_GUIDEDTOURS_STEP_NEW_STEP') : Text::_('COM_GUIDEDTOURS_STEP_EDIT_STEP')); ?>
        <div class="row">
            <div class="col-md-9">
                <?php echo $this->form->renderField('description'); ?>

                <?php if ($this->item->id != 0 && strpos($this->item->description, 'GUIDEDTOUR') !== false) : ?>
                    <?php $this->form->setFieldAttribute('description_translation', 'label', Text::sprintf('COM_GUIDEDTOURS_STEP_DESCRIPTION_TRANSLATION', $lang)); ?>
                    <?php echo $this->form->renderField('description_translation'); ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                <?php echo $this->form->renderField('position'); ?>
                <?php echo $this->form->renderField('target'); ?>
                <?php echo $this->form->renderField('type'); ?>
                <?php echo $this->form->renderField('url'); ?>
                <?php echo $this->form->renderField('interactive_type'); ?>
                <?php $this->form->setValue('tour_id', null, $tour_id); ?>
                <?php echo $this->form->renderField('tour_id'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
        <div class="row">
            <div class="col-12 col-lg-8">
                <fieldset id="fieldset-publishingdata" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
                    <div>
                        <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_GUIDEDTOURS_RULES_TAB')); ?>
        <div class="row">
            <fieldset id="fieldset-rules" class="options-form">
                <legend><?php echo Text::_('COM_GUIDEDTOURS_RULES_TAB'); ?></legend>
                <?php echo $this->form->getInput('rules'); ?>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>

    </div>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
