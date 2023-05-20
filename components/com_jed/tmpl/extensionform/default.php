<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Jed\Component\Jed\Site\Helper\JedHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jed', JPATH_SITE);

$user    = JedHelper::getUser();
$canEdit = JedHelper::canUserEdit($this->item, $user);


if ($this->item->state == 1) {
    $state_string = 'Publish';
    $state_value = 1;
} else {
    $state_string = 'Unpublish';
    $state_value = 0;
}
$canState = JedHelper::getUser()->authorise('core.edit.state', 'com_jed');
?>

<div class="extension-edit front-end-edit">
    <?php if (!$canEdit) : ?>
        <h3>
            <?php throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403); ?>
        </h3>
    <?php else : ?>
        <?php if (!empty($this->item->id)) : ?>
            <h1><?php echo Text::sprintf('JACTION_EDIT_TITLE', $this->item->id); ?></h1>
        <?php else : ?>
            <h1><?php echo Text::_('JGLOBAL_FIELD_ADD'); ?></h1>
        <?php endif; ?>

        <form id="form-extension"
              action="<?php echo Route::_('index.php?option=com_jed&task=extensionform.save'); ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'extension')); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extension', Text::_('COM_JED_TAB_EXTENSION', true)); ?>
        <?php echo $this->form->renderField('id'); ?>

        <?php echo $this->form->renderField('title'); ?>

        <?php echo $this->form->renderField('alias'); ?>

        <?php echo $this->form->renderField('published'); ?>

        <?php echo $this->form->renderField('created_by'); ?>

        <?php echo $this->form->renderField('modified_by'); ?>

        <?php echo $this->form->renderField('created_on'); ?>

        <?php echo $this->form->renderField('modified_on'); ?>

        <?php echo $this->form->renderField('joomla_versions'); ?>

        <?php echo $this->form->renderField('popular'); ?>

        <?php echo $this->form->renderField('requires_registration'); ?>

        <?php echo $this->form->renderField('gpl_license_type'); ?>

        <?php echo $this->form->renderField('jed_internal_note'); ?>

        <?php echo $this->form->renderField('can_update'); ?>

        <?php echo $this->form->renderField('video'); ?>

        <?php echo $this->form->renderField('version'); ?>

        <?php echo $this->form->renderField('uses_updater'); ?>

        <?php echo $this->form->renderField('includes'); ?>

        <?php echo $this->form->renderField('approved'); ?>

        <?php echo $this->form->renderField('approved_time'); ?>

        <?php echo $this->form->renderField('second_contact_email'); ?>

        <?php echo $this->form->renderField('jed_checked'); ?>

        <?php echo $this->form->renderField('uses_third_party'); ?>

        <?php echo $this->form->renderField('primary_category_id'); ?>

        <?php echo $this->form->renderField('logo'); ?>

        <?php echo $this->form->renderField('approved_notes'); ?>

        <?php echo $this->form->renderField('approved_reason'); ?>

        <?php echo $this->form->renderField('published_notes'); ?>

        <?php echo $this->form->renderField('published_reason'); ?>

    <div class="control-group">
        <?php if (!$canState) : ?>
            <div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
            <div class="controls"><?php echo $state_string; ?></div>
            <input type="hidden" name="jform[state]" value="<?php echo $state_value; ?>" />
        <?php else : ?>
            <div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('state'); ?></div>
        <?php endif; ?>
    </div>

        <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <div class="control-group">
                <div class="controls">

                    <?php if ($this->canSave) : ?>
                        <button type="submit" class="validate btn btn-primary">
                            <span class="fas fa-check" aria-hidden="true"></span>
                            <?php echo Text::_('JSUBMIT'); ?>
                        </button>
                    <?php endif; ?>
                    <a class="btn btn-danger"
                       href="<?php echo Route::_('index.php?option=com_jed&task=extensionform.cancel'); ?>"
                       title="<?php echo Text::_('JCANCEL'); ?>">
                       <span class="fas fa-times" aria-hidden="true"></span>
                        <?php echo Text::_('JCANCEL'); ?>
                    </a>
                </div>
            </div>

            <input type="hidden" name="option" value="com_jed"/>
            <input type="hidden" name="task"
                   value="extensionform.save"/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php endif; ?>
</div>
