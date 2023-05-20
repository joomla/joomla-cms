<?php

/**
 * @package         JED
 *
 * @subpackage      Tickets
 *
 * @copyright   (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
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
        method="post" enctype="multipart/form-data" name="adminForm" id="messagetemplate-form"
        class="form-validate form-horizontal">
    <?php echo $this->form->getLabel('title'); ?><br/>
    <?php echo $this->form->getInput('title'); ?><br/>

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'template')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'template', Text::_('COM_JED_GENERAL_TEMPLATE_LABEL', true)); ?>

    <div class="row">
        <div class="col-lg-9">
            <fieldset class="adminform">
                <legend>&nbsp;</legend>
                <?php echo $this->form->renderField('subject'); ?>
                <?php echo $this->form->renderField('template'); ?>
            </fieldset>
        </div>
        <div class="col-lg-3">
            <fieldset class="adminform">
                <legend>&nbsp;</legend>
                <?php echo $this->form->renderField('email_type'); ?>

                <?php echo $this->form->renderField('ticket_status'); ?>

                <?php echo $this->form->renderField('id'); ?>

                <?php echo $this->form->renderField('state'); ?>
            </fieldset>

        </div>
    </div>


    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Publishing', Text::_('COM_JED_GENERAL_PUBLISHING_LABEL', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_JED_GENERAL_PUBLISHING_LABEL'); ?></legend>
                <?php echo $this->form->renderField('created_by'); ?>
                <?php echo $this->form->renderField('modified_by'); ?>
                <?php echo $this->form->renderField('created'); ?>
                <?php echo $this->form->renderField('modified'); ?>
                <?php echo $this->form->renderField('state'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
