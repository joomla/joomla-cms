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

$app   = Factory::getApplication();
$user  = $app->getIdentity();
$input = $app->getInput();
$lang  = $this->getLanguage()->getTag();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>

<form action="<?php echo Route::_('index.php?option=com_guidedtours&view=tour&layout=edit&id=' .
    (int) $this->item->id); ?>" method="post" name="adminForm" id="guidedtours-form" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <?php if ($this->item->id != 0 && strpos($this->item->title, 'GUIDEDTOUR') !== false) : ?>
        <div class="row title-alias form-vertical mb-3">
            <div class="col-12">
                <?php $this->form->setFieldAttribute('title_translation', 'label', Text::sprintf('COM_GUIDEDTOURS_TITLE_TRANSLATION', $lang)); ?>
                <?php echo $this->form->renderField('title_translation'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_GUIDEDTOURS_NEW_TOUR') : Text::_('COM_GUIDEDTOURS_EDIT_TOUR')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('url'); ?>
                <?php echo $this->form->renderField('description'); ?>

                <?php if ($this->item->id != 0 && strpos($this->item->description, 'GUIDEDTOUR') !== false) : ?>
                    <?php $this->form->setFieldAttribute('description_translation', 'label', Text::sprintf('COM_GUIDEDTOURS_DESCRIPTION_TRANSLATION', $lang)); ?>
                    <?php echo $this->form->renderField('description_translation'); ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <?php
                    // Set main fields.
                    $this->fields = [
                        'published',
                        'access',
                        'language',
                        'extensions',
                        'note',
                    ];

                    echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
        <div class="row">
            <div class="col-12 col-lg-8">
                <fieldset id="fieldset-publishingdata" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
                    <div>
                        <?php
                        $this->fields = [];
                        echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
