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
    method="post" enctype="multipart/form-data" name="adminForm" id="review-form" class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'rev')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rev', Text::_('COM_JED_TAB_REV', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_JED_FIELDSET_REV'); ?></legend>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('extension_id'); ?>
                <?php echo $this->form->renderField('supply_option_id'); ?>
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('alias'); ?>
                <?php echo $this->form->renderField('body'); ?>
                <?php echo $this->form->renderField('functionality'); ?>
                <?php echo $this->form->renderField('functionality_comment'); ?>
                <?php echo $this->form->renderField('ease_of_use'); ?>
                <?php echo $this->form->renderField('ease_of_use_comment'); ?>
                <?php echo $this->form->renderField('support'); ?>
                <?php echo $this->form->renderField('support_comment'); ?>
                <?php echo $this->form->renderField('documentation'); ?>
                <?php echo $this->form->renderField('documentation_comment'); ?>
                <?php echo $this->form->renderField('value_for_money'); ?>
                <?php echo $this->form->renderField('value_for_money_comment'); ?>
                <?php echo $this->form->renderField('overall_score'); ?>
                <?php echo $this->form->renderField('used_for'); ?>
                <?php echo $this->form->renderField('flagged'); ?>
                <?php echo $this->form->renderField('ip_address'); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('created_on'); ?>
                <?php echo $this->form->renderField('created_by'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
