<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
        action="<?php echo Route::_('index.php?option=com_jed&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" enctype="multipart/form-data" name="adminForm" id="veldeveloperupdate-form"
        class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'update')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'update', Text::_('COM_JED_VEL_TAB_DEVELOPER_UPDATE', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('contact_fullname'); ?>
                <?php echo $this->form->renderField('contact_organisation'); ?>
                <?php echo $this->form->renderField('contact_email'); ?>
                <?php echo $this->form->renderField('vulnerable_item_name'); ?>
                <?php echo $this->form->renderField('vulnerable_item_version'); ?>
                <?php echo $this->form->renderField('extension_update'); ?>
                <?php echo $this->form->renderField('new_version_number'); ?>
                <?php echo $this->form->renderField('update_notice_url'); ?>
                <?php echo $this->form->renderField('changelog_url'); ?>
                <?php echo $this->form->renderField('download_url'); ?>
                <?php echo $this->form->renderField('consent_to_process'); ?>
                <?php echo $this->form->renderField('vel_item_id'); ?>
                <?php echo $this->form->renderField('update_data_source'); ?>
                <?php echo $this->form->renderField('update_date_submitted'); ?>
                <?php echo $this->form->renderField('update_user_ip'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Publishing', Text::_('COM_JED_GENERAL_PUBLISHING_LABEL', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <?php echo $this->form->renderField('created_by'); ?>
                <?php echo $this->form->renderField('modified_by'); ?>
                <?php echo $this->form->renderField('created'); ?>
                <?php echo $this->form->renderField('modified'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
